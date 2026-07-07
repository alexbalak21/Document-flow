<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\DocumentController;

// Auth
Route::get('/login', [LoginController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Templates
    Route::get('/templates', [TemplateController::class, 'index'])->name('templates.index');
    Route::post('/templates/install', [TemplateController::class, 'install'])->name('templates.install');

    // Documents
    Route::get('/documents/{slug}/create', [DocumentController::class, 'create'])->name('documents.create');
    Route::post('/documents/{slug}/preview', [DocumentController::class, 'preview'])->name('documents.preview');

    // History
    Route::get('/history', [DocumentController::class, 'history'])->name('documents.history');
    Route::get('/history/{document}', [DocumentController::class, 'show'])->name('documents.show');
});
