<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DeviceController extends Controller
{
    // ============================================================================
    // FUNCTION UNTUK TAMPILAN WEB
    // ============================================================================
    
    /**
     * Function index() - Menampilkan halaman web utama
     * Cara kerja:
     * 1. Ambil semua data devices dari database
     * 2. Kirim data devices ke view
     * 3. User melihat tampilan dashboard smart home
     */
    public function index()
    {
        // Ambil semua data devices dari tabel 'devices' di database
        $devices = Device::all();
        
        // Tampilkan file view: resources/views/devices/index.blade.php
        // compact('devices') = kirim variable $devices ke view
        return view('devices.index', compact('devices'));
    }

    // ============================================================================
    // FUNCTION UNTUK API (Komunikasi dengan NodeMCU & AJAX)
    // ============================================================================

    /**
     * Function apiStatus() - Memberikan status semua devices dalam format JSON
     * Cara kerja:
     * 1. NodeMCU/AJAX request GET /api/devices/status
     * 2. Ambil semua devices dari database
     * 3. Format data menjadi JSON
     * 4. Kirim response ke NodeMCU/AJAX
     */
    public function apiStatus(Request $request)
    {
        // Ambil semua devices dan format ulang datanya
        $devices = Device::all()->map(function($device) {
            return [
                'id' => $device->id,                    // ID device (1-6)
                'label' => $device->label,              // Nama device (Lampu Kamar, dll)
                'status' => (bool)$device->status,      // Status ON/OFF (dikonversi ke boolean)
                'updated_at' => $device->updated_at->format('H:i:s') // Waktu update terakhir
            ];
        });

        // Return response JSON yang akan dibaca oleh NodeMCU/AJAX
        return response()->json([
            'success' => true,      // Flag sukses
            'data' => $devices      // Data devices
        ]);
    }

    /**
     * Function apiUpdate() - Menerima perintah update status device
     * Cara kerja:
     * 1. AJAX/NodeMCU kirim request PUT/POST /api/devices/{id}
     * 2. Validasi dan proses data
     * 3. Update database
     * 4. Kirim response sukses/gagal
     */
    public function apiUpdate(Request $request, $id)
    {
        // Tulis log untuk debugging (bisa dilihat di storage/logs/laravel.log)
        Log::info('=== API UPDATE DEBUG ===');
        Log::info('Raw request data:', $request->all()); // Data yang diterima dari AJAX
        Log::info('Device ID: ' . $id);                  // ID device yang mau diupdate

        try {
            // Ambil nilai 'status' dari request AJAX
            $status = $request->input('status');
            Log::info('Raw status value: ' . $status . ' (type: ' . gettype($status) . ')');

            // DEBUG: Cek semua kemungkinan format data yang diterima
            Log::info('Conversion test:');
            Log::info('true: ' . ($status === true ? 'MATCH' : 'no'));    // Boolean true
            Log::info('"true": ' . ($status === 'true' ? 'MATCH' : 'no')); // String "true"
            Log::info('1: ' . ($status === 1 ? 'MATCH' : 'no'));          // Integer 1
            Log::info('"1": ' . ($status === '1' ? 'MATCH' : 'no'));      // String "1"

            // KONVERSI KE BOOLEAN - Ini inti dari function ini
            $finalStatus = false; // Default value false (OFF)
            
            // Jika status termasuk dalam nilai yang dianggap TRUE
            if ($status === true || $status === 'true' || $status === 1 || $status === '1') {
                $finalStatus = true;  // Set ke TRUE (ON)
                Log::info('Converted to: TRUE');
            } else {
                $finalStatus = false; // Set ke FALSE (OFF)
                Log::info('Converted to: FALSE');
            }

            Log::info('Final status: ' . ($finalStatus ? 'TRUE' : 'FALSE'));

            // Cari device berdasarkan ID di database
            $device = Device::find($id);
            
            // Jika device tidak ditemukan, kirim error 404
            if (!$device) {
                Log::error('Device not found: ' . $id);
                return response()->json([
                    'success' => false,
                    'message' => 'Device not found'
                ], 404); // HTTP Status 404 = Not Found
            }

            // UPDATE DATABASE - Simpan status baru ke database
            $device->status = $finalStatus;
            $device->save(); // Execute UPDATE query ke database

            Log::info('Device updated successfully: ' . $device->label . ' -> ' . ($device->status ? 'ON' : 'OFF'));

            // KIRIM RESPONSE SUKSES ke AJAX
            return response()->json([
                'success' => true,
                'message' => 'Device updated successfully',
                'device' => [
                    'id' => $device->id,
                    'label' => $device->label,
                    'status' => (bool)$device->status, // Pastikan boolean
                    'updated_at' => $device->updated_at->format('H:i:s') // Timestamp update
                ]
            ]);

        } catch (\Exception $e) {
            // JIKA TERJADI ERROR - Tangani error dan kirim response error
            Log::error('Update error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Update failed: ' . $e->getMessage()
            ], 500); // HTTP Status 500 = Internal Server Error
        }
    }
}