<?php

namespace App\Console\Commands;

use App\Models\AccountsHistory;
use App\Services\OperDayService;
use App\Services\RabbitService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RabbitRepublishByOperDayCommand extends Command
{
    protected $signature = 'rabbitmq:republish-by-operday';
    protected $description = 'Fetches records for the current operational day from AccountsHistory and republishes them to RabbitMQ.';

    public function handle(OperDayService $operDayService, RabbitService $rabbitService): int
    {
        $this->info('Starting republication process...');
        Log::info('Republication command started.');

        try {
            $operDay = $operDayService->getOperDay();

            if (!$operDay) {
                $this->error('Could not determine the operational day. Aborting.');
                Log::error('Republication failed: Could not get operational day.');
                return self::FAILURE;
            }

            $this->info("Fetching records for operational day: {$operDay}");

            $query = AccountsHistory::where('operDay', $operDay);
            $totalRecords = $query->count();

            if ($totalRecords === 0) {
                $this->info("No records found for operational day {$operDay}. Nothing to publish.");
                Log::info("Republication complete: No records found for {$operDay}.");
                return self::SUCCESS;
            }

            $this->info("Found {$totalRecords} records to republish. Starting...");
            $progressBar = $this->output->createProgressBar($totalRecords);
            $publishedCount = 0;

            $query->chunkById(200, function ($records) use ($rabbitService, $progressBar, &$publishedCount) {
                foreach ($records as $record) {
                    try {
                        $rabbitService->publishMessage($record->toJson());
                        $publishedCount++;
                    } catch (\Exception $e) {
                        $this->error("Failed to publish record ID {$record->id}: " . $e->getMessage());
                        Log::error('Republication error: Failed to publish message.', [
                            'record_id' => $record->id,
                            'exception' => $e->getMessage(),
                        ]);
                    }
                }
                $progressBar->advance(count($records));
            });

            $progressBar->finish();
            $this->info("\nRepublication complete. Published {$publishedCount} out of {$totalRecords} records.");
            Log::info("Republication finished. Published {$publishedCount}/{$totalRecords} records for operDay {$operDay}.");

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('A critical error occurred during the republication process: ' . $e->getMessage());
            Log::critical('Republication command failed critically.', ['exception' => $e->getMessage()]);
            return self::FAILURE;
        } finally {
            $rabbitService->close();
            $this->info('RabbitMQ connection closed.');
        }
    }
}
