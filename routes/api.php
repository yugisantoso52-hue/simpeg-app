<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SyncController;

Route::middleware('throttle:60,1')->group(function () {
    Route::post('/v1/sync/receive', [SyncController::class, 'receive']);
    Route::get('/v1/sync/changes', [SyncController::class, 'changes']);
});