<?php

namespace App\Http\Controllers;

use App\Models\Resume;
use App\Models\Experience;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExperienceController extends Controller
{
    private function authorizeResume(Resume $resume)
    {
        abort_if($resume->user_id !== Auth::id(), 403);
    }

    public function create(Resume $resume)
    {
        $this->authorizeResume($resume);
        return view('resumes.partials.experience-form', compact('resume'));
    }

    public function store(Request $request, Resume $resume)
    {
        $this->authorizeResume($resume);
        $data = $request->validate([
            'position_title' => 'required|string|max:255',
            'company'        => 'required|string|max:255',
            'location'       => 'nullable|string|max:255',
            'start_date'     => 'required|string|max:50',
            'end_date'       => 'nullable|string|max:50',
            'description'    => 'nullable|string',
        ]);
        $resume->experiences()->create($data);
        return back()->with('success', 'Expérience ajoutée.');
    }

    public function edit(Resume $resume, Experience $experience)
    {
        $this->authorizeResume($resume);
        return view('resumes.partials.experience-form', compact('resume', 'experience'));
    }

    public function update(Request $request, Resume $resume, Experience $experience)
    {
        $this->authorizeResume($resume);
        $data = $request->validate([
            'position_title' => 'required|string|max:255',
            'company'        => 'required|string|max:255',
            'location'       => 'nullable|string|max:255',
            'start_date'     => 'required|string|max:50',
            'end_date'       => 'nullable|string|max:50',
            'description'    => 'nullable|string',
        ]);
        $experience->update($data);
        return back()->with('success', 'Expérience mise à jour.');
    }

    public function destroy(Resume $resume, Experience $experience)
    {
        $this->authorizeResume($resume);
        $experience->delete();
        return back()->with('success', 'Expérience supprimée.');
    }
}
