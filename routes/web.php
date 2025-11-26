<?php

use App\Http\Controllers\DeviceController;
use Illuminate\Support\Facades\Route;

// ============================================================================
// ROUTE UNTUK TAMPILAN WEB (HTML)
// ============================================================================

// Route utama: saat user buka alamat website (localhost:8000/) 
// maka akan menjalankan function index() di DeviceController
// ->name('devices.index') memberi nama route untuk bisa dipanggil di view
Route::get('/', [DeviceController::class, 'index'])->name('devices.index');


// ============================================================================
// ROUTE UNTUK API ENDPOINT (JSON)
// Bagian ini yang membuat Laravel dan NodeMCU bisa komunikasi
// ============================================================================

// Route test: untuk cek apakah API bekerja
// Bisa diakses via: GET /api/test
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

// Route untuk ambil status semua devices
// Bisa diakses via: GET /api/devices/status
// Akan menjalankan function apiStatus() di DeviceController
Route::get('/api/devices/status', [DeviceController::class, 'apiStatus']);

// Route untuk update status device tertentu
// Bisa diakses via: PUT atau POST /api/devices/{id}
// {id} akan diganti dengan ID device (1-6)
// Menggunakan match() karena browser AJAX butuh POST, tapi logicnya PUT
Route::match(['put', 'post'], '/api/devices/{id}', [DeviceController::class, 'apiUpdate']);
// Bisa diakses via: POST /api/devices/{id}/mode
Route::post('/api/devices/{id}/mode', [DeviceController::class, 'apiUpdateMode']);


// ============================================================================
// ROUTE FALLBACK (Penanganan error)
// ============================================================================

// Route ini akan dijalankan jika user akses endpoint yang tidak terdaftar
// Misal: GET /api/abc atau POST /api/tidak-ada
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'Endpoint not found',
        'available_endpoints' => [
            'GET /api/test',
            'GET /api/devices/status',
            'PUT /api/devices/{id}'
        ]
    ], 404); // HTTP Status Code 404 = Not Found
});