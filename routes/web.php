<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CompanySettingsController;
use App\Http\Controllers\ImportExportController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\ProductController;

// Auth
Route::get('/login', [LoginController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected
Route::middleware('auth')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Templates
    Route::get('/templates', [TemplateController::class, 'index'])->name('templates.index');
    Route::post('/templates/upload',   [TemplateController::class, 'upload'])->name('templates.upload');
    Route::post('/templates/install',  [TemplateController::class, 'install'])->name('templates.install');
    Route::post('/templates/rescan',   [TemplateController::class, 'rescan'])->name('templates.rescan');
    Route::post('/templates/{template}/regenerate', [TemplateController::class, 'regenerate'])->name('templates.regenerate');
    Route::get('/templates/{template}/preview',     [TemplateController::class, 'preview'])->name('templates.preview');
    Route::post('/templates/{template}/toggle',     [TemplateController::class, 'toggle'])->name('templates.toggle');
    Route::post('/templates/{template}/color',       [TemplateController::class, 'updateColor'])->name('templates.color');
    Route::delete('/templates/{template}',          [TemplateController::class, 'destroy'])->name('templates.destroy');

    // Document type landing page
    Route::get('/documents/{slug}', [DocumentController::class, 'page'])->name('documents.page');

    // Document CRUD
    Route::get('/documents/{slug}/create',   [DocumentController::class, 'create'])->name('documents.create');
    Route::post('/documents/{slug}/preview', [DocumentController::class, 'preview'])->name('documents.preview');
    Route::post('/documents/{slug}/store',   [DocumentController::class, 'store'])->name('documents.store');

    // History
    Route::get('/history',            [DocumentController::class, 'history'])->name('documents.history');
    Route::get('/history/{document}',     [DocumentController::class, 'show'])->name('documents.show');
    Route::get('/history/{document}/raw', [DocumentController::class, 'raw'])->name('documents.raw');
    Route::get('/pdf/{document}/download', [PdfController::class, 'download'])->name('pdf.download');
    Route::get('/pdf/{document}/preview',  [PdfController::class, 'preview'])->name('pdf.preview');
    Route::get('/documents/{document}/edit',   [DocumentController::class, 'edit'])->name('documents.edit');
    Route::put('/documents/{document}/update', [DocumentController::class, 'update'])->name('documents.update');
    // Status & Convert
    Route::post('/documents/{document}/status',  [DocumentController::class, 'updateStatus'])->name('documents.status');
    Route::post('/documents/{document}/convert', [DocumentController::class, 'convert'])->name('documents.convert');

    // Company settings
    Route::get('/settings/company', [CompanySettingsController::class, 'edit'])->name('settings.company');
    Route::put('/settings/company', [CompanySettingsController::class, 'update'])->name('settings.company.update');

    // Products
    Route::get('/products',                  [ProductController::class, 'index'])->name('products.index');
    Route::post('/products',                 [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit',   [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}',        [ProductController::class, 'update'])->name('products.update');

    // Import / Export
    Route::get('/export/document/{document}',        [ImportExportController::class, 'documentExport'])->name('export.document');
    Route::get('/export/document-model/{slug}',      [ImportExportController::class, 'documentModel'])->name('export.document.model');
    Route::post('/import/document/{slug}',           [ImportExportController::class, 'documentImport'])->name('import.document');

    Route::get('/export/customer/{customer}',        [ImportExportController::class, 'customerExport'])->name('export.customer');
    Route::get('/export/customer-model',             [ImportExportController::class, 'customerModel'])->name('export.customer.model');
    Route::post('/import/customer',                  [ImportExportController::class, 'customerImport'])->name('import.customer');

    Route::get('/export/product/{product}',          [ImportExportController::class, 'productExport'])->name('export.product');
    Route::get('/export/product-model',              [ImportExportController::class, 'productModel'])->name('export.product.model');
    Route::post('/import/product',                   [ImportExportController::class, 'productImport'])->name('import.product');

    // Customers
    Route::get('/customers',                   [CustomerController::class, 'index'])->name('customers.index');
    Route::post('/customers',                  [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{customer}/edit',   [CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('/customers/{customer}',        [CustomerController::class, 'update'])->name('customers.update');
    Route::get('/api/customers',               [CustomerController::class, 'list'])->name('customers.list');

});