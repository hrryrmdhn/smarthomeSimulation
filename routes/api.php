<?php

use App\Http\Controllers\DeviceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Test route
Route::get('/test', function () {
    return response()->json([
        'message' => '✅ Laravel API is working!',
        'timestamp' => now()->format('Y-m-d H:i:s'),
        'version' => '1.0'
    ]);
});

// Device API Routes
Route::get('/devices/status', [DeviceController::class, 'apiStatus']);
Route::get('/devices/{id}', [DeviceController::class, 'apiShow']);
Route::put('/devices/{id}', [DeviceController::class, 'apiUpdate']);
Route::post('/devices', [DeviceController::class, 'apiStore']);
Route::delete('/devices/{id}', [DeviceController::class, 'apiDestroy']);
Route::post('/devices/reset/all', [DeviceController::class, 'apiResetAll']);

// Fallback for undefined API routes
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'API endpoint not found',
        'available_endpoints' => [
            'GET /api/test',
            'GET /api/devices/status',
            'GET /api/devices/{id}',
            'PUT /api/devices/{id}',
            'POST /api/devices',
            'DELETE /api/devices/{id}',
            'POST /api/devices/reset/all'
        ]
    ], 404);
});