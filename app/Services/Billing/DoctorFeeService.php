<?php

namespace App\Services\Billing;

use App\Models\DoctorFee;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\Service;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class DoctorFeeService extends Service
{
    /**
     * Posting fee saat tagihan LUNAS. Item radiologi (ICD-9 87.*) memakai
     * tarif rontgen, sisanya tarif tindakan. Idempoten per invoice.
     */
    public function postForInvoice(Invoice $invoice): ?DoctorFee
    {
        if ($invoice->status !== Invoice::STATUS_PAID || ! $invoice->doctor_id) {
            return null;
        }
        if (DoctorFee::where('invoice_id', $invoice->id)->exists()) {
            return DoctorFee::where('invoice_id', $invoice->id)->first();
        }

        $rule = DB::table('doctor_fee_rules')->where('doctor_id', $invoice->doctor_id)->first();
        $percentage = (float) ($rule->percentage ?? 30);
        $xrayPercentage = (float) ($rule->xray_percentage ?? 20);

        $xray = (float) $invoice->items()
            ->where('item_type', InvoiceItem::TYPE_TREATMENT)
            ->where('reference_code', 'like', '87%')
            ->sum('amount');
        $base = (float) $invoice->items()
            ->where('item_type', InvoiceItem::TYPE_TREATMENT)
            ->where(fn ($q) => $q->where('reference_code', 'not like', '87%')->orWhereNull('reference_code'))
            ->sum('amount');

        try {
            return DoctorFee::create([
                'id' => (string) Str::uuid(),
                'doctor_id' => $invoice->doctor_id,
                'invoice_id' => $invoice->id,
                'base_amount' => $base + $xray,
                'percentage' => $percentage,
                'fee_amount' => $base * $percentage / 100 + $xray * $xrayPercentage / 100,
                'status' => DoctorFee::STATUS_UNPAID,
            ]);
        } catch (Throwable $th) {
            $this->writeLog('DoctorFeeService::postForInvoice', $th);

            return null;
        }
    }
}
