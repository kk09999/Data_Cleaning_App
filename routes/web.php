<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DataCleanerController;
use App\Http\Controllers\AuthController;

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
});
