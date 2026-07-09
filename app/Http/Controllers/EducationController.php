<?php

namespace App\Http\Controllers;

use App\Models\Resume;
use App\Models\Education;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EducationController extends Controller
{
    private function authorizeResume(Resume $resume) { abort_if($resume->user_id !== Auth::id(), 403); }

    public function create(Resume $resume) { $this->authorizeResume($resume); return view('resumes.partials.education-form', compact('resume')); }

    public function store(Request $request, Resume $resume)
    {
        $this->authorizeResume($resume);
        $resume->educations()->create($request->validate(['school'=>'required|string|max:255','diploma'=>'required|string|max:255','year'=>'required|string|max:20']));
        return back()->with('success', 'Formation ajoutée.');
    }

    public function edit(Resume $resume, Education $education) { $this->authorizeResume($resume); return view('resumes.partials.education-form', compact('resume','education')); }

    public function update(Request $request, Resume $resume, Education $education)
    {
        $this->authorizeResume($resume);
        $education->update($request->validate(['school'=>'required|string|max:255','diploma'=>'required|string|max:255','year'=>'required|string|max:20']));
        return back()->with('success', 'Formation mise à jour.');
    }

    public function destroy(Resume $resume, Education $education) { $this->authorizeResume($resume); $education->delete(); return back()->with('success', 'Formation supprimée.'); }
}
