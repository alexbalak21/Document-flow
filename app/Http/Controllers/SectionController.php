<?php

namespace App\Http\Controllers;

use App\Models\Resume;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SectionController extends Controller
{
    private function authorizeResume(Resume $resume)
    {
        abort_if($resume->user_id !== Auth::id(), 403);
    }

    public function create(Resume $resume)
    {
        $this->authorizeResume($resume);
        return view('resumes.partials.section-form', compact('resume'));
    }

    public function store(Request $request, Resume $resume)
    {
        $this->authorizeResume($resume);
        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'icon'             => 'nullable|string|max:100',
            'markdown_content' => 'nullable|string',
            'section_type'     => 'nullable|string|max:50',
            'order_index'      => 'nullable|integer',
        ]);
        $data['order_index'] = $data['order_index'] ?? $resume->sections()->count();
        $resume->sections()->create($data);
        return back()->with('success', 'Section ajoutée.');
    }

    public function edit(Resume $resume, Section $section)
    {
        $this->authorizeResume($resume);
        return view('resumes.partials.section-form', compact('resume', 'section'));
    }

    public function update(Request $request, Resume $resume, Section $section)
    {
        $this->authorizeResume($resume);
        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'icon'             => 'nullable|string|max:100',
            'markdown_content' => 'nullable|string',
            'section_type'     => 'nullable|string|max:50',
            'order_index'      => 'nullable|integer',
        ]);
        $section->update($data);
        return back()->with('success', 'Section mise à jour.');
    }

    public function destroy(Resume $resume, Section $section)
    {
        $this->authorizeResume($resume);
        $section->delete();
        return back()->with('success', 'Section supprimée.');
    }
}
