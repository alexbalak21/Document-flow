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
    // TOGGLE ACTIVE
    // -------------------------------------------------------------------------

    public function toggle(DocumentType $template)
    {
        $template->update(['active' => ! $template->active]);

        $state = $template->active ? 'enabled' : 'disabled';

        return redirect()->route('templates.index')
            ->with('success', '"' . $template->name . '" ' . $state . '.');
    }

    // -------------------------------------------------------------------------
    // DELETE — removes DB record AND files from disk
    // -------------------------------------------------------------------------

    public function destroy(DocumentType $template)
    {
        // Block deletion if documents exist for this template
        if ($template->documents()->count() > 0) {
            return redirect()->route('templates.index')
                ->with('error', 'Cannot delete "' . $template->name . '" — ' . $template->documents()->count() . ' document(s) exist. Disable it instead.');
        }

        $templateDir = storage_path('app/templates/' . $template->slug);

        // Delete files from disk
        if (is_dir($templateDir)) {
            $this->rmdirRecursive($templateDir);
        }

        $template->delete();

        return redirect()
            ->route('templates.index')
            ->with('success', "Template \"{$template->name}\" deleted.");
    }

    // -------------------------------------------------------------------------
    // REGENERATE SNAPSHOTS for one template
    // -------------------------------------------------------------------------

    public function regenerate(DocumentType $template)
    {
        $count      = 0;
        $errors     = [];
        $controller = app(DocumentController::class);

        Document::where('document_type_id', $template->id)
            ->whereNotNull('json_data')
            ->get()
            ->each(function ($doc) use ($template, $controller, &$count, &$errors) {
                try {
                    $html = $controller->renderHtml($template, $doc->json_data);
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
    // PREVIEW TEMPLATE (with placeholders highlighted)
    // -------------------------------------------------------------------------

    public function preview(DocumentType $template)
    {
        $templateFile = $template->template_path;
        $styleFile    = dirname($templateFile) . '/style.css';

        if (! file_exists($templateFile)) {
            abort(404, 'Template file not found: ' . $templateFile);
        }

        $html = file_get_contents($templateFile);
        $css  = file_exists($styleFile) ? file_get_contents($styleFile) : '';

        // Inject CSS
        $html = str_replace('{{style}}', $css, $html);

        // Highlight all remaining {{placeholders}} with a coloured span
        $html = preg_replace(
            '/\{\{([^}]+)\}\}/',
            '<span class="__placeholder__">{{$1}}</span>',
            $html
        );

        // Inject placeholder highlight style into <head>
        $placeholderCss = '
        <style>
        .__placeholder__ {
            display: inline-block;
            background: #fef9c3;
            border: 1px dashed #ca8a04;
            border-radius: 3px;
            padding: 0 4px;
            font-family: monospace;
            font-size: 11px;
            color: #92400e;
            white-space: nowrap;
        }
        </style>';

        $html = str_replace('</head>', $placeholderCss . '</head>', $html);

        // Add a top banner indicating this is a preview
        $banner = '
        <div style="
            position: fixed; top: 0; left: 0; right: 0; z-index: 9999;
            background: #1e2533; color: #fff;
            padding: 8px 20px;
            font-family: sans-serif; font-size: 13px;
            display: flex; align-items: center; gap: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,.3);
        ">
            <span style="font-weight:600;">Template Preview</span>
            <span style="color:#93c5fd;">' . htmlspecialchars($template->name) . ' v' . $template->version . '</span>
            <span style="margin-left:auto; font-size:11px; color:#9ca3af;">
                Placeholders are highlighted in yellow — they will be replaced with real data on document generation.
            </span>
            <a href="javascript:history.back()" style="
                color: rgba(255,255,255,.8);
                background: rgba(255,255,255,.1);
                border: 1px solid rgba(255,255,255,.2);
                border-radius: 5px;
                padding: 4px 12px;
                text-decoration: none;
                font-size: 12px;
            ">← Back</a>
        </div>
        <div style="height:48px;"></div>';

        $html = str_replace('<body>', '<body>' . $banner, $html);

        return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
    }

    // -------------------------------------------------------------------------
    // SHARED SCAN LOGIC
    // -------------------------------------------------------------------------

    // -------------------------------------------------------------------------
    // UPDATE ACCENT COLOR
    // -------------------------------------------------------------------------

    public function updateColor(Request $request, DocumentType $template)
    {
        $request->validate([
            'accent_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $template->update(['accent_color' => $request->accent_color]);

        // Regenerate all snapshots with new color
        $count      = 0;
        $controller = app(DocumentController::class);

        Document::where('document_type_id', $template->id)
            ->whereNotNull('json_data')
            ->get()
            ->each(function ($doc) use ($template, $controller, &$count) {
                try {
                    $html = $controller->renderHtml($template, $doc->json_data);
                    $doc->update(['html_snapshot' => $html]);
                    $count++;
                } catch (\Throwable $e) {}
            });

        return response()->json([
            'success' => true,
            'message' => 'Color updated. ' . $count . ' document(s) regenerated.',
            'color'   => $request->accent_color,
        ]);
    }

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

                    Document::where('document_type_id', $type->id)
                        ->whereNotNull('json_data')
                        ->get()
                        ->each(function ($doc) use ($type, $controller, &$errors) {
                            try {
                                $html = $controller->renderHtml($type, $doc->json_data);
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