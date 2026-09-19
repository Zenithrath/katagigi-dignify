<?php

namespace App\Services\Inventory;

use App\Models\InventoryItem;
use App\Models\StockBatch;
use App\Models\StockMovement;
use App\Services\Service;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class StockService extends Service
{
    public function receive(InventoryItem $item, array $data): StockBatch|Exception
    {
        if (($data['quantity'] ?? 0) <= 0) {
            return new Exception('Jumlah terima harus lebih dari nol.', 422);
        }

        try {
            return DB::transaction(function () use ($item, $data) {
                $batch = StockBatch::firstOrNew([
                    'inventory_item_id' => $item->id,
                    'batch_no' => $data['batch_no'],
                ]);
                if (! $batch->exists) {
                    $batch->id = (string) Str::uuid();
                }
                $batch->expiry_date = $data['expiry_date'] ?? $batch->expiry_date;
                $batch->buy_price = $data['buy_price'] ?? $batch->buy_price ?? 0;
                $batch->quantity = (float) $batch->quantity + (float) $data['quantity'];
                $batch->save();

                StockMovement::create([
                    'id' => (string) Str::uuid(),
                    'inventory_item_id' => $item->id,
                    'batch_id' => $batch->id,
                    'type' => StockMovement::TYPE_IN,
                    'quantity' => (float) $data['quantity'],
                    'reference' => $data['reference'] ?? null,
                    'notes' => $data['notes'] ?? null,
                    'created_by' => Auth::id(),
                ]);

                return $batch->fresh();
            });
        } catch (Throwable $th) {
            $this->writeLog('StockService::receive', $th);

            return new Exception($th->getMessage(), 500);
        }
    }

    /**
     * Keluarkan stok FIFO (kedaluwarsa tercepat dulu, tanpa batch abaikan).
     */
    public function dispense(InventoryItem $item, float $quantity, ?string $reference = null, ?string $notes = null): bool|Exception
    {
        if ($quantity <= 0) {
            return new Exception('Jumlah keluar harus lebih dari nol.', 422);
        }
        if ($item->currentStock() < $quantity) {
            return new Exception('Stok tidak cukup (tersisa '.$item->currentStock().' '.$item->unit.').', 422);
        }

        try {
            return DB::transaction(function () use ($item, $quantity, $reference, $notes) {
                $remaining = $quantity;
                $batches = $item->batches()->where('quantity', '>', 0)->orderByRaw('expiry_date IS NULL, expiry_date ASC')->lockForUpdate()->get();

                foreach ($batches as $batch) {
                    if ($remaining <= 0) {
                        break;
                    }
                    $take = min((float) $batch->quantity, $remaining);
                    $batch->decrement('quantity', $take);
                    StockMovement::create([
                        'id' => (string) Str::uuid(),
                        'inventory_item_id' => $item->id,
                        'batch_id' => $batch->id,
                        'type' => StockMovement::TYPE_OUT,
                        'quantity' => $take,
                        'reference' => $reference,
                        'notes' => $notes,
                        'created_by' => Auth::id(),
                    ]);
                    $remaining -= $take;
                }

                return true;
            });
        } catch (Throwable $th) {
            $this->writeLog('StockService::dispense', $th);

            return new Exception($th->getMessage(), 500);
        }
    }

    /**
     * Opname: setel qty batch ke nilai hasil hitung fisik.
     */
    public function adjust(StockBatch $batch, float $counted, ?string $notes = null): StockBatch|Exception
    {
        try {
            return DB::transaction(function () use ($batch, $counted, $notes) {
                $delta = $counted - (float) $batch->quantity;
                $batch->update(['quantity' => $counted]);
                StockMovement::create([
                    'id' => (string) Str::uuid(),
                    'inventory_item_id' => $batch->inventory_item_id,
                    'batch_id' => $batch->id,
                    'type' => StockMovement::TYPE_ADJUST,
                    'quantity' => $delta,
                    'notes' => $notes ?? 'Opname',
                    'created_by' => Auth::id(),
                ]);

                return $batch->fresh();
            });
        } catch (Throwable $th) {
            $this->writeLog('StockService::adjust', $th);

            return new Exception($th->getMessage(), 500);
        }
    }
}
