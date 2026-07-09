<?php

namespace App\Http\Controllers;

use App\Models\Resume;
use App\Services\MarkdownService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarkdownController extends Controller
{
    public function __construct(private MarkdownService $md) {}

    public function export(Resume $resume)
    {
        abort_if($resume->user_id !== Auth::id(), 403);

        $content  = $this->md->export($resume);
        $filename = str()->slug($resume->name) . '.md';

        return response($content, 200, [
            'Content-Type'        => 'text/markdown',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'markdown_file' => 'required|file|mimes:md,txt|max:512',
        ]);

        $content = file_get_contents($request->file('markdown_file')->getRealPath());
        $resume  = $this->md->import($content, Auth::id());

        return redirect()->route('resumes.edit', $resume)->with('success', 'CV importé avec succès.');
    }
}
