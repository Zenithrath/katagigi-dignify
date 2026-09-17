<?php

use App\Http\Controllers\Api\DiagnosisCodeController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware('auth')->group(function () {
    // Autocomplete kode diagnosis (ICD-10 / ICD-9 / SNOMED) untuk nakes.
    Route::get('api/diagnosis-codes', [DiagnosisCodeController::class, 'index'])
        ->name('api.diagnosis-codes');
});

require __DIR__.'/auth.php';
