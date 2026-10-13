<?php

use App\Http\Controllers\ServiceApiController;
use App\Http\Middleware\EnsureServiceSignature;
use Illuminate\Support\Facades\Route;

// Versioned service ingest endpoints for hosted website backends.
// Authenticated with HMAC service signatures (EnsureServiceSignature),
// never with browser sessions.
Route::prefix('v1/hosting')->middleware('throttle:60,1')->group(function () {
    Route::post('service/heartbeat', [ServiceApiController::class, 'heartbeat'])->middleware(EnsureServiceSignature::class);
    Route::post('service/health-report', [ServiceApiController::class, 'healthReport'])->middleware(EnsureServiceSignature::class);
    Route::get('service/status', [ServiceApiController::class, 'status'])->middleware(EnsureServiceSignature::class);
});
