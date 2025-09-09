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

// You might want a dashboard route as well
Route::get('/', function () {
    // If you have a dashboard component, you can render it here.
    // For now, let's redirect to the new page.
    return redirect()->route('announce-templates.index');
})->name('dashboard');
