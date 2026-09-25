<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SyncController;
use App\Http\Controllers\DeployWebhookController;

Route::post('/v1/sync/receive', [SyncController::class, 'receive']);
Route::get('/v1/sync/changes', [SyncController::class, 'changes']);

/* Automated 1-Click Deployment Webhook */
Route::match(['get', 'post'], '/deploy-webhook', [DeployWebhookController::class, 'handle']);
Route::match(['get', 'post'], '/sistem-auto-update', [DeployWebhookController::class, 'handle']);