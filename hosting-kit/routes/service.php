<?php

use App\Http\Controllers\TtryyServiceController;
use Illuminate\Support\Facades\Route;

/*
 * ttryy service routes. Register these on EVERY hosted website:
 *
 *   Laravel 11+/12 — in bootstrap/app.php:
 *       ->withRouting(web: __DIR__.'/../routes/web.php',
 *                      then: function () {
 *                          Route::middleware('web')
 *                              ->group(base_path('routes/service.php'));
 *                      })
 *
 *   Laravel 10 and earlier — in RouteServiceProvider::boot():
 *       Route::middleware('web')->group(base_path('routes/service.php'));
 *
 * These paths are signed by the central platform; do not rename them.
 */
Route::get('/hosting-service/health', [TtryyServiceController::class, 'health'])->name('ttryy.service.health');
Route::get('/hosting-service/status', [TtryyServiceController::class, 'status'])->name('ttryy.service.status');
Route::post('/hosting-service/commands', [TtryyServiceController::class, 'commands'])->name('ttryy.service.commands');
