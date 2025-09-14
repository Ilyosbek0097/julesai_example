<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryReturn;
use App\Models\OutputDetail;
use App\Models\ReturnDetail;
use App\Service\UniqueNumberGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $request->validate([
            'inventory_id' => 'required|exists:inventories,id',
            'quantity' => 'required|numeric|min:0.01',
            'comment' => 'nullable|string|max:255',
        ]);

        $inventoryId = $request->inventory_id;
        $returnQuantity = $request->quantity;

        DB::transaction(function () use ($inventoryId, $returnQuantity, $request, $uniqueNumberGenerator) {
            // 1. Validate that return quantity does not exceed net sold quantity
            $outputDetailsQuery = OutputDetail::whereHas('inventoryEntry', function ($query) use ($inventoryId) {
                $query->where('inventory_id', $inventoryId);
            });

            $totalSold = (clone $outputDetailsQuery)->sum('quantity');
            $totalReturned = (clone $outputDetailsQuery)->sum('returned_quantity');
            $maxReturnable = $totalSold - $totalReturned;

            if ($returnQuantity > $maxReturnable) {
                throw ValidationException::withMessages([
                    'quantity' => 'Qaytariladigan miqdor sotilgan miqdordan ko\'p bo\'lishi mumkin emas. Maksimal miqdor: ' . $maxReturnable,
                ]);
            }

            // 2. Create the main return record
            $return = InventoryReturn::create([
                'return_number' => $uniqueNumberGenerator->generateCustom(InventoryReturn::class, 'return_number'),
                'return_date' => now(),
                'comment' => $request->comment,
                'user_id' => auth()->id(),
            ]);

            // 3. Apply LIFO logic to create return details
            $detailsToReturnFrom = $outputDetailsQuery
                ->whereRaw('quantity > returned_quantity')
                ->with('inventoryEntry') // Eager load for stock update
                ->latest('created_at') // LIFO
                ->get();

            $returnQuantityLeft = $returnQuantity;

            foreach ($detailsToReturnFrom as $detail) {
                if ($returnQuantityLeft <= 0) {
                    break;
                }

                $returnableFromThisDetail = $detail->quantity - $detail->returned_quantity;
                $quantityToReturnNow = min($returnQuantityLeft, $returnableFromThisDetail);

                ReturnDetail::create([
                    'inventory_return_id' => $return->id,
                    'output_detail_id' => $detail->id,
                    'quantity' => $quantityToReturnNow,
                    'price' => $detail->price, // Preserve original price for records
                    'total' => $quantityToReturnNow * $detail->price,
                ]);

                // Update the returned quantity on the original output detail
                $detail->increment('returned_quantity', $quantityToReturnNow);

                // Update the stock on the corresponding inventory entry
                $detail->inventoryEntry->increment('remaining_quantity', $quantityToReturnNow);

                $returnQuantityLeft -= $quantityToReturnNow;
            }
        });

        return redirect()->route('inventory-returns.index')->with('success', 'Tovar muvaffaqiyatli qaytarildi.');
    }
}
