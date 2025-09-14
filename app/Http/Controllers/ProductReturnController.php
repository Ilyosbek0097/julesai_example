<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryReturn;
use App\Models\OutputDetail;
use App\Models\ReturnDetail;
use App\Service\UniqueNumberGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ProductReturnController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $inventories = Inventory::orderBy('name')->get(['id', 'name']);

        return Inertia::render('ProductReturns/Create', [
            'inventories' => $inventories
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, UniqueNumberGenerator $uniqueNumberGenerator)
    {
        $validated = $request->validate([
            'comment' => 'nullable|string|max:255',
            'returns' => 'required|array|min:1',
            'returns.*.inventory_id' => 'required|exists:inventories,id',
            'returns.*.quantity' => 'required|numeric|min:0.01',
        ]);

        DB::transaction(function () use ($validated, $uniqueNumberGenerator) {
            // 1. Create the main return record
            $return = InventoryReturn::create([
                'return_number' => $uniqueNumberGenerator->generateCustom(InventoryReturn::class, 'return_number'),
                'return_date' => now(),
                'comment' => $validated['comment'],
                'user_id' => auth()->id(),
            ]);

            foreach ($validated['returns'] as $index => $returnItem) {
                $inventoryId = $returnItem['inventory_id'];
                $returnQuantity = $returnItem['quantity'];

                // 2. For each item, validate that return quantity does not exceed net sold quantity
                $outputDetailsQuery = OutputDetail::whereHas('inventoryEntry', function ($query) use ($inventoryId) {
                    $query->where('inventory_id', $inventoryId);
                });

                $totalSold = (clone $outputDetailsQuery)->sum('quantity');
                $totalReturned = (clone $outputDetailsQuery)->sum('returned_quantity');
                $maxReturnable = $totalSold - $totalReturned;

                if ($returnQuantity > $maxReturnable) {
                    $inventory = Inventory::find($inventoryId);
                    // Throw a validation error that points to the specific item in the list
                    throw ValidationException::withMessages([
                        "returns.{$index}.quantity" => "{$inventory->name}: Qaytariladigan miqdor sotilgan miqdordan ko'p. Maksimal: {$maxReturnable}",
                    ]);
                }

                // 3. Apply LIFO logic to create return details for this item
                $detailsToReturnFrom = $outputDetailsQuery
                    ->whereRaw('quantity > returned_quantity')
                    ->with('inventoryEntry')
                    ->latest('created_at')
                    ->get();

                $returnQuantityLeft = $returnQuantity;

                foreach ($detailsToReturnFrom as $detail) {
                    if ($returnQuantityLeft <= 0) break;

                    $returnableFromThisDetail = $detail->quantity - $detail->returned_quantity;
                    $quantityToReturnNow = min($returnQuantityLeft, $returnableFromThisDetail);

                    ReturnDetail::create([
                        'inventory_return_id' => $return->id,
                        'output_detail_id' => $detail->id,
                        'quantity' => $quantityToReturnNow,
                        'price' => $detail->price,
                        'total' => $quantityToReturnNow * $detail->price,
                    ]);

                    $detail->increment('returned_quantity', $quantityToReturnNow);
                    $detail->inventoryEntry->increment('remaining_quantity', $quantityToReturnNow);

                    $returnQuantityLeft -= $quantityToReturnNow;
                }
            }
        });

        return redirect()->route('inventory-returns.index')->with('success', 'Tovarlar muvaffaqiyatli qaytarildi.');
    }
}
