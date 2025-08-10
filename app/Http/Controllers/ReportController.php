<?php

namespace App\Http\Controllers;

use App\Exports\ReportExport;
use App\Models\Inventory;
use App\Models\InventoryEntry;
use App\Models\InventoryOutput;
use App\Models\InventoryReturn;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index()
    {
        return Inertia::render('Reports/Index');
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:stock,entries,outputs,returns,consolidated',
            'from_date' => 'sometimes|required_if:type,entries,outputs,returns|date',
            'to_date' => 'sometimes|required_if:type,entries,outputs,returns|date|after_or_equal:from_date',
        ]);

        $type = $validated['type'];
        $fromDate = $validated['from_date'] ?? null;
        $toDate = $validated['to_date'] ?? null;

        $data = [];
        $columns = [];

        switch ($type) {
            case 'consolidated':
                $columns = ['Mahsulot', 'Kirim', 'Chiqim', 'Qaytish', 'Sof chiqim', 'Qoldiq', 'Qoldiq summasi'];
                $inventories = Inventory::with('unit', 'entries.outputDetails.returnDetails')->get();

                $data = $inventories->map(function ($inventory) {
                    $total_entries = $inventory->entries->sum('quantity');
                    $total_outputs = $inventory->entries->pluck('outputDetails')->flatten()->sum('quantity');
                    $total_returns = $inventory->entries->pluck('outputDetails')->flatten()->pluck('returnDetails')->flatten()->sum('quantity');

                    $net_output = $total_outputs - $total_returns;
                    $remaining_stock = $total_entries - $net_output;

                    $avg_price = $inventory->entries->avg('unit_price') ?? 0;
                    $remaining_value = $remaining_stock * $avg_price;

                    return [
                        'Mahsulot' => $inventory->name . ' (' . $inventory->unit->name . ')',
                        'Kirim' => $total_entries,
                        'Chiqim' => $total_outputs,
                        'Qaytish' => $total_returns,
                        'Sof chiqim' => $net_output,
                        'Qoldiq' => $remaining_stock,
                        'Qoldiq summasi' => number_format($remaining_value, 2) . ' so‘m',
                    ];
                });
                break;

            case 'stock':
                $columns = ['Mahsulot', 'Hozirgi Qoldiq'];
                $data = Inventory::with('unit')->get()->map(function ($inventory) {
                    return [
                        'Mahsulot' => $inventory->name . ' (' . $inventory->unit->name . ')',
                        'Hozirgi Qoldiq' => $inventory->entries()->sum('remaining_quantity'),
                    ];
                });
                break;

            case 'entries':
                $columns = ['Kirim Raqami', 'Mahsulot', 'Miqdori', 'Narxi', 'Umumiy', 'Yetkazib Beruvchi', 'Sana'];
                $data = InventoryEntry::with('inventory.unit', 'supplier')
                    ->whereBetween('entry_date', [$fromDate, $toDate])
                    ->get()->map(fn ($entry) => [
                        'Kirim Raqami' => $entry->entry_number,
                        'Mahsulot' => $entry->inventory->name,
                        'Miqdori' => $entry->quantity . ' ' . $entry->inventory->unit->name,
                        'Narxi' => $entry->unit_price,
                        'Umumiy' => $entry->quantity * $entry->unit_price,
                        'Yetkazib Beruvchi' => $entry->supplier->name,
                        'Sana' => $entry->entry_date,
                    ]);
                break;

            case 'outputs':
                $columns = ['Chiqim Raqami', 'Sana', 'Umumiy Narx', 'Izoh'];
                 $data = InventoryOutput::whereBetween('output_date', [$fromDate, $toDate])
                    ->get()->map(fn ($output) => [
                        'Chiqim Raqami' => $output->output_number,
                        'Sana' => $output->output_date,
                        'Umumiy Narx' => $output->total_price,
                        'Izoh' => $output->comment,
                    ]);
                break;

            case 'returns':
                 $columns = ['Qaytarish Raqami', 'Sana', 'Izoh'];
                 $data = InventoryReturn::whereBetween('return_date', [$fromDate, $toDate])
                    ->get()->map(fn ($return) => [
                        'Qaytarish Raqami' => $return->return_number,
                        'Sana' => $return->return_date,
                        'Izoh' => $return->comment,
                    ]);
                break;
        }

        return Inertia::render('Reports/Index', [
            'report_type' => $type,
            'report_data' => $data,
            'report_columns' => $columns,
            'request_params' => $request->all(),
        ]);
    }

    public function export(Request $request)
    {
         $validated = $request->validate([
            'type' => 'required|string|in:stock,entries,outputs,returns',
            'from_date' => 'sometimes|required_if:type,entries,outputs,returns|date',
            'to_date' => 'sometimes|required_if:type,entries,outputs,returns|date|after_or_equal:from_date',
        ]);

        $type = $validated['type'];
        $fromDate = $validated['from_date'] ?? null;
        $toDate = $validated['to_date'] ?? null;

        $data = [];
        $columns = [];

        // This logic is duplicated from generate(). It could be refactored into a private method.
        switch ($type) {
            case 'consolidated':
                $columns = ['Mahsulot', 'Kirim', 'Chiqim', 'Qaytish', 'Sof chiqim', 'Qoldiq', 'Qoldiq summasi'];
                $inventories = Inventory::with('unit', 'entries.outputDetails.returnDetails')->get();

                $data = $inventories->map(function ($inventory) {
                    $total_entries = $inventory->entries->sum('quantity');
                    $total_outputs = $inventory->entries->pluck('outputDetails')->flatten()->sum('quantity');
                    $total_returns = $inventory->entries->pluck('outputDetails')->flatten()->pluck('returnDetails')->flatten()->sum('quantity');

                    $net_output = $total_outputs - $total_returns;
                    $remaining_stock = $total_entries - $net_output;

                    $avg_price = $inventory->entries->avg('unit_price') ?? 0;
                    $remaining_value = $remaining_stock * $avg_price;

                    return [
                        'Mahsulot' => $inventory->name . ' (' . $inventory->unit->name . ')',
                        'Kirim' => $total_entries,
                        'Chiqim' => $total_outputs,
                        'Qaytish' => $total_returns,
                        'Sof chiqim' => $net_output,
                        'Qoldiq' => $remaining_stock,
                        'Qoldiq summasi' => number_format($remaining_value, 2) . ' so‘m',
                    ];
                });
                break;
            case 'stock':
                $columns = ['Mahsulot', 'Hozirgi Qoldiq'];
                $data = Inventory::with('unit')->get()->map(function ($inventory) {
                    return [
                        'Mahsulot' => $inventory->name . ' (' . $inventory->unit->name . ')',
                        'Hozirgi Qoldiq' => $inventory->entries()->sum('remaining_quantity'),
                    ];
                });
                break;
            case 'entries':
                $columns = ['Kirim Raqami', 'Mahsulot', 'Miqdori', 'Narxi', 'Umumiy', 'Yetkazib Beruvchi', 'Sana'];
                $data = InventoryEntry::with('inventory.unit', 'supplier')
                    ->whereBetween('entry_date', [$fromDate, $toDate])
                    ->get()->map(fn ($entry) => [
                        'Kirim Raqami' => $entry->entry_number,
                        'Mahsulot' => $entry->inventory->name,
                        'Miqdori' => $entry->quantity . ' ' . $entry->inventory->unit->name,
                        'Narxi' => $entry->unit_price,
                        'Umumiy' => $entry->quantity * $entry->unit_price,
                        'Yetkazib Beruvchi' => $entry->supplier->name,
                        'Sana' => $entry->entry_date,
                    ]);
                break;
            case 'outputs':
                $columns = ['Chiqim Raqami', 'Sana', 'Umumiy Narx', 'Izoh'];
                 $data = InventoryOutput::whereBetween('output_date', [$fromDate, $toDate])
                    ->get()->map(fn ($output) => [
                        'Chiqim Raqami' => $output->output_number,
                        'Sana' => $output->output_date,
                        'Umumiy Narx' => $output->total_price,
                        'Izoh' => $output->comment,
                    ]);
                break;
            case 'returns':
                 $columns = ['Qaytarish Raqami', 'Sana', 'Izoh'];
                 $data = InventoryReturn::whereBetween('return_date', [$fromDate, $toDate])
                    ->get()->map(fn ($return) => [
                        'Qaytarish Raqami' => $return->return_number,
                        'Sana' => $return->return_date,
                        'Izoh' => $return->comment,
                    ]);
                break;
        }

        return Excel::download(new ReportExport($data, $columns), 'hisobot-' . $type . '-' . now()->format('Y-m-d') . '.xlsx');
    }
}
