<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DeviceController extends Controller
{
    public function index()
    {
        $devices = Device::all();
        return view('devices.index', compact('devices'));
    }

    public function apiStatus(Request $request)
    {
        $devices = Device::all()->map(function($device) {
            return [
                'id' => $device->id,
                'label' => $device->label,
                'status' => (bool)$device->status,
                'mode' => $device->mode, // Tambah mode
                'auto_threshold' => $device->auto_threshold, // Tambah threshold
                'updated_at' => $device->updated_at->format('H:i:s')
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $devices
        ]);
    }

    public function apiUpdate(Request $request, $id)
    {
        Log::info('API Update Request:', $request->all());

        try {
            $status = $request->input('status');
            
            // Convert to boolean
            $finalStatus = false;
            if ($status === true || $status === 'true' || $status === 1 || $status === '1') {
                $finalStatus = true;
            }

            $device = Device::find($id);
            
            if (!$device) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device not found'
                ], 404);
            }

            // Hanya update jika mode manual
            if ($device->mode === 'manual') {
                $device->status = $finalStatus;
                $device->save();
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Device dalam mode auto, tidak bisa dikontrol manual'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Device updated successfully',
                'device' => [
                    'id' => $device->id,
                    'label' => $device->label,
                    'status' => (bool)$device->status,
                    'mode' => $device->mode,
                    'auto_threshold' => $device->auto_threshold,
                    'updated_at' => $device->updated_at->format('H:i:s')
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Update error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Update failed: ' . $e->getMessage()
            ], 500);
        }
    }

    // API Baru: Untuk update mode device
    public function apiUpdateMode(Request $request, $id)
    {
        try {
            $request->validate([
                'mode' => 'required|in:manual,auto',
                'auto_threshold' => 'nullable|numeric'
            ]);

            $device = Device::find($id);
            $device->mode = $request->mode;
            $device->auto_threshold = $request->auto_threshold;
            $device->save();

            return response()->json([
                'success' => true,
                'message' => 'Mode updated successfully',
                'device' => $device
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Update mode failed'
            ], 500);
        }
    }
}