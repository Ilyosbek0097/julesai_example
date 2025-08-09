<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInventoryRequest;
use App\Http\Requests\UpdateInventoryRequest;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class InventoryController extends Controller
{
    public function index()
    {
        $inventories = Inventory::with('category', 'unit')
            ->latest()
            ->paginate(10);

        return Inertia::render('Inventories/Index', [
            'inventories' => $inventories,
        ]);
    }

    public function create()
    {
        return Inertia::render('Inventories/Create', [
            'categories' => Category::all(['id', 'name']),
            'units' => Unit::all(['id', 'name']),
        ]);
    }

    public function store(StoreInventoryRequest $request)
    {
        Inventory::create($request->validated() + ['user_id' => Auth::id()]);

        return redirect()->route('inventories.index')
            ->with('message', 'Inventory created successfully.');
    }

    public function edit(Inventory $inventory)
    {
        return Inertia::render('Inventories/Edit', [
            'inventory' => $inventory,
            'categories' => Category::all(['id', 'name']),
            'units' => Unit::all(['id', 'name']),
        ]);
    }

    public function update(UpdateInventoryRequest $request, Inventory $inventory)
    {
        $inventory->update($request->validated());

        return redirect()->route('inventories.index')
            ->with('message', 'Inventory updated successfully.');
    }

    public function destroy(Inventory $inventory)
    {
        $inventory->delete();

        return redirect()->route('inventories.index')
            ->with('message', 'Inventory deleted successfully.');
    }
}
