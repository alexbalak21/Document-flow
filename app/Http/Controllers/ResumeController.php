<?php

namespace App\Http\Controllers;

use App\Models\Resume;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResumeController extends Controller
{
    public function index()
    {
        $resumes = Auth::user()->resumes()->latest()->get();
        return view('resumes.index', compact('resumes'));
    }

    public function create()
    {
        return view('resumes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'language' => 'required|string|max:10',
        ]);

        $resume = Auth::user()->resumes()->create($data);

        return redirect()->route('resumes.edit', $resume)->with('success', 'CV créé avec succès.');
    }

    public function show(Resume $resume)
    {
        $this->authorizeResume($resume);
        return view('resumes.show', compact('resume'));
    }

    public function edit(Resume $resume)
    {
        $this->authorizeResume($resume);
        $resume->load([
            'sections',
            'experiences',
            'educations',
            'certifications',
            'skills',
            'languages',
            'softSkills',
        ]);
        return view('resumes.edit', compact('resume'));
    }

    public function update(Request $request, Resume $resume)
    {
        $this->authorizeResume($resume);

        $data = $request->validate([
            'name'                  => 'required|string|max:255',
            'language'              => 'required|string|max:10',
            'profile_image_base64'  => 'nullable|string',
        ]);

        // Handle image upload → base64
        if ($request->hasFile('profile_image')) {
            $file = $request->file('profile_image');
            $data['profile_image_base64'] = 'data:' . $file->getMimeType() . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));
        }

        $resume->update($data);

        return back()->with('success', 'CV mis à jour.');
    }

    public function destroy(Resume $resume)
    {
        $this->authorizeResume($resume);
        $resume->delete();
        return redirect()->route('resumes.index')->with('success', 'CV supprimé.');
    }

    private function authorizeResume(Resume $resume)
    {
        abort_if($resume->user_id !== Auth::id(), 403);
    }
}
