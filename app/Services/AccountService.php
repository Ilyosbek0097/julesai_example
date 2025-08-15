<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\AccountsHistory; // Assuming this is the correct path for the model
use Illuminate\Support\Facades\Log;

class AccountService
{
    /**
     * A placeholder for a database transaction handler.
     */
    protected function handleTransaction(callable $callback)
    {
        // In a real Laravel app, this would be DB::transaction(...)
        try {
            return $callback();
        } catch (\Exception $e) {
            Log::error('Transaction failed', ['exception' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Processes a batch of account history data and performs a bulk upsert.
     *
     * @param array $bulkData An array of account data records.
     * @return bool True on success, false on failure.
     */
    public function upsertBulkAccountsHistory(array $bulkData = [])
    {
        $uniqueRecords = [];
        foreach ($bulkData as $record) {
            // Basic validation to ensure key fields exist.
            $isMatched = isset($record['accExternal']) && !is_null($record['operDay']);

            if ($isMatched) {
                // Prepare the data for upsert.
                $preparedRecord = $this->arrayOptimize($record);

                // Create a unique key to handle duplicates within the batch.
                // This ensures only the last record for a given composite key is kept.
                $compositeKey = $preparedRecord['accExternal'] . '_' . $preparedRecord['operDay'];
                $uniqueRecords[$compositeKey] = $preparedRecord;

            } else {
                Log::info('upsertBulkAccountsHistory: Record skipped due to missing fields', ['data' => $record]);
            }
        }

        if (empty($uniqueRecords)) {
            Log::info('upsertBulkAccountsHistory: No valid data to upsert.');
            return true; // Nothing to do, so operation is "successful".
        }

        // Get the de-duplicated records ready for upsert.
        $deduplicatedData = array_values($uniqueRecords);

        try {
            // Perform a single bulk upsert operation with the de-duplicated data.
            return $this->handleTransaction(function () use ($deduplicatedData) {
                $result = AccountsHistory::upsert(
                    $deduplicatedData,
                    ['accExternal', 'operDay']
                );
                Log::debug('AccountsHistory: Bulk upsert completed.', [
                    'record_count' => count($deduplicatedData),
                    'result' => $result,
                ]);
                return (bool)$result;
            });
        } catch (\Exception $e) {
            Log::error('AccountsHistory: Error during bulk upsert', [
                'exception' => $e->getMessage(),
                'record_count' => count($deduplicatedData),
            ]);
            return false;
        }
    }

    /**
     * Prepares and sanitizes a single account data array for database insertion.
     *
     * @param array $data The raw data array.
     * @return array The prepared data array.
     */
    private function arrayOptimize(array $data): array
    {
        return [
            'cashboxId' => $data['cashboxId'],
            'accExternal' => $data['accExternal'],
            'codeFilial' => $data['codeFilial'],
            'branch' => $data['branch'],
            'localCode' => $data['localCode'],
            'codeCurrency' => $data['codeCurrency'],
            'nameCur' => $data['nameCur'],
            'name' => $data['name'],
            'saldoIn' => $data['saldoIn'],
            'saldoOut' => $data['saldoOut'],
            'saldoUnlead' => $data['saldoUnlead'],
            'turnoverDebit' => $data['turnoverDebit'],
            'turnoverCredit' => $data['turnoverCredit'],
            'turnoverAllDebet' => $data['turnoverAllDebet'],
            'turnoverAllCredit' => $data['turnoverAllCredit'],
            'Doc_Count_Incoming' => $data['Doc_Count_Incoming'] ?? 0,
            'Doc_Count_Outgoing' => $data['Doc_Count_Outgoing'] ?? 0,
            'operDay' => Carbon::createFromFormat('d.m.Y', $data['operDay'])->format('Y-m-d'),
            'isCheked' => $data['isCheked'],
            'sysDate' => $data['sysDate'] ?? null,
        ];
    }
}
