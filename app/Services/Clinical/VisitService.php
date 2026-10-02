<?php

namespace App\Services\Clinical;

use App\Helpers\BranchContext;
use App\Models\Visit;
use App\Services\Service;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class VisitService extends Service
{
    /**
     * Nomor visit: VST-(2 digit tahun)(5 digit urutan). Portabel antar DB
     * (pola yang sama dengan TransactionService::nextSequence).
     */
    public function nextVisitNumber(): string
    {
        $prefix = 'VST-'.date('y');

        $max = DB::table('visits')
            ->where('visit_number', 'like', $prefix.'%')
            ->max('visit_number');

        $seq = $max ? ((int) substr($max, strlen($prefix))) + 1 : 1;

        return $prefix.str_pad((string) $seq, 5, '0', STR_PAD_LEFT);
    }

    public function createVisit(array $data): Visit|Exception
    {
        // Alur pelayanan: setiap visit wajib berasal dari appointment
        // (tidak boleh langsung masuk antrean tanpa janji temu).
        if (empty($data['appointment_id'])) {
            return new Exception('Visit wajib memiliki appointment.', 422);
        }

        try {
            return DB::transaction(function () use ($data) {
                $visitDate = $data['visit_date'] ?? date('Y-m-d');
                // whereDate: SQLite menyimpan kolom date sebagai 'Y-m-d H:i:s',
                // where biasa tidak pernah cocok.
                $nextQueueNo = ((int) DB::table('visits')
                    ->where('doctor_id', $data['doctor_id'])
                    ->whereDate('visit_date', $visitDate)
                    ->max('queue_no')) + 1;

                return Visit::create([
                    'id' => (string) Str::uuid(),
                    'visit_number' => $this->nextVisitNumber(),
                    'queue_no' => $nextQueueNo,
                    'branch_id' => $data['branch_id'] ?? $this->defaultBranchId(),
                    'patient_id' => $data['patient_id'],
                    'appointment_id' => $data['appointment_id'],
                    'doctor_id' => $data['doctor_id'],
                    'visit_date' => $visitDate,
                    'clinical_status' => $data['clinical_status'] ?? Visit::STATUS_REGISTERED,
                    'billing_status' => Visit::BILLING_UNBILLED,
                    'notes' => $data['notes'] ?? null,
                ]);
            });
        } catch (Throwable $th) {
            $this->writeLog('VisitService::createVisit', $th);

            return new Exception($th->getMessage(), 500);
        }
    }

    public function defaultBranchId(): ?string
    {
        return BranchContext::currentId() ?? BranchContext::defaultId();
    }
}
