<?php

use App\Http\Controllers\Api\DiagnosisCodeController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Billing\DoctorFeeController;
use App\Http\Controllers\Billing\InvoiceController;
use App\Http\Controllers\Billing\PaymentController;
use App\Http\Controllers\CancellationController;
use App\Http\Controllers\Integration\SatuSehatController;
use App\Http\Controllers\Integration\WhatsappController;
use App\Http\Controllers\Inventory\InventoryController;
use App\Http\Controllers\Operational\BranchController;
use App\Http\Controllers\Operational\ExpenseController;
use App\Http\Controllers\Report\FinanceReportController;
use App\Http\Controllers\Clinical\AnamnesisController;
use App\Http\Controllers\Clinical\CalendarController;
use App\Http\Controllers\Clinical\ExaminationController;
use App\Http\Controllers\Clinical\OdontogramController;
use App\Http\Controllers\Clinical\PrescriptionController;
use App\Http\Controllers\Clinical\TreatmentPlanController;
use App\Http\Controllers\Clinical\VisitAttachmentController;
use App\Http\Controllers\Clinical\VisitController;
use App\Http\Controllers\Clinical\WorkspaceController;
use App\Http\Controllers\Clinical\VisitDiagnosisController;
use App\Http\Controllers\Clinical\VisitTreatmentController;
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

    // Fase 2: kunjungan klinis (check-in → antrian → visit).
    Route::post('appointments/{appointment}/checkin', [VisitController::class, 'checkin'])
        ->name('appointments.checkin');
    Route::post('visits/{visit}/status', [VisitController::class, 'updateStatus'])
        ->name('visits.status');
    Route::post('visits/{visit}/sign', [VisitController::class, 'sign'])
        ->name('visits.sign');
    Route::resource('visits', VisitController::class)->only(['index', 'show']);

    // Fase 2 Task 9: workspace dokter + kalender appointment.
    Route::get('workspace', [WorkspaceController::class, 'index'])->name('workspace.index');
    Route::get('calendar', [CalendarController::class, 'index'])->name('calendar.index');

    // Fase 2 Task 3: anamnesis + SOAP per visit.
    Route::post('visits/{visit}/anamnesis', [AnamnesisController::class, 'store'])->name('visits.anamnesis.store');
    Route::put('visits/{visit}/anamnesis', [AnamnesisController::class, 'update'])->name('visits.anamnesis.update');
    Route::post('visits/{visit}/examination', [ExaminationController::class, 'store'])->name('visits.examination.store');
    Route::put('visits/{visit}/examination', [ExaminationController::class, 'update'])->name('visits.examination.update');

    // Fase 2 Task 4: odontogram per visit.
    Route::post('visits/{visit}/odontogram', [OdontogramController::class, 'store'])->name('visits.odontogram.store');
    Route::delete('visits/{visit}/odontogram/{finding}', [OdontogramController::class, 'destroy'])->name('visits.odontogram.destroy');

    // Fase 2 Task 5: diagnosis ICD-10 + tindakan ICD-9 per visit.
    Route::post('visits/{visit}/diagnoses', [VisitDiagnosisController::class, 'store'])->name('visits.diagnoses.store');
    Route::delete('visits/{visit}/diagnoses/{diagnosis}', [VisitDiagnosisController::class, 'destroy'])->name('visits.diagnoses.destroy');
    Route::post('visits/{visit}/treatments', [VisitTreatmentController::class, 'store'])->name('visits.treatments.store');
    Route::delete('visits/{visit}/treatments/{treatment}', [VisitTreatmentController::class, 'destroy'])->name('visits.treatments.destroy');

    // Fase 2 Task 6: rencana perawatan per visit.
    Route::post('visits/{visit}/plans', [TreatmentPlanController::class, 'store'])->name('visits.plans.store');
    Route::post('visits/{visit}/plans/{plan}/status', [TreatmentPlanController::class, 'updateStatus'])->name('visits.plans.status');
    Route::delete('visits/{visit}/plans/{plan}', [TreatmentPlanController::class, 'destroy'])->name('visits.plans.destroy');
    Route::post('visits/{visit}/plans/{plan}/items', [TreatmentPlanController::class, 'storeItem'])->name('visits.plans.items.store');
    Route::delete('visits/{visit}/plans/{plan}/items/{item}', [TreatmentPlanController::class, 'destroyItem'])->name('visits.plans.items.destroy');

    // Fase 2 Task 7: resep terstruktur per visit.
    Route::post('visits/{visit}/prescriptions', [PrescriptionController::class, 'store'])->name('visits.prescriptions.store');
    Route::delete('visits/{visit}/prescriptions/{prescription}', [PrescriptionController::class, 'destroy'])->name('visits.prescriptions.destroy');
    Route::post('visits/{visit}/prescriptions/{prescription}/items', [PrescriptionController::class, 'storeItem'])->name('visits.prescriptions.items.store');
    Route::delete('visits/{visit}/prescriptions/{prescription}/items/{item}', [PrescriptionController::class, 'destroyItem'])->name('visits.prescriptions.items.destroy');

    // Fase 2 Task 8: lampiran visit (private + signed URL).
    Route::post('visits/{visit}/attachments', [VisitAttachmentController::class, 'store'])->name('visits.attachments.store');
    Route::delete('visits/{visit}/attachments/{attachment}', [VisitAttachmentController::class, 'destroy'])->name('visits.attachments.destroy');
    Route::get('attachments/{attachment}/file', [VisitAttachmentController::class, 'file'])
        ->middleware('signed')->name('attachments.file');

    Route::resource('appointments', AppointmentController::class);
    Route::resource('services', ServiceController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('admins', AdminController::class)->except('show');
    Route::resource('doctors', DoctorController::class)->except('show');
    Route::resource('nurses', NurseController::class)->except('show');
    Route::resource('patients', MasterController::class);
    Route::resource('schedules', ScheduleController::class)->except(['create', 'show', 'edit', 'update']);
    Route::resource('medical-records', MedicalRecordController::class);
    // D-06c: nota = ledger final; edit/update/destroy dinonaktifkan.
    // Koreksi lewat alur usul-kunci-approve (CancellationController).
    Route::resource('transactions', TransactionController::class)->except(['edit', 'update', 'destroy']);
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

    // Fase 3 T1: tagihan (split dari transactions).
    Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::post('visits/{visit}/invoice', [InvoiceController::class, 'fromVisit'])->name('visits.invoice.store');
    Route::post('invoices/{invoice}/items', [InvoiceController::class, 'storeItem'])->name('invoices.items.store');
    Route::delete('invoices/{invoice}/items/{item}', [InvoiceController::class, 'destroyItem'])->name('invoices.items.destroy');
    Route::post('invoices/{invoice}/issue', [InvoiceController::class, 'issue'])->name('invoices.issue');
    Route::post('invoices/{invoice}/void', [InvoiceController::class, 'void'])->name('invoices.void');

    // Fase 3 T2: pembayaran + kwitansi.
    Route::post('invoices/{invoice}/payments', [PaymentController::class, 'store'])->name('invoices.payments.store');
    Route::get('payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');

    // Fase 3 T4: inventory ringan.
    Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('inventory/create', [InventoryController::class, 'create'])->name('inventory.create');
    Route::post('inventory', [InventoryController::class, 'store'])->name('inventory.store');
    Route::get('inventory/{item}', [InventoryController::class, 'show'])->name('inventory.show');
    Route::post('inventory/{item}/receive', [InventoryController::class, 'receive'])->name('inventory.receive');
    Route::post('inventory/{item}/dispense', [InventoryController::class, 'dispense'])->name('inventory.dispense');
    Route::post('inventory/{item}/batches/{batch}/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');

    // Fase 3 T5: beban operasional + laporan keuangan.
    Route::get('expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::post('expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::delete('expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
    Route::get('finance-report', [FinanceReportController::class, 'index'])->name('finance-report.index');

    // Fase 4 T1: cabang (CRUD manajemen + switch konteks semua user).
    Route::get('branches', [BranchController::class, 'index'])->name('branches.index');
    Route::post('branches', [BranchController::class, 'store'])->name('branches.store');
    Route::post('branches/{branch}/toggle', [BranchController::class, 'toggle'])->name('branches.toggle');
    Route::post('branch/switch', [BranchController::class, 'switch'])->name('branch.switch');

    // Fase 4 T2: SATUSEHAT (sandbox dulu; tanpa klaim produksi).
    Route::get('satusehat', [SatuSehatController::class, 'index'])->name('satusehat.index');
    Route::post('visits/{visit}/satusehat', [SatuSehatController::class, 'sync'])->name('visits.satusehat.sync');

    // Fase 4 T3: WhatsApp Official (driver log default).
    Route::get('whatsapp', [WhatsappController::class, 'index'])->name('whatsapp.index');
    Route::post('whatsapp/send', [WhatsappController::class, 'send'])->name('whatsapp.send');

    // Fase 3 T3: jasa medis dokter.
    Route::get('doctor-fees', [DoctorFeeController::class, 'index'])->name('doctor-fees.index');
    Route::post('doctor-fees/{fee}/pay', [DoctorFeeController::class, 'pay'])->name('doctor-fees.pay');
    Route::post('doctors/{doctor}/fee-rule', [DoctorFeeController::class, 'rule'])->name('doctors.fee-rule');

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

    // D-06g: route gaji dokter kini bernama agar bisa dipakai redirect/link.
    Route::get('salaries', [SalaryController::class, 'index'])->name('salaries.index');
    Route::resource('installments', InstallmentController::class);
});

require __DIR__.'/auth.php';
