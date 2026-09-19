<?php

namespace App\Services\Billing;

use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\PaymentReceipt;
use App\Services\Service;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class PaymentService extends Service
{
    public function nextReceiptNumber(): string
    {
        $prefix = 'RCP-'.date('y');

        $max = DB::table('payment_receipts')
            ->where('number', 'like', $prefix.'%')
            ->max('number');

        $seq = $max ? ((int) substr($max, strlen($prefix))) + 1 : 1;

        return $prefix.str_pad((string) $seq, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Bayar tagihan (boleh cicil). Otomatis: kwitansi + status
     * PARTIALLY_PAID/PAID. Kelebihan bayar ditolak.
     */
    public function pay(Invoice $invoice, array $data): InvoicePayment|Exception
    {
        if (! in_array($invoice->status, [Invoice::STATUS_ISSUED, Invoice::STATUS_PARTIALLY_PAID], true)) {
            return new Exception('Hanya tagihan terbit yang bisa dibayar.', 422);
        }

        $amount = (float) ($data['amount'] ?? 0);
        if ($amount <= 0) {
            return new Exception('Nominal harus lebih dari nol.', 422);
        }
        if ($amount - $invoice->amountDue() > 0.009) {
            return new Exception('Nominal melebihi sisa tagihan Rp'.number_format($invoice->amountDue(), 0, ',', '.').'.', 422);
        }

        try {
            return DB::transaction(function () use ($invoice, $data, $amount) {
                $payment = InvoicePayment::create([
                    'id' => (string) Str::uuid(),
                    'invoice_id' => $invoice->id,
                    'amount' => $amount,
                    'method' => $data['method'],
                    'paid_at' => $data['paid_at'] ?? now(),
                    'received_by' => Auth::id(),
                    'notes' => $data['notes'] ?? null,
                ]);

                PaymentReceipt::create([
                    'id' => (string) Str::uuid(),
                    'payment_id' => $payment->id,
                    'number' => $this->nextReceiptNumber(),
                ]);

                $invoice->refresh();
                $invoice->update([
                    'status' => $invoice->amountDue() <= 0.009 ? Invoice::STATUS_PAID : Invoice::STATUS_PARTIALLY_PAID,
                ]);

                // Fase 3 T3: tagihan lunas → posting jasa medis dokter.
                if ($invoice->status === Invoice::STATUS_PAID) {
                    (new DoctorFeeService)->postForInvoice($invoice);
                }

                return $payment;
            });
        } catch (Throwable $th) {
            $this->writeLog('PaymentService::pay', $th);

            return new Exception($th->getMessage(), 500);
        }
    }
}
