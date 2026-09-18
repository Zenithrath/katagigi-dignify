<?php

use App\Http\Controllers\Api\DiagnosisCodeController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\CancellationController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\General\CategoryController;
use App\Http\Controllers\General\DashboardController;
use App\Http\Controllers\General\ScheduleController;
use App\Http\Controllers\General\ServiceController;
use App\Http\Controllers\InstallmentController;
use App\Http\Controllers\Master\AdminController;
use App\Http\Controllers\Master\DoctorController;
use App\Http\Controllers\Master\NurseController;
use App\Http\Controllers\Patient\MasterController;
use App\Http\Controllers\Patient\MedicalRecordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Report\IncomeController;
use App\Http\Controllers\Report\TransactionController;
use App\Http\Controllers\SalaryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — port app lama + tambahan V2
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

// Dashboard klinik (data live per role, tampilan Donezo)
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    // Autocomplete kode diagnosis V2 (ICD-10 / ICD-9 / SNOMED)
    Route::get('api/diagnosis-codes', [DiagnosisCodeController::class, 'index'])
        ->name('api.diagnosis-codes');

    Route::get('api/medical-records/lookup', [MedicalRecordController::class, 'lookup'])
        ->name('api.medical-records.lookup');
    Route::get('api/medical-records/{id}/lookup_history', [MedicalRecordController::class, 'lookupMedicalHistory'])
        ->name('api.medical-records.lookup.history');
    Route::get('api/medical-records/get_service', [MedicalRecordController::class, 'getAppointmentService'])
        ->name('api.medical-records.get_service');
    Route::get('api/appointments/get_patient', [AppointmentController::class, 'getPatientByCode'])
        ->name('api.appointments.get_patient');
    Route::get('api/appointments/lookup', [AppointmentController::class, 'lookup'])
        ->name('api.appointments.lookup');
    Route::get('api/appointments/lookup_doctor', [AppointmentController::class, 'lookupDoctor'])
        ->name('api.appointments.lookup.doctor');
    Route::get('api/transactions/find_transactions', [TransactionController::class, 'getTransactionsByKeyword'])
        ->name('api.transactions.find_transactions');
    Route::get('api/transactions/get_transaction/{id}', [TransactionController::class, 'getTransactionsByID'])
        ->name('api.transactions.get_transaction');
    Route::get('api/incomes/lookup', [IncomeController::class, 'lookupTransactionReport'])
        ->name('api.incomes.lookup');
    Route::put('api/transactions/{id}/reschedule', [TransactionController::class, 'reschedule'])
        ->name('api.transactions.reschedule');
    Route::get('api/patients/lookup', [MasterController::class, 'lookup'])
        ->name('api.patients.lookup');
    Route::get('api/schedules/lookup', [ScheduleController::class, 'lookup'])
        ->name('api.schedules.lookup');
    Route::get('api/schedules/lookup_doctor', [ScheduleController::class, 'lookupDoctor'])
        ->name('api.schedules.lookup.doctor');

    Route::get('api/followup/whatsapp/{phone}/{message}', function ($phone, $message) {
        $url = 'https://api.whatsapp.com/send?phone='.$phone.'&text='.$message.'&type=phone_number';

        return redirect()->away($url);
    })->name('api.followup.whatsapp');

    // D-04: konfirmasi mengubah status → wajib POST + authorize di controller.
    Route::post('appointments/{appointment}/confirm', [AppointmentController::class, 'confirm'])
        ->name('appointments.confirm');

    Route::resource('appointments', AppointmentController::class);
    Route::resource('services', ServiceController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('admins', AdminController::class)->except('show');
    Route::resource('doctors', DoctorController::class)->except('show');
    Route::resource('nurses', NurseController::class)->except('show');
    Route::resource('patients', MasterController::class);
    Route::resource('schedules', ScheduleController::class)->except(['create', 'show', 'edit', 'update']);
    Route::resource('medical-records', MedicalRecordController::class);
    Route::resource('transactions', TransactionController::class);
    Route::resource('incomes', IncomeController::class);
    Route::post(
        'schedules/update_status/{schedule}',
        [ScheduleController::class, 'updateStatus']
    )->name('schedules.update_status');

    // Batal langsung = khusus manajemen. Admin operasional lewat usulan di bawah.
    Route::post('transactions/{id}/cancel', [TransactionController::class, 'cancel'])->name('transactions.cancel');

    // V2: usul-kunci-approve pembatalan nota
    Route::post('transactions/{id}/propose-cancel', [CancellationController::class, 'propose'])
        ->name('transactions.propose-cancel');
    Route::post('cancellations/{id}/approve', [CancellationController::class, 'approve'])
        ->name('cancellations.approve');
    Route::post('cancellations/{id}/reject', [CancellationController::class, 'reject'])
        ->name('cancellations.reject');

    Route::get('export-transactions', [ExportController::class, 'exportTransactions'])
        ->name('export-transactions');
    Route::get('switch-language/{lang}', [ProfileController::class, 'switchLanguage'])
        ->name('switch-language');
    Route::get('profile', [ProfileController::class, 'index'])
        ->name('profile');
    Route::put('profile/{id}', [ProfileController::class, 'update'])
        ->name('profile.update');
    Route::get('profile/change-password', [ProfileController::class, 'changePassword'])
        ->name('profile.change-password');
    Route::put('profile/change-password/{id}', [ProfileController::class, 'updatePassword'])
        ->name('profile.change-password.update');

    Route::get('salaries', [SalaryController::class, 'index']);
    Route::resource('installments', InstallmentController::class);
});

require __DIR__.'/auth.php';
