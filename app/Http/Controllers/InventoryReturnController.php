<?php

namespace App\Http\Controllers;

use App\Models\InventoryReturn;
use App\Models\InventoryOutput;
use App\Models\OutputDetail;
use App\Models\ReturnDetail;
use App\Http\Requests\StoreInventoryReturnRequest;
use App\Http\Requests\UpdateInventoryReturnRequest;
use App\Service\UniqueNumberGenerator;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class InventoryReturnController extends Controller
{
    public function index()
    {
        $returns = InventoryReturn::with('user')->latest()->paginate(10);
        return Inertia::render('InventoryReturns/Index', ['returns' => $returns]);
    }

    public function create()
    {
        $outputs = InventoryOutput::with(['output_details.inventoryEntry.inventory.unit'])
            ->whereHas('output_details', function ($query) {
                $query->whereRaw('quantity > returned_quantity');
            })
            ->latest()
            ->get()
            ->map(function ($output) {
                return [
                    'id' => $output->id,
                    'output_number' => $output->output_number,
                    'formatted_date' => $output->formatted_date,
                    'comment' => $output->comment,
                    'output_details' => $output->output_details
                        ->map(function ($detail) {
                            $remaining = $detail->quantity - $detail->returned_quantity;
                            return [
                                'id' => $detail->id,
                                'original_quantity' => $detail->quantity,
                                'returned_quantity' => $detail->returned_quantity,
                                'remaining_returnable' => $remaining,
                                'price' => $detail->price,
                                'created_at' => $detail->created_at->format('d.m.Y'),
                                'inventory_entry' => [
                                    'product_name' => $detail->inventoryEntry->inventory->name . ' (' . $detail->inventoryEntry->inventory->unit->name . ')',
                                ],
                            ];
                        })->filter(fn ($detail) => $detail['remaining_returnable'] > 0),
                ];
            })->filter(fn ($output) => $output['output_details']->isNotEmpty());

        return inertia('InventoryReturns/Create', [
            'outputs' => $outputs
        ]);
    }

    public function store(StoreInventoryReturnRequest $request, UniqueNumberGenerator $uniqueNumberGenerator)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $uniqueNumberGenerator) {
            $return = InventoryReturn::create([
                'return_number' => $uniqueNumberGenerator->generateCustom(InventoryReturn::class, 'return_number'),
                'return_date' => now(),
                'comment' => $data['comment'] ?? null,
                'user_id' => auth()->id(),
            ]);

            foreach ($data['returns'] as $item) {
                $detail = OutputDetail::findOrFail($item['output_detail_id']);

                ReturnDetail::create([
                    'inventory_return_id' => $return->id,
                    'output_detail_id' => $detail->id,
                    'quantity' => $item['quantity'],
                    'price' => $detail->price,
                    'total' => $item['quantity'] * $detail->price,
                ]);

                // Update the returned quantity on the original output detail
                $detail->increment('returned_quantity', $item['quantity']);

                // Update the stock (remaining_quantity) on the inventory entry
                $entry = $detail->inventoryEntry;
                $entry->increment('remaining_quantity', $item['quantity']);
            }
        });

        return redirect()->route('inventory-returns.index')->with('success', 'Qaytarish muvaffaqiyatli amalga oshirildi.');
    }

    public function show(InventoryReturn $inventoryReturn)
    {
        $inventoryReturn->load('return_details.outputDetail.inventoryEntry.inventory.unit');
        return Inertia::render('InventoryReturns/Show', ['return' => $inventoryReturn]);
    }

    public function destroy(InventoryReturn $inventoryReturn)
    {
        DB::transaction(function () use ($inventoryReturn) {
            foreach ($inventoryReturn->return_details as $returnDetail) {
                // Restore stock
                $outputDetail = $returnDetail->outputDetail;
                $entry = $outputDetail->inventoryEntry;
                $entry->decrement('remaining_quantity', $returnDetail->quantity);

                // Decrement returned quantity
                $outputDetail->decrement('returned_quantity', $returnDetail->quantity);
            }
            $inventoryReturn->delete();
        });

        return redirect()->route('inventory-returns.index')->with('success', 'Qaytarish bekor qilindi.');
    }

    public function edit(InventoryReturn $inventoryReturn)
    {
        // For now, editing a return is complex and not fully implemented.
        // We will redirect to the show page. A full implementation would require a dedicated component.
        return redirect()->route('inventory-returns.show', $inventoryReturn);
    }

    public function update(UpdateInventoryReturnRequest $request, InventoryReturn $inventoryReturn)
    {
        // Simplified update - only updates the comment for now.
        $inventoryReturn->update($request->validated());

        return redirect()->route('inventory-returns.index')->with('success', 'Qaytarish muvaffaqiyatli yangilandi.');
    }
}
