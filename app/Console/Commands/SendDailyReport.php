<?php

namespace App\Console\Commands;

use App\Models\Inventory;
use App\Models\InventoryEntry;
use App\Models\OutputDetail;
use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendDailyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends a daily summary report to Telegram.';

    /**
     * Execute the console command.
     */
    public function handle(TelegramService $telegramService)
    {
        $this->info('Generating daily report...');

        // 1. Calculate total stock value
        $inventories = Inventory::with('entries')->get();
        $totalStockValue = $inventories->sum(function ($inventory) {
            $remainingStock = $inventory->entries->sum('remaining_quantity');
            $avgPrice = $inventory->entries->avg('unit_price') ?? 0;
            return $remainingStock * $avgPrice;
        });

        // 2. Calculate total entries today
        $todayEntries = InventoryEntry::whereDate('entry_date', Carbon::today())->get();
        $totalEntriesValue = $todayEntries->sum(function ($entry) {
            return $entry->quantity * $entry->unit_price;
        });

        // 3. Calculate total outputs today
        $todayOutputs = OutputDetail::whereHas('inventoryOutput', function ($query) {
            $query->whereDate('output_date', Carbon::today());
        })->get();
        $totalOutputsValue = $todayOutputs->sum(function ($detail) {
            return $detail->quantity * $detail->price;
        });

        // 4. Format the message
        $today = Carbon::today()->format('d.m.Y');
        $message = "*📊 Kunlik Hisobot ({$today})*\n\n";
        $message .= "Omborning joriy umumiy qiymati: *" . number_format($totalStockValue, 2) . " so'm*\n\n";
        $message .= "📈 Bugungi kirimlar: *+" . number_format($totalEntriesValue, 2) . " so'm*\n";
        $message .= "📉 Bugungi chiqimlar: *-" . number_format($totalOutputsValue, 2) . " so'm*\n";

        // 5. Send the message
        $telegramService->sendMessage($message, false); // Send synchronously for command

        $this->info('Daily report sent successfully!');
        return 0;
    }
}
