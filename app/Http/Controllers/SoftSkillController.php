<?php

namespace App\Http\Controllers;

use App\Models\Resume;
use App\Models\SoftSkill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SoftSkillController extends Controller
{
    private function authorizeResume(Resume $resume) { abort_if($resume->user_id !== Auth::id(), 403); }

    public function create(Resume $resume) { $this->authorizeResume($resume); return view('resumes.partials.softskill-form', compact('resume')); }

    public function store(Request $request, Resume $resume)
    {
        $this->authorizeResume($resume);
        $resume->softSkills()->create($request->validate(['name'=>'required|string|max:255','icon'=>'nullable|string|max:100','description'=>'nullable|string']));
        return back()->with('success', 'Soft skill ajouté.');
    }

    public function edit(Resume $resume, SoftSkill $softskill) { $this->authorizeResume($resume); return view('resumes.partials.softskill-form', compact('resume','softskill')); }

    public function update(Request $request, Resume $resume, SoftSkill $softskill)
    {
        $this->authorizeResume($resume);
        $softskill->update($request->validate(['name'=>'required|string|max:255','icon'=>'nullable|string|max:100','description'=>'nullable|string']));
        return back()->with('success', 'Soft skill mis à jour.');
    }

    public function destroy(Resume $resume, SoftSkill $softskill) { $this->authorizeResume($resume); $softskill->delete(); return back()->with('success', 'Soft skill supprimé.'); }
}
