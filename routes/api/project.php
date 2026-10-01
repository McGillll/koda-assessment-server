<?php

use App\Http\Controllers\Api\ProjectController;
use Illuminate\Support\Facades\Route;

Route::prefix('projects')->middleware(['auth:sanctum', 'abilities:auth:user', 'throttle:api'])->group(function () {
    Route::get('/', [ProjectController::class, 'index']);
    Route::post('/', [ProjectController::class, 'store']);
    Route::get('/{uuid}', [ProjectController::class, 'show']);
    Route::put('/{uuid}', [ProjectController::class, 'update']);
    Route::delete('/{uuid}', [ProjectController::class, 'destroy']);
});
