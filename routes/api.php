<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SyncController;

Route::post('/v1/sync/receive', [SyncController::class, 'receive']);
Route::get('/v1/sync/changes', [SyncController::class, 'changes']);