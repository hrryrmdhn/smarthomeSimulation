<?php

use App\Http\Controllers\DeviceController;
use Illuminate\Support\Facades\Route;

// ========== WEB ROUTES ==========
Route::get('/', [DeviceController::class, 'index'])->name('devices.index');

// ========== API ROUTES (pindah ke sini) ==========
Route::get('/api/test', function () {
    return response()->json([
        'message' => '✅ Laravel API is working!',
        'timestamp' => now()->format('Y-m-d H:i:s'),
        'routes' => [
            '/api/test',
            '/api/devices/status', 
            '/api/devices/{id} (PUT)'
        ]
    ]);
});

Route::get('/api/devices/status', [DeviceController::class, 'apiStatus']);
// Untuk AJAX dari browser, kita perlu POST dengan _method=PUT
Route::match(['put', 'post'], '/api/devices/{id}', [DeviceController::class, 'apiUpdate']);

// Fallback untuk undefined API routes
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'Endpoint not found',
        'available_endpoints' => [
            'GET /api/test',
            'GET /api/devices/status',
            'PUT /api/devices/{id}'
        ]
    ], 404);
});