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

<<<<<<< HEAD
    // Document type landing page
    Route::get('/documents/{slug}', [DocumentController::class, 'page'])->name('documents.page');

    // Document CRUD
    Route::get('/documents/{slug}/create',   [DocumentController::class, 'create'])->name('documents.create');
    Route::post('/documents/{slug}/preview', [DocumentController::class, 'preview'])->name('documents.preview');
    Route::post('/documents/{slug}/store',   [DocumentController::class, 'store'])->name('documents.store');

    // History
    Route::get('/history',            [DocumentController::class, 'history'])->name('documents.history');
    Route::get('/history/{document}', [DocumentController::class, 'show'])->name('documents.show');

    // Status & Convert
    Route::post('/documents/{document}/status',  [DocumentController::class, 'updateStatus'])->name('documents.status');
    Route::post('/documents/{document}/convert', [DocumentController::class, 'convert'])->name('documents.convert');

    // Customers
    Route::get('/customers',                   [CustomerController::class, 'index'])->name('customers.index');
    Route::post('/customers',                  [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{customer}/edit',   [CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('/customers/{customer}',        [CustomerController::class, 'update'])->name('customers.update');
    Route::get('/api/customers',               [CustomerController::class, 'list'])->name('customers.list');
=======
    // Nested resources (scoped to resume)
    Route::resource('resumes.experiences',    ExperienceController::class)->except(['index', 'show']);
    Route::resource('resumes.educations',     EducationController::class)->except(['index', 'show']);
    Route::resource('resumes.certifications', CertificationController::class)->except(['index', 'show']);
    Route::resource('resumes.skills',         SkillController::class)->except(['index', 'show']);
    Route::resource('resumes.languages',      LanguageController::class)->except(['index', 'show']);
    Route::resource('resumes.softskills',     SoftSkillController::class)->except(['index', 'show']);
    Route::resource('resumes.sections',       SectionController::class)->except(['index', 'show']);
>>>>>>> 31cafc1165c946cd67ea18900825a33a70b7f07b

    // Markdown import / export
    Route::get('resumes/{resume}/export-md',  [MarkdownController::class, 'export'])->name('resumes.export-md');
    Route::post('resumes/import-md',          [MarkdownController::class, 'import'])->name('resumes.import-md');
});

require __DIR__.'/auth.php';
