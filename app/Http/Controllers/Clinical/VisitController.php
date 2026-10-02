<?php

namespace App\Http\Controllers\Clinical;

use App\Helpers\Audit;
use App\Http\Controllers\Controller;
use App\Models\DiagnosisCode;
use App\Models\Visit;
use App\Services\Clinical\VisitService;
use App\Services\Patient\MasterService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VisitController extends Controller
{
    public const TRANSITIONS = [
        Visit::STATUS_REGISTERED => [Visit::STATUS_WAITING],
        Visit::STATUS_WAITING => [Visit::STATUS_CALLED],
        Visit::STATUS_CALLED => [Visit::STATUS_IN_TREATMENT],
        Visit::STATUS_IN_TREATMENT => [Visit::STATUS_DONE],
        Visit::STATUS_DONE => [],
        Visit::STATUS_SIGNED => [],
    ];

    public function __construct(private VisitService $visits) {}

    /**
     * Antrian digabung ke Workspace (satu halaman, 2 tab:
     * Antrean Suster + Antrean Dokter). Route ini dipertahankan
     * sebagai redirect agar link/test lama tidak mati.
     */
    public function index(Request $request)
    {
        $this->authorize('read visit');

        return redirect()->route('workspace.index');
    }

    public function show($id)
    {
        $this->authorize('read visit');
        // Suster tidak membuka halaman visit sama sekali — triase via drawer workspace.
        abort_if(Auth::user()->hasRole('nurse'), 403, 'Halaman visit khusus dokter & manajemen.');

        $visit = Visit::with([
            'patient', 'doctor.user', 'appointment', 'branch', 'signer:id,name',
            'anamnesis', 'examination', 'odontogramFindings', 'diagnoses', 'treatments',
            'vitalSign', 'oralHealthIndex', 'consents.doctor.user', 'radiologyOrders.doctor.user',
            'treatmentPlans.items', 'prescriptions.items', 'attachments.uploader', 'invoices',
            'satusehatLogs' => fn ($q) => $q->orderBy('created_at', 'desc')->limit(5),
        ])->findOrFail($id);

        return view('pages.clinical.visit.show', [
            'visit' => $visit,
            'transitions' => self::TRANSITIONS,
            // Kamus kode resmi untuk dropdown (tanpa Livewire/JS).
            'icd10Groups' => DiagnosisCode::active()->system('ICD10')->orderBy('category')->orderBy('code')
                ->get(['id', 'code', 'display_id', 'category'])
                ->groupBy(fn ($c) => $c->category ?: 'Lainnya'),
            'icd9Groups' => DiagnosisCode::active()->system('ICD9')->orderBy('category')->orderBy('code')
                ->get(['id', 'code', 'display_id', 'category'])
                ->groupBy(fn ($c) => $c->category ?: 'Lainnya'),
        ]);
    }

    /**
     * Check-in: appointment terkonfirmasi → validasi kelengkapan data
     * pasien → visit WAITING (antrean perawat).
     *
     * Data wajib lengkap sebelum check-in selesai: nama, NIK 16 digit,
     * no. HP, tanggal lahir, jenis kelamin, alamat (jalan/desa), consent.
     */
    public function checkin($appointmentId)
    {
        $this->authorize('create visit');

        $appointment = DB::table('appointments')->where('id', $appointmentId)->first();
        abort_if(! $appointment, 404);
        abort_if(! $appointment->confirmed_at || $appointment->canceled_at, 422, 'Hanya appointment terkonfirmasi yang bisa check-in.');

        $exists = Visit::where('appointment_id', $appointmentId)->first();
        if ($exists) {
            return redirect()->route('workspace.index')
                ->with('info', 'Appointment ini sudah check-in.');
        }

        $missing = self::missingCheckinFields($appointment->patient_id);
        if ($missing !== []) {
            // Data kurang → kembali ke halaman janji temu + buka drawer
            // pelengkap (tanpa lempar ke halaman edit pasien).
            return redirect()->route('appointments.show', $appointment->id)
                ->withErrors(['checkin' => 'Lengkapi data pasien sebelum check-in: '.implode(', ', $missing).'.'])
                ->with('checkin_pending', true)
                ->with('info', 'Check-in ditunda — lengkapi data pasien dulu.');
        }

        return $this->doCheckin($appointment);
    }

    /**
     * Lengkapi data + check-in dalam 1 klik dari drawer janji temu.
     * Hanya field whitelist yang diupdate, sisanya tidak tersentuh.
     */
    public function checkinComplete(Request $request, $appointmentId)
    {
        $this->authorize('create visit');

        $appointment = DB::table('appointments')->where('id', $appointmentId)->first();
        abort_if(! $appointment, 404);
        abort_if(! $appointment->confirmed_at || $appointment->canceled_at, 422, 'Hanya appointment terkonfirmasi yang bisa check-in.');

        $exists = Visit::where('appointment_id', $appointmentId)->first();
        if ($exists) {
            return redirect()->route('workspace.index')
                ->with('info', 'Appointment ini sudah check-in.');
        }

        $validated = $request->validate([
            'nik' => 'nullable|digits:16',
            'phone' => 'nullable|regex:/^08[0-9]{8,13}$/',
            'birthdate' => 'nullable|date',
            'gender' => 'nullable|in:MALE,FEMALE',
            'street' => 'nullable|string|max:255',
            'village' => 'nullable|string|max:255',
            'satusehat_consent' => 'nullable|boolean',
        ]);

        // Normalisasi HP seperti form appointment (62…/8… → 08…).
        if (! empty($validated['phone'])) {
            $digits = preg_replace('/\D+/', '', $validated['phone']);
            if (str_starts_with($digits, '62')) {
                $digits = '0'.substr($digits, 2);
            } elseif (str_starts_with($digits, '8')) {
                $digits = '0'.$digits;
            }
            $validated['phone'] = '62'.$digits;
        }

        DB::transaction(function () use ($appointment, $validated) {
            $patientPatch = array_filter([
                'nik' => $validated['nik'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'birthdate' => $validated['birthdate'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'satusehat_consent' => array_key_exists('satusehat_consent', $validated) ? (bool) $validated['satusehat_consent'] : null,
            ], fn ($v) => $v !== null && $v !== '');

            if ($patientPatch !== []) {
                DB::table('patients')->where('id', $appointment->patient_id)->update($patientPatch);
            }

            $addressPatch = array_filter([
                'street' => $validated['street'] ?? null,
                'village' => $validated['village'] ?? null,
            ], fn ($v) => $v !== null && $v !== '');

            if ($addressPatch !== []) {
                DB::table('patient_addresses')->updateOrInsert(
                    ['patient_id' => $appointment->patient_id],
                    $addressPatch
                );
            }
        });

        $missing = self::missingCheckinFields($appointment->patient_id);
        if ($missing !== []) {
            return redirect()->route('appointments.show', $appointment->id)
                ->withErrors(['checkin' => 'Masih kurang: '.implode(', ', $missing).'.'])
                ->with('checkin_pending', true);
        }

        // Muat ulang appointment (snapshot nama pasien ikut data terbaru).
        $appointment = DB::table('appointments')->where('id', $appointmentId)->first();

        return $this->doCheckin($appointment);
    }

    /**
     * Buat visit WAITING dari appointment yang sudah valid.
     */
    private function doCheckin(object $appointment)
    {
        $visit = $this->visits->createVisit([
            'patient_id' => $appointment->patient_id,
            'appointment_id' => $appointment->id,
            'doctor_id' => $appointment->doctor_id,
            'visit_date' => $appointment->date,
            'clinical_status' => Visit::STATUS_WAITING,
        ]);

        if ($visit instanceof Exception) {
            return back()->withErrors(['error' => $visit->getMessage()]);
        }

        // Mendarat di workspace (papan antrean) + highlight pasien baru.
        return redirect()->route('workspace.index', ['highlight' => $visit->id])
            ->with('success', 'Check-in berhasil. '.$visit->patient->name.' ('.$visit->queueLabel().') masuk antrean perawat.');
    }

    /**
     * Field yang wajib ada agar pasien siap dilayani.
     * Memakai definisi kelengkapan MasterService + jenis kelamin.
     *
     * @return string[]
     */
    public static function missingCheckinFields(string $patientId): array
    {
        $row = DB::table('patients')
            ->leftJoin('patient_addresses', 'patient_addresses.patient_id', '=', 'patients.id')
            ->select('patients.*', 'patient_addresses.street', 'patient_addresses.village')
            ->where('patients.id', $patientId)
            ->first();

        if (! $row) {
            return ['Pasien tidak ditemukan'];
        }

        $missing = MasterService::missingFields($row);

        if (empty($row->name)) {
            $missing[] = 'Nama';
        }
        if (empty($row->gender)) {
            $missing[] = 'Jenis Kelamin';
        }

        return array_values(array_unique($missing));
    }

    /**
     * Maju status klinis (maju saja; SIGNED lewat rute sign Task 10).
     * WAITING = antrean perawat → CALLED = antrean dokter →
     * IN_TREATMENT = diperiksa → DONE = selesai diperiksa.
     * Saat DONE, pasien berikutnya di antrean dokter otomatis
     * dipanggil (CALLED → IN_TREATMENT).
     */
    public function updateStatus(Request $request, $id)
    {
        $this->authorize('update visit');

        $visit = Visit::findOrFail($id);
        abort_if($visit->isSigned(), 422, 'Visit SIGNED tidak bisa diubah.');

        $request->validate(['status' => 'required|string']);
        $allowed = self::TRANSITIONS[$visit->clinical_status] ?? [];
        abort_unless(in_array($request->status, $allowed, true), 422, 'Transisi status tidak valid.');

        // Progres klinis (mulai periksa / selesai) hanya dokter & manajemen.
        // Suster memajukan WAITING → CALLED lewat triase otomatis.
        if (in_array($request->status, [Visit::STATUS_IN_TREATMENT, Visit::STATUS_DONE], true)) {
            abort_unless(Auth::user()->hasRole('doctor|manajemen'), 403, 'Hanya dokter yang boleh memulai/menyelesaikan pemeriksaan.');
        }

        $visit->update(['clinical_status' => $request->status]);

        $autoNext = null;
        if ($request->status === Visit::STATUS_DONE) {
            $autoNext = $this->callNextDoctorPatient($visit);
        }

        $message = 'Status visit: '.$request->status.'.';
        if ($autoNext) {
            $message .= ' Pasien berikutnya '.$autoNext->visit_number.' otomatis masuk diperiksa.';
        }

        // Selesai periksa → kembali ke workspace (lanjut pasien berikut).
        if ($request->status === Visit::STATUS_DONE) {
            return redirect()->route('workspace.index')->with('success', $message);
        }

        return back()->with('success', $message);
    }

    /**
     * Auto-next: pasien tertua di antrean dokter (CALLED) untuk dokter +
     * tanggal yang sama langsung masuk IN_TREATMENT.
     */
    private function callNextDoctorPatient(Visit $finished): ?Visit
    {
        $next = Visit::whereDate('visit_date', $finished->visit_date->format('Y-m-d'))
            ->where('doctor_id', $finished->doctor_id)
            ->where('clinical_status', Visit::STATUS_CALLED)
            ->where('id', '!=', $finished->id)
            ->orderBy('created_at')
            ->first();

        if ($next) {
            $next->update(['clinical_status' => Visit::STATUS_IN_TREATMENT]);
        }

        return $next;
    }

    /**
     * Task 10: tanda tangan elektronik visit (SIGNED = kunci).
     * Syarat: status DONE + minimal 1 diagnosis ICD-10 (kelengkapan SATUSEHAT).
     * Menandai appointment tercatat agar konsisten dengan dashboard v1.
     */
    public function sign($id)
    {
        $this->authorize('sign visit');

        $visit = Visit::findOrFail($id);
        abort_if($visit->isSigned(), 422, 'Visit sudah SIGNED.');
        abort_unless($visit->clinical_status === Visit::STATUS_DONE, 422, 'Visit harus DONE sebelum ditandatangani.');
        abort_unless(
            $visit->diagnoses()->where('system', 'ICD10')->exists(),
            422,
            'Minimal 1 diagnosis ICD-10 sebelum sign (syarat SATUSEHAT).'
        );

        // Fase 1.1: informed consent wajib tercatat & disetujui sebelum rekam
        // medis dikunci (Permenkes 24/2022 Pasal 34).
        abort_unless(
            $visit->consents()->where('granted', true)->exists(),
            422,
            'Informed consent yang disetujui pasien wajib tercatat sebelum sign (Permenkes 24/2022).'
        );

        $visit->update([
            'clinical_status' => Visit::STATUS_SIGNED,
            'signed_at' => now(),
            'signed_by' => Auth::id(),
        ]);

        if ($visit->appointment_id) {
            DB::table('appointments')->where('id', $visit->appointment_id)->update([
                'recorded_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // Permenkes 24/2022: e-sign dokter tercatat (who/when).
        Audit::log('sign-visit', 'visit', $visit->id, ['clinical_status' => Visit::STATUS_DONE], ['clinical_status' => Visit::STATUS_SIGNED]);

        return back()->with('success', 'Visit '.$visit->visit_number.' ditandatangani dan dikunci.');
    }
}
