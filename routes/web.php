<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResumeController;
use App\Http\Controllers\ExperienceController;
use App\Http\Controllers\EducationController;
use App\Http\Controllers\CertificationController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\SoftSkillController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\MarkdownController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Root: redirect authenticated users to their CVs, guests to welcome page
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('resumes.index');
    }
    return view('welcome');
});

// Dashboard: redirect straight to resumes list
Route::get('/dashboard', function () {
    return redirect()->route('resumes.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    // User profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Resumes
    Route::resource('resumes', ResumeController::class);

    // Nested resources (scoped to resume)
    Route::resource('resumes.experiences',    ExperienceController::class)->except(['index', 'show']);
    Route::resource('resumes.educations',     EducationController::class)->except(['index', 'show']);
    Route::resource('resumes.certifications', CertificationController::class)->except(['index', 'show']);
    Route::resource('resumes.skills',         SkillController::class)->except(['index', 'show']);
    Route::resource('resumes.languages',      LanguageController::class)->except(['index', 'show']);
    Route::resource('resumes.softskills',     SoftSkillController::class)->except(['index', 'show']);
    Route::resource('resumes.sections',       SectionController::class)->except(['index', 'show']);

    // Markdown import / export
    Route::get('resumes/{resume}/export-md',  [MarkdownController::class, 'export'])->name('resumes.export-md');
    Route::post('resumes/import-md',          [MarkdownController::class, 'import'])->name('resumes.import-md');
});

require __DIR__.'/auth.php';
