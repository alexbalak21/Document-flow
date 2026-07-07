<?php

namespace App\Http\Controllers;

use App\Models\DocumentType;

class TemplateController extends Controller
{
    /**
     * List all installed templates.
     */
    public function index()
    {
        $templates = DocumentType::orderBy('name')->get();
        return view('templates.index', compact('templates'));
    }

    /**
     * Scan storage/app/templates/ and install any template not yet in the DB.
     */
    public function install()
    {
        $installed  = 0;
        $errors     = [];

        $basePath = storage_path('app/templates');

        if (! is_dir($basePath)) {
            return redirect()->route('templates.index')
                ->with('success', '0 template(s) installed.')
                ->with('errors_list', ["Folder not found: $basePath"]);
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

            // Skip if already installed (same slug + version)
            $exists = DocumentType::where('slug', $manifest['slug'])
                ->where('version', $manifest['version'])
                ->exists();

            if ($exists) {
                continue;
            }

            DocumentType::create([
                'name'          => $manifest['name'],
                'slug'          => $manifest['slug'],
                'description'   => $manifest['description'] ?? null,
                'version'       => $manifest['version'] ?? '1.0',
                'template_path' => $folder . '/' . $manifest['template'],
                'config_path'   => $folder . '/' . $manifest['form'],
                'preview_image' => isset($manifest['preview'])
                    ? $folder . '/' . $manifest['preview']
                    : null,
                'active'        => true,
            ]);

            $installed++;
        }

        return redirect()->route('templates.index')
            ->with('success', "$installed template(s) installed.")
            ->with('errors_list', $errors);
    }
}
