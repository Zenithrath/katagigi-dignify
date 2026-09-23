<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * F3-T4: inventory FIFO + opname + gate.
 */
class InventoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function item(): InventoryItem
    {
        return InventoryItem::create([
            'id' => (string) Str::uuid(),
            'code' => 'KOM-001',
            'name' => 'Komposit A2',
            'unit' => 'pcs',
            'min_stock' => 10,
            'is_active' => true,
        ]);
    }

    public function test_receive_dispense_fifo_and_adjust(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->first();
        $item = $this->item();

        $this->actingAs($admin)->post(route('inventory.receive', $item->id), [
            'batch_no' => 'B1',
            'expiry_date' => date('Y-m-d', strtotime('+2 years')),
            'quantity' => 20,
            'buy_price' => 50000,
        ])->assertRedirect();
        $this->actingAs($admin)->post(route('inventory.receive', $item->id), [
            'batch_no' => 'B2',
            'expiry_date' => date('Y-m-d', strtotime('+1 year')),
            'quantity' => 10,
        ])->assertRedirect();
        $this->assertEquals(30.0, $item->fresh()->currentStock());

        // FIFO: batch kedaluwarsa tercepat (B2) terkuras dulu.
        $this->actingAs($admin)->post(route('inventory.dispense', $item->id), [
            'quantity' => 12,
            'reference' => 'Resep-001',
        ])->assertRedirect();
        $item = $item->fresh();
        $this->assertEquals(18.0, $item->currentStock());
        $this->assertEquals(0.0, $item->batches()->where('batch_no', 'B2')->first()->quantity);
        $this->assertEquals(18.0, $item->batches()->where('batch_no', 'B1')->first()->quantity);

        // Stok tak cukup ditolak.
        $this->actingAs($admin)->post(route('inventory.dispense', $item->id), [
            'quantity' => 999,
        ])->assertRedirect();
        $this->assertEquals(18.0, $item->fresh()->currentStock());

        // Opname batch B1 ke 15.
        $b1 = $item->batches()->where('batch_no', 'B1')->first();
        $this->actingAs($admin)->post(route('inventory.adjust', [$item->id, $b1->id]), [
            'quantity' => 15,
            'notes' => 'Opname bulanan',
        ])->assertRedirect();
        $this->assertEquals(15.0, $item->fresh()->currentStock());
        $this->assertFalse($item->fresh()->isLowStock());
    }

    public function test_low_stock_flag_and_gates(): void
    {
        $nurse = User::where('email', 'nurse@gmail.com')->first();
        $item = $this->item();
        $this->assertTrue($item->isLowStock());

        // Nurse boleh baca, tak boleh kelola.
        $this->actingAs($nurse)->get(route('inventory.index'))->assertOk();
        $this->actingAs($nurse)->get(route('inventory.show', $item->id))->assertOk();
        $this->actingAs($nurse)->post(route('inventory.receive', $item->id), [
            'batch_no' => 'X',
            'quantity' => 5,
        ])->assertForbidden();
    }
}
