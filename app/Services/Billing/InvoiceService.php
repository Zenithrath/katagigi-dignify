<?php

namespace App\Services\Billing;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Visit;
use App\Services\Service;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class InvoiceService extends Service
{
    /**
     * Nomor tagihan: INV-(2 digit tahun)(5 digit urutan).
     */
    public function nextNumber(): string
    {
        $prefix = 'INV-'.date('y');

        $max = DB::table('invoices')
            ->where('number', 'like', $prefix.'%')
            ->max('number');

        $seq = $max ? ((int) substr($max, strlen($prefix))) + 1 : 1;

        return $prefix.str_pad((string) $seq, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Buat tagihan DRAFT dari visit (item = tindakan visit).
     */
    public function createFromVisit(Visit $visit): Invoice|Exception
    {
        try {
            return DB::transaction(function () use ($visit) {
                $invoice = Invoice::create([
                    'id' => (string) Str::uuid(),
                    'number' => $this->nextNumber(),
                    'patient_id' => $visit->patient_id,
                    'visit_id' => $visit->id,
                    'appointment_id' => $visit->appointment_id,
                    'doctor_id' => $visit->doctor_id,
                    'status' => Invoice::STATUS_DRAFT,
                ]);

                foreach ($visit->treatments as $treatment) {
                    InvoiceItem::create([
                        'id' => (string) Str::uuid(),
                        'invoice_id' => $invoice->id,
                        'item_type' => InvoiceItem::TYPE_TREATMENT,
                        'description' => $treatment->procedure.($treatment->tooth_fdi ? ' ('.$treatment->tooth_fdi.')' : ''),
                        'tooth_fdi' => $treatment->tooth_fdi,
                        'reference_code' => $treatment->code,
                        'quantity' => $treatment->quantity,
                        'unit_price' => $treatment->unit_price,
                        'amount' => $treatment->quantity * $treatment->unit_price,
                    ]);
                }

                $invoice->recalculate();

                return $invoice->fresh();
            });
        } catch (Throwable $th) {
            $this->writeLog('InvoiceService::createFromVisit', $th);

            return new Exception($th->getMessage(), 500);
        }
    }

    public function addItem(Invoice $invoice, array $data): InvoiceItem|Exception
    {
        if (! $invoice->isEditable()) {
            return new Exception('Hanya tagihan DRAFT yang bisa diubah.', 422);
        }

        try {
            return DB::transaction(function () use ($invoice, $data) {
                $item = InvoiceItem::create([
                    'id' => (string) Str::uuid(),
                    'invoice_id' => $invoice->id,
                    'item_type' => $data['item_type'] ?? InvoiceItem::TYPE_OTHER,
                    'description' => $data['description'],
                    'tooth_fdi' => $data['tooth_fdi'] ?? '',
                    'reference_code' => $data['reference_code'] ?? null,
                    'quantity' => $data['quantity'] ?? 1,
                    'unit_price' => $data['unit_price'] ?? 0,
                    'amount' => ($data['quantity'] ?? 1) * ($data['unit_price'] ?? 0),
                ]);
                $invoice->recalculate();

                return $item;
            });
        } catch (Throwable $th) {
            $this->writeLog('InvoiceService::addItem', $th);

            return new Exception($th->getMessage(), 500);
        }
    }

    public function removeItem(Invoice $invoice, string $itemId): bool|Exception
    {
        if (! $invoice->isEditable()) {
            return new Exception('Hanya tagihan DRAFT yang bisa diubah.', 422);
        }

        try {
            return DB::transaction(function () use ($invoice, $itemId) {
                $invoice->items()->where('id', $itemId)->firstOrFail()->delete();
                $invoice->recalculate();

                return true;
            });
        } catch (Throwable $th) {
            $this->writeLog('InvoiceService::removeItem', $th);

            return new Exception($th->getMessage(), 500);
        }
    }

    /**
     * Terbitkan tagihan. Syarat: ada item + visit SIGNED (bila dari visit).
     */
    public function issue(Invoice $invoice, array $data = []): Invoice|Exception
    {
        if ($invoice->status !== Invoice::STATUS_DRAFT) {
            return new Exception('Hanya tagihan DRAFT yang bisa diterbitkan.', 422);
        }
        if ($invoice->items()->count() === 0) {
            return new Exception('Tagihan kosong tidak bisa diterbitkan.', 422);
        }
        if ($invoice->visit_id) {
            $visit = Visit::find($invoice->visit_id);
            if (! $visit || ! $visit->isSigned()) {
                return new Exception('Visit harus SIGNED sebelum tagihan diterbitkan.', 422);
            }
        }

        try {
            return DB::transaction(function () use ($invoice, $data) {
                $invoice->update([
                    'discount' => $data['discount'] ?? 0,
                    'tax' => $data['tax'] ?? 0,
                    'status' => Invoice::STATUS_ISSUED,
                    'issued_by' => Auth::id(),
                    'issued_at' => now(),
                    'notes' => $data['notes'] ?? $invoice->notes,
                ]);
                $invoice->recalculate();
                $invoice->visit?->update(['billing_status' => Visit::BILLING_BILLED]);

                return $invoice->fresh();
            });
        } catch (Throwable $th) {
            $this->writeLog('InvoiceService::issue', $th);

            return new Exception($th->getMessage(), 500);
        }
    }

    /**
     * Batalkan tagihan. Hanya bila belum ada pembayaran (refund = Fase lanjut).
     */
    public function void(Invoice $invoice): Invoice|Exception
    {
        if (in_array($invoice->status, [Invoice::STATUS_PAID, Invoice::STATUS_VOID], true)) {
            return new Exception('Tagihan LUNAS/VOID tidak bisa dibatalkan.', 422);
        }
        if ($invoice->amountPaid() > 0) {
            return new Exception('Tagihan dengan pembayaran tidak bisa dibatalkan.', 422);
        }

        $invoice->update(['status' => Invoice::STATUS_VOID]);

        return $invoice->fresh();
    }
}
