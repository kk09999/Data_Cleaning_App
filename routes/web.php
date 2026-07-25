<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DataCleanerController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExportController;

// --- Public Authentication Routes ---
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// --- Protected Application Routes (Authentication Required) ---
Route::middleware(['auth'])->group(function () {
    Route::get('/', [DataCleanerController::class, 'index'])->name('cleaner.index');
    Route::post('/api/clean-data', [DataCleanerController::class, 'cleanData'])->name('cleaner.clean');
    Route::post('/api/save-database', [DataCleanerController::class, 'saveToDatabase'])->name('cleaner.save');
    Route::get('/api/database-leads', [DataCleanerController::class, 'getDatabaseLeads'])->name('cleaner.database');
    Route::delete('/api/batch/{id}', [DataCleanerController::class, 'deleteBatch'])->name('cleaner.deleteBatch');

    // --- Lead Database CRUD Routes ---
    Route::post('/api/leads', [DataCleanerController::class, 'storeLead'])->name('leads.store');
    Route::put('/api/leads/{id}', [DataCleanerController::class, 'updateLead'])->name('leads.update');
    Route::delete('/api/leads/{id}', [DataCleanerController::class, 'deleteLead'])->name('leads.destroy');
    Route::post('/api/leads/bulk-delete', [DataCleanerController::class, 'bulkDeleteLeads'])->name('leads.bulkDelete');
    Route::post('/api/leads/bulk-status', [DataCleanerController::class, 'bulkUpdateStatus'])->name('leads.bulkStatus');
    Route::post('/api/leads/wipe-all', [DataCleanerController::class, 'wipeAllLeads'])->name('leads.wipeAll');
    Route::get('/api/leads/search-email', [DataCleanerController::class, 'searchByEmail'])->name('leads.searchEmail');

    // --- Data Analyst Analytics Dashboard Endpoint ---
    Route::get('/api/analytics-summary', [DataCleanerController::class, 'getAnalyticsSummary'])->name('analytics.summary');

    // --- CSV Streamed Export Route ---
    Route::get('/export/csv', [ExportController::class, 'exportCsv'])->name('export.csv');
});
