<?php

namespace App\Http\Controllers;

use App\Models\Resume;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SkillController extends Controller
{
    private function authorizeResume(Resume $resume)
    {
        abort_if($resume->user_id !== Auth::id(), 403);
    }

    public function create(Resume $resume)
    {
        $this->authorizeResume($resume);
        return view('resumes.partials.skill-form', compact('resume'));
    }

    public function store(Request $request, Resume $resume)
    {
        $this->authorizeResume($resume);
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'icon'        => 'nullable|string|max:100',
            'level_type'  => 'required|in:beginner,intermediate,advanced,expert,percentage',
            'level_value' => 'nullable|integer|min:0|max:100',
        ]);
        $resume->skills()->create($data);
        return back()->with('success', 'Compétence ajoutée.');
    }

    public function edit(Resume $resume, Skill $skill)
    {
        $this->authorizeResume($resume);
        return view('resumes.partials.skill-form', compact('resume', 'skill'));
    }

    public function update(Request $request, Resume $resume, Skill $skill)
    {
        $this->authorizeResume($resume);
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'icon'        => 'nullable|string|max:100',
            'level_type'  => 'required|in:beginner,intermediate,advanced,expert,percentage',
            'level_value' => 'nullable|integer|min:0|max:100',
        ]);
        $skill->update($data);
        return back()->with('success', 'Compétence mise à jour.');
    }

    public function destroy(Resume $resume, Skill $skill)
    {
        $this->authorizeResume($resume);
        $skill->delete();
        return back()->with('success', 'Compétence supprimée.');
    }
}
