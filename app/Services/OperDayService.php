<?php

namespace App\Services;

use App\Models\Operday;
use App\Traits\HandleTransaction;
use Carbon\Carbon;

class OperDayService
{
    use HandleTransaction;
    public $oper_day_url;

    public function __construct(
        protected ShinaService $shinaService,
        protected Operday $operday
    ) {
        $this->oper_day_url = config('app.esbcore.oper_day_info');
    }

    public function getOperDay()
    {
        $operDay = $this->operday
            ->orderBy('id', 'desc')
            ->value('operDay');
        if (!$operDay) {
            return null;
        }
        try {
            $date = Carbon::createFromFormat('d.m.Y', $operDay);
            if (app()->environment(['local', 'lab'])) {
                $date = $date->subDays(29);
            }
            return $date->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getOperDayNotFormat()
    {
        $operDay = $this->operday
            ->orderBy('id', 'desc')
            ->value('operDay');

        if (!$operDay) {
            return null;
        }

        try {
            $date = Carbon::parse($operDay);

            if (app()->environment(['local', 'lab'])) {
                // faqat lab yoki localda subDays(29) ishlaydi
                $date = $date->subDays(28);
            }

            return $date->format('d.m.Y'); // formatni eng oxirida chaqirasiz
        } catch (\Exception $e) {
            return null;
        }
    }

    public function insertOperDay()
    {
        $response = $this->shinaService->getDataFromApi($this->oper_day_url);
        return $this->upsertOperDay($response['responseBody']);
    }

    public function upsertOperDay($data)
    {
        return $this->handleTransaction(function () use ($data) {
            return $this->operday->upsert(
                $data,
                ['operDay'],
                [
                    'bankDay',
                    'caReconciliation',
                    'ccMfo',
                    'documentAllowed',
                    'endRecieved',
                    'headMfo',
                    'interbankAllowed',
                    'operDayClose',
                    'operDayOpen',
                    'realDb',
                ]
            );
        });
    }
}
