<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentType;
use Illuminate\Http\Request;
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
    // UPLOAD ZIP PACKAGE
    // -------------------------------------------------------------------------

    public function upload(Request $request)
    {
        $request->validate([
            'package' => ['required', 'file', 'mimes:zip', 'max:10240'],
        ]);

        $zip     = $request->file('package');
        $tmpDir  = storage_path('app/tmp_upload_' . uniqid());

        // Extract ZIP to temp dir
        $archive = new \ZipArchive();
        if ($archive->open($zip->getRealPath()) !== true) {
            return back()->with('error', 'Could not open ZIP file.');
        }
        $archive->extractTo($tmpDir);
        $archive->close();

        // Find the manifest — may be in root or in a subfolder
        $manifestPath = $this->findFile($tmpDir, 'manifest.json');

        if (! $manifestPath) {
            $this->rmdirRecursive($tmpDir);
            return back()->with('error', 'manifest.json not found in ZIP.');
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->rmdirRecursive($tmpDir);
            return back()->with('error', 'manifest.json is invalid JSON.');
        }

        $errors = $this->validateManifest($manifest, dirname($manifestPath));
        if ($errors) {
            $this->rmdirRecursive($tmpDir);
            return back()->with('error', implode(' ', $errors));
        }

        $slug       = $manifest['slug'];
        $targetDir  = storage_path('app/templates/' . $slug);
        $packageDir = dirname($manifestPath);

        // Copy package files to the templates directory
        if (! is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        foreach (scandir($packageDir) as $file) {
            if ($file === '.' || $file === '..') continue;
            copy($packageDir . '/' . $file, $targetDir . '/' . $file);
        }

        $this->rmdirRecursive($tmpDir);

        // Install or update in DB
        [$installed, $updated, $dbErrors] = $this->scanTemplates(
            installNew: true,
            updateExisting: true,
            onlySlug: $slug
        );

        $message = $installed
            ? "Template \"{$manifest['name']}\" installed."
            : "Template \"{$manifest['name']}\" updated.";

        return redirect()->route('templates.index')
            ->with('success', $message)
            ->with('errors_list', $dbErrors);
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
    // RESCAN & UPDATE
    // -------------------------------------------------------------------------

    public function rescan()
    {
        [$installed, $updated, $errors] = $this->scanTemplates(installNew: true, updateExisting: true);

        return redirect()->route('templates.index')
            ->with('success', "{$installed} template(s) installed, {$updated} template(s) updated.")
            ->with('errors_list', $errors);
    }

    // -------------------------------------------------------------------------
    // REGENERATE SNAPSHOTS for one template
    // -------------------------------------------------------------------------

    public function regenerate(DocumentType $template)
    {
        $count      = 0;
        $errors     = [];
        $controller = app(DocumentController::class);
        $method     = new \ReflectionMethod($controller, 'renderHtml');
        $method->setAccessible(true);

        Document::where('document_type_id', $template->id)
            ->whereNotNull('json_data')
            ->get()
            ->each(function ($doc) use ($template, $controller, $method, &$count, &$errors) {
                try {
                    $html = $method->invoke($controller, $template, $doc->json_data);
                    $doc->update(['html_snapshot' => $html]);
                    $count++;
                } catch (\Throwable $e) {
                    $errors[] = "Doc #{$doc->id}: " . $e->getMessage();
                }
            });

        return redirect()->route('templates.index')
            ->with('success', "{$count} document(s) regenerated for {$template->name}.")
            ->with('errors_list', $errors);
    }

    // -------------------------------------------------------------------------
    // SHARED SCAN LOGIC
    // -------------------------------------------------------------------------

    private function scanTemplates(
        bool $installNew,
        bool $updateExisting,
        ?string $onlySlug = null
    ): array {
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

            if (! file_exists($manifestPath)) continue;

            $manifest = json_decode(file_get_contents($manifestPath), true);
            if (json_last_error() !== JSON_ERROR_NONE) continue;

            $slug    = $manifest['slug'];
            $version = $manifest['version'] ?? '1.0';

            // If onlySlug set, skip others
            if ($onlySlug && $slug !== $onlySlug) continue;

            $existing = DB::table('document_types')->where('slug', $slug)->first();

            $fields = [
                'name'          => $manifest['name'],
                'slug'          => $slug,
                'description'   => $manifest['description']   ?? null,
                'version'       => $version,
                'template_path' => $folder . '/' . $manifest['template'],
                'config_path'   => $folder . '/' . $manifest['form'],
                'preview_image' => isset($manifest['preview'])
                    ? $folder . '/' . $manifest['preview'] : null,
                'icon'          => $manifest['icon']          ?? 'bi-file-earmark-text',
                'sidebar_label' => $manifest['sidebar_label'] ?? null,
                'sidebar_group' => $manifest['sidebar_group'] ?? null,
            ];

            if (! $existing) {
                if ($installNew) {
                    DB::table('document_types')->insert(array_merge($fields, [
                        'active'     => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]));
                    $installed++;
                }
            } else {
                if ($updateExisting) {
                    DB::table('document_types')->where('slug', $slug)->update(
                        array_merge($fields, ['updated_at' => now()])
                    );

                    // Regenerate snapshots
                    $type       = DocumentType::where('slug', $slug)->first();
                    $controller = app(DocumentController::class);
                    $method     = new \ReflectionMethod($controller, 'renderHtml');
                    $method->setAccessible(true);

                    Document::where('document_type_id', $type->id)
                        ->whereNotNull('json_data')
                        ->get()
                        ->each(function ($doc) use ($type, $controller, $method, &$errors) {
                            try {
                                $html = $method->invoke($controller, $type, $doc->json_data);
                                $doc->update(['html_snapshot' => $html]);
                            } catch (\Throwable $e) {
                                $errors[] = "Doc #{$doc->id}: " . $e->getMessage();
                            }
                        });

                    $updated++;
                }
            }
        }

        return [$installed, $updated, $errors];
    }

    // -------------------------------------------------------------------------
    // HELPERS
    // -------------------------------------------------------------------------

    private function findFile(string $dir, string $filename): ?string
    {
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir)) as $file) {
            if ($file->getFilename() === $filename) {
                return $file->getPathname();
            }
        }
        return null;
    }

    private function validateManifest(array $manifest, string $dir): array
    {
        $errors = [];

        foreach (['name', 'slug', 'template', 'form'] as $field) {
            if (empty($manifest[$field])) {
                $errors[] = "manifest.json missing field: {$field}.";
            }
        }

        if (! empty($manifest['template']) && ! file_exists($dir . '/' . $manifest['template'])) {
            $errors[] = "Template file '{$manifest['template']}' not found in ZIP.";
        }

        if (! empty($manifest['form']) && ! file_exists($dir . '/' . $manifest['form'])) {
            $errors[] = "Form file '{$manifest['form']}' not found in ZIP.";
        }

        return $errors;
    }

    private function rmdirRecursive(string $dir): void
    {
        if (! is_dir($dir)) return;

        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . '/' . $item;
            is_dir($path) ? $this->rmdirRecursive($path) : unlink($path);
        }

        rmdir($dir);
    }
}
