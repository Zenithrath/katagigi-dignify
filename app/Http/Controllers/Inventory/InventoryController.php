<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\StockBatch;
use App\Services\Inventory\StockService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InventoryController extends Controller
{
    public function __construct(private StockService $stock) {}

    public function index(Request $request)
    {
        $this->authorize('read inventory');

        $items = InventoryItem::with('batches')
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->q.'%')->orWhere('code', 'like', '%'.$request->q.'%'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('pages.inventory.index', [
            'items' => $items,
            'lowCount' => InventoryItem::all()->filter(fn ($i) => $i->isLowStock())->count(),
        ]);
    }

    public function create()
    {
        $this->authorize('manage inventory');

        return view('pages.inventory.form', [
            'type' => 'create',
            'action' => route('inventory.store'),
            'data' => new InventoryItem,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('manage inventory');

        $validated = $request->validate([
            'code' => 'required|string|max:32|unique:inventory_items,code',
            'name' => 'required|string|max:255',
            'unit' => 'nullable|string|max:16',
            'min_stock' => 'nullable|numeric|min:0',
        ]);

        InventoryItem::create([
            'id' => (string) Str::uuid(),
            'unit' => $validated['unit'] ?? 'pcs',
            'min_stock' => $validated['min_stock'] ?? 0,
            'is_active' => true,
            'code' => $validated['code'],
            'name' => $validated['name'],
        ]);

        return redirect()->route('inventory.index')->with('success', 'Item inventory dibuat.');
    }

    public function show($id)
    {
        $this->authorize('read inventory');

        $item = InventoryItem::with(['batches', 'movements.batch'])->findOrFail($id);

        return view('pages.inventory.show', ['item' => $item]);
    }

    public function receive(Request $request, $id)
    {
        $this->authorize('manage inventory');
        $item = InventoryItem::findOrFail($id);

        $validated = $request->validate([
            'batch_no' => 'required|string|max:64',
            'expiry_date' => 'nullable|date',
            'quantity' => 'required|numeric|min:1',
            'buy_price' => 'nullable|numeric|min:0',
            'reference' => 'nullable|string|max:255',
        ]);

        $result = $this->stock->receive($item, $validated);
        if ($result instanceof Exception) {
            return back()->withErrors(['error' => $result->getMessage()]);
        }

        return back()->with('success', 'Stok masuk tercatat.');
    }

    public function dispense(Request $request, $id)
    {
        $this->authorize('manage inventory');
        $item = InventoryItem::findOrFail($id);

        $validated = $request->validate([
            'quantity' => 'required|numeric|min:1',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $result = $this->stock->dispense($item, (float) $validated['quantity'], $validated['reference'] ?? null, $validated['notes'] ?? null);
        if ($result instanceof Exception) {
            return back()->withErrors(['error' => $result->getMessage()]);
        }

        return back()->with('success', 'Stok keluar tercatat (FIFO).');
    }

    public function adjust(Request $request, $id, $batchId)
    {
        $this->authorize('manage inventory');
        $item = InventoryItem::findOrFail($id);
        $batch = StockBatch::where('inventory_item_id', $item->id)->where('id', $batchId)->firstOrFail();

        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $result = $this->stock->adjust($batch, (float) $validated['quantity'], $validated['notes'] ?? null);
        if ($result instanceof Exception) {
            return back()->withErrors(['error' => $result->getMessage()]);
        }

        return back()->with('success', 'Opname tersimpan.');
    }
}
