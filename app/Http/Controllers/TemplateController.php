<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentType;
use App\Services\EntityResolver;
use Illuminate\Support\Facades\DB;

class TemplateController extends Controller
{
    // -------------------------------------------------------------------------
    // INDEX
    // -------------------------------------------------------------------------

    public function index()
    {
        $templates = DocumentType::withCount('documents')->orderBy('name')->get();
        return view('templates.index', compact('templates'));
    }

    // -------------------------------------------------------------------------
    // SCAN & INSTALL (new templates only)
    // -------------------------------------------------------------------------

    public function install()
    {
        [$installed, $updated, $errors] = $this->scanTemplates(installNew: true, updateExisting: false);

        return redirect()->route('templates.index')
            ->with('success', "{$installed} template(s) installed.")
            ->with('errors_list', $errors);
    }

    // -------------------------------------------------------------------------
    // RESCAN & UPDATE (update existing DB records + regenerate snapshots)
    // -------------------------------------------------------------------------

    public function rescan()
    {
        [$installed, $updated, $errors] = $this->scanTemplates(installNew: true, updateExisting: true);

        return redirect()->route('templates.index')
            ->with('success', "{$installed} template(s) installed, {$updated} template(s) updated.")
            ->with('errors_list', $errors);
    }

    // -------------------------------------------------------------------------
    // REGENERATE SNAPSHOTS for one template type
    // -------------------------------------------------------------------------

    public function regenerate(DocumentType $template)
    {
        $count      = 0;
        $errors     = [];
        $controller = app(DocumentController::class);
        $method     = new \ReflectionMethod($controller, 'renderHtml');
        $method->setAccessible(true);

        $documents = Document::where('document_type_id', $template->id)
            ->whereNotNull('json_data')
            ->get();

        foreach ($documents as $doc) {
            try {
                $html = $method->invoke($controller, $template, $doc->json_data);
                $doc->update(['html_snapshot' => $html]);
                $count++;
            } catch (\Throwable $e) {
                $errors[] = "Doc #{$doc->id}: " . $e->getMessage();
            }
        }

        return redirect()->route('templates.index')
            ->with('success', "{$count} document(s) regenerated for {$template->name}.")
            ->with('errors_list', $errors);
    }

    // -------------------------------------------------------------------------
    // SHARED SCAN LOGIC
    // -------------------------------------------------------------------------

    private function scanTemplates(bool $installNew, bool $updateExisting): array
    {
        $installed = 0;
        $updated   = 0;
        $errors    = [];

        $basePath = storage_path('app/templates');

        if (! is_dir($basePath)) {
            $errors[] = "Folder not found: $basePath";
            return [$installed, $updated, $errors];
        }

        $folders = array_filter(glob($basePath . '/*'), 'is_dir');

        foreach ($folders as $folder) {
            $manifestPath = $folder . '/manifest.json';

            if (! file_exists($manifestPath)) {
                $errors[] = basename($folder) . ': manifest.json missing';
                continue;
            }

            $manifest = json_decode(file_get_contents($manifestPath), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $errors[] = basename($folder) . ': manifest.json is invalid JSON';
                continue;
            }

            $slug    = $manifest['slug'];
            $version = $manifest['version'] ?? '1.0';

            $existing = DB::table('document_types')->where('slug', $slug)->first();

            if (! $existing) {
                // New template — install if requested
                if ($installNew) {
                    DB::table('document_types')->insert([
                        'name'          => $manifest['name'],
                        'slug'          => $slug,
                        'description'   => $manifest['description'] ?? null,
                        'version'       => $version,
                        'template_path' => $folder . '/' . $manifest['template'],
                        'config_path'   => $folder . '/' . $manifest['form'],
                        'preview_image' => isset($manifest['preview'])
                            ? $folder . '/' . $manifest['preview'] : null,
                        'active'        => true,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                    $installed++;
                }
            } else {
                // Existing template — update if requested
                if ($updateExisting) {
                    DB::table('document_types')->where('slug', $slug)->update([
                        'name'          => $manifest['name'],
                        'description'   => $manifest['description'] ?? null,
                        'version'       => $version,
                        'template_path' => $folder . '/' . $manifest['template'],
                        'config_path'   => $folder . '/' . $manifest['form'],
                        'preview_image' => isset($manifest['preview'])
                            ? $folder . '/' . $manifest['preview'] : null,
                        'updated_at'    => now(),
                    ]);

                    // Regenerate all saved snapshots for this template
                    $type       = DocumentType::where('slug', $slug)->first();
                    $controller = app(DocumentController::class);
                    $method     = new \ReflectionMethod($controller, 'renderHtml');
                    $method->setAccessible(true);

                    $documents = Document::where('document_type_id', $type->id)
                        ->whereNotNull('json_data')
                        ->get();

                    foreach ($documents as $doc) {
                        try {
                            $html = $method->invoke($controller, $type, $doc->json_data);
                            $doc->update(['html_snapshot' => $html]);
                        } catch (\Throwable $e) {
                            $errors[] = "Doc #{$doc->id}: " . $e->getMessage();
                        }
                    }

                    $updated++;
                }
            }
        }

        return [$installed, $updated, $errors];
    }
}
