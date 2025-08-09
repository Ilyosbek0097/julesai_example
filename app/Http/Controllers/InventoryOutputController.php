<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInventoryOutputRequest;
use App\Http\Requests\UpdateInventoryOutputRequest;
use App\Models\InventoryEntry;
use App\Models\InventoryOutput;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class InventoryOutputController extends Controller
{
    public function index()
    {
        $outputs = InventoryOutput::latest()->paginate(10);

        return Inertia::render('InventoryOutputs/Index', [
            'outputs' => $outputs,
        ]);
    }

    public function create()
    {
        // We need inventory entries that have stock to be able to output them
        $entries = InventoryEntry::where('quantity', '>', 0)->get()->map(function ($entry) {
            return [
                'value' => $entry->id,
                'label' => $entry->product_name . ' (Mavjud: ' . $entry->quantity . ')',
                'quantity' => $entry->quantity, // Pass max quantity to frontend
            ];
        });

        return Inertia::render('InventoryOutputs/Create', [
            'entries' => $entries,
        ]);
    }

    public function store(StoreInventoryOutputRequest $request)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            $totalPrice = collect($validated['items'])->sum(function ($item) {
                return $item['quantity'] * $item['price'];
            });

            $output = InventoryOutput::create([
                'output_date' => $validated['output_date'],
                'comment' => $validated['comment'],
                'user_id' => Auth::id(),
                'total_price' => $totalPrice,
                'output_number' => 'CH-' . time(), // Simple unique number
            ]);

            foreach ($validated['items'] as $item) {
                $entry = InventoryEntry::find($item['inventory_entry_id']);
                if ($entry && $entry->quantity >= $item['quantity']) {
                    $output->output_details()->create($item);
                    $entry->decrement('quantity', $item['quantity']);
                } else {
                    // This should be caught by validation, but as a safeguard
                    throw new \Exception('Not enough stock for one of the items.');
                }
            }
        });

        return redirect()->route('inventory-outputs.index')->with('message', 'Chiqim muvaffaqiyatli yaratildi.');
    }

    public function show(InventoryOutput $inventoryOutput)
    {
        $inventoryOutput->load('output_details.inventoryEntry');

        return Inertia::render('InventoryOutputs/Show', [
            'output' => $inventoryOutput,
        ]);
    }

    public function edit(InventoryOutput $inventoryOutput)
    {
        $inventoryOutput->load('output_details');

        $entries = InventoryEntry::where('quantity', '>', 0)
            // Also include entries that are already in this output, even if their stock is now 0
            ->orWhereIn('id', $inventoryOutput->output_details->pluck('inventory_entry_id'))
            ->get()->map(function ($entry) {
                return [
                    'value' => $entry->id,
                    'label' => $entry->product_name . ' (Mavjud: ' . $entry->quantity . ')',
                    'quantity' => $entry->quantity,
                ];
            });

        return Inertia::render('InventoryOutputs/Edit', [
            'output' => $inventoryOutput,
            'entries' => $entries,
        ]);
    }

    public function update(UpdateInventoryOutputRequest $request, InventoryOutput $inventoryOutput)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $inventoryOutput) {
            // Recalculate total price
            $totalPrice = collect($validated['items'])->sum(function ($item) {
                return $item['quantity'] * $item['price'];
            });

            // Restore old quantities first
            foreach ($inventoryOutput->output_details as $detail) {
                InventoryEntry::find($detail->inventory_entry_id)->increment('quantity', $detail->quantity);
            }

            // Delete old details
            $inventoryOutput->output_details()->delete();

            // Update parent model
            $inventoryOutput->update([
                'output_date' => $validated['output_date'],
                'comment' => $validated['comment'],
                'total_price' => $totalPrice,
            ]);

            // Create new details and decrement new quantities
            foreach ($validated['items'] as $item) {
                $entry = InventoryEntry::find($item['inventory_entry_id']);
                if ($entry && $entry->quantity >= $item['quantity']) {
                    $inventoryOutput->output_details()->create($item);
                    $entry->decrement('quantity', $item['quantity']);
                } else {
                    throw new \Exception('Not enough stock for one of the items during update.');
                }
            }
        });

        return redirect()->route('inventory-outputs.index')->with('message', 'Chiqim muvaffaqiyatli yangilandi.');
    }
}
