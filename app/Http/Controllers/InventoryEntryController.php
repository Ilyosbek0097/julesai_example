<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInventoryEntryRequest;
use App\Http\Requests\UpdateInventoryEntryRequest;
use App\Models\Inventory;
use App\Models\InventoryEntry;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class InventoryEntryController extends Controller
{
    public function index()
    {
        $entries = InventoryEntry::with('inventory', 'supplier')->latest()->paginate(15);
        return Inertia::render('InventoryEntries/Index', ['entries' => $entries]);
    }

    public function create()
    {
        // This method provides the data for the page I already built.
        return Inertia::render('InventoryEntries/Create', [
            'categories' => \App\Models\Category::all(['id', 'name']),
            'units' => \App\Models\Unit::all(['id', 'name']),
            'products' => Inventory::all(['id', 'name']),
            'suppliers' => Supplier::orderBy('name')->get()->map(fn($item) => ['label' => $item->name, 'value' => $item->id]),
        ]);
    }

    public function store(StoreInventoryEntryRequest $request)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            foreach ($validated['items'] as $item) {
                InventoryEntry::create([
                    'inventory_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'entry_date' => now(),
                    'remaining_quantity' => $item['quantity'],
                    'comment' => $validated['comment'],
                    'user_id' => Auth::id(),
                    'entry_number' => 'K-' . time() . '-' . $item['product_id'],
                    'supplier_id' => $validated['supplier_id'],
                ]);
            }
        });

        return redirect()->route('inventory-entries.index')->with('message', 'Kirim muvaffaqiyatli yaratildi.');
    }

    public function edit(InventoryEntry $inventoryEntry)
    {
        return Inertia::render('InventoryEntries/Edit', [
            'entry' => $inventoryEntry,
            'inventories' => Inventory::all(['id', 'name']),
            'suppliers' => Supplier::orderBy('name')->get()->map(fn($item) => ['label' => $item->name, 'value' => $item->id]),
        ]);
    }

    public function update(UpdateInventoryEntryRequest $request, InventoryEntry $inventoryEntry)
    {
        $inventoryEntry->update($request->validated());

        return redirect()->route('inventory-entries.index')->with('message', 'Kirim muvaffaqiyatli yangilandi.');
    }

    public function destroy(InventoryEntry $inventoryEntry)
    {
        $inventoryEntry->delete();
        return back()->with('message', 'Kirim o\'chirildi.');
    }
}
