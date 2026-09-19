<?php

namespace App\Services\Clinical;

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
        try {
            return DB::transaction(function () use ($data) {
                return Visit::create([
                    'id' => (string) Str::uuid(),
                    'visit_number' => $this->nextVisitNumber(),
                    'branch_id' => $data['branch_id'] ?? $this->defaultBranchId(),
                    'patient_id' => $data['patient_id'],
                    'appointment_id' => $data['appointment_id'] ?? null,
                    'doctor_id' => $data['doctor_id'],
                    'visit_date' => $data['visit_date'] ?? date('Y-m-d'),
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
        return DB::table('branches')->where('is_active', true)->orderBy('code')->value('id');
    }
}
