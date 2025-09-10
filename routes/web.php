<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AnnounceTemplateController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/announce-templates', [AnnounceTemplateController::class, 'index'])
    ->name('announce-templates.index');

Route::post('/announce-templates/export', [AnnounceTemplateController::class, 'export'])
    ->name('announce-templates.export');

Route::post('/announce-templates/print', [AnnounceTemplateController::class, 'print'])
    ->name('announce-templates.print');

Route::post('/announce-templates/{announceTemplate}/update-payer', [AnnounceTemplateController::class, 'updatePayerName'])
    ->name('announce-templates.update-payer');

Route::post('/announce-templates/batch-update-payer', [AnnounceTemplateController::class, 'batchUpdatePayerName'])
    ->name('announce-templates.batch-update-payer');

// You might want a dashboard route as well
Route::get('/', function () {
    // If you have a dashboard component, you can render it here.
    // For now, let's redirect to the new page.
    return redirect()->route('announce-templates.index');
})->name('dashboard');
