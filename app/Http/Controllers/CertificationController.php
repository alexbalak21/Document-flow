<?php

namespace App\Http\Controllers;

use App\Models\Resume;
use App\Models\Certification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CertificationController extends Controller
{
    private function authorizeResume(Resume $resume) { abort_if($resume->user_id !== Auth::id(), 403); }

    public function create(Resume $resume) { $this->authorizeResume($resume); return view('resumes.partials.certification-form', compact('resume')); }

    public function store(Request $request, Resume $resume)
    {
        $this->authorizeResume($resume);
        $resume->certifications()->create($request->validate(['name'=>'required|string|max:255','organization'=>'required|string|max:255','year'=>'nullable|string|max:20']));
        return back()->with('success', 'Certification ajoutée.');
    }

    public function edit(Resume $resume, Certification $certification) { $this->authorizeResume($resume); return view('resumes.partials.certification-form', compact('resume','certification')); }

    public function update(Request $request, Resume $resume, Certification $certification)
    {
        $this->authorizeResume($resume);
        $certification->update($request->validate(['name'=>'required|string|max:255','organization'=>'required|string|max:255','year'=>'nullable|string|max:20']));
        return back()->with('success', 'Certification mise à jour.');
    }

    public function destroy(Resume $resume, Certification $certification) { $this->authorizeResume($resume); $certification->delete(); return back()->with('success', 'Certification supprimée.'); }
}
