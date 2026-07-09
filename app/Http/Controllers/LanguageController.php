<?php

namespace App\Http\Controllers;

use App\Models\Resume;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LanguageController extends Controller
{
    private function authorizeResume(Resume $resume)
    {
        abort_if($resume->user_id !== Auth::id(), 403);
    }

    public function create(Resume $resume)
    {
        $this->authorizeResume($resume);
        return view('resumes.partials.language-form', compact('resume'));
    }

    public function store(Request $request, Resume $resume)
    {
        $this->authorizeResume($resume);
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'level' => 'required|in:basic,intermediate,advanced,fluent',
        ]);
        $resume->languages()->create($data);
        return back()->with('success', 'Langue ajoutée.');
    }

    public function edit(Resume $resume, Language $language)
    {
        $this->authorizeResume($resume);
        return view('resumes.partials.language-form', compact('resume', 'language'));
    }

    public function update(Request $request, Resume $resume, Language $language)
    {
        $this->authorizeResume($resume);
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'level' => 'required|in:basic,intermediate,advanced,fluent',
        ]);
        $language->update($data);
        return back()->with('success', 'Langue mise à jour.');
    }

    public function destroy(Resume $resume, Language $language)
    {
        $this->authorizeResume($resume);
        $language->delete();
        return back()->with('success', 'Langue supprimée.');
    }
}
