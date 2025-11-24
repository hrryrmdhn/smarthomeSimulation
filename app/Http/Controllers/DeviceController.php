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
        Log::info('=== API UPDATE DEBUG ===');
        Log::info('Raw request data:', $request->all());
        Log::info('Device ID: ' . $id);

        try {
            $status = $request->input('status');
            Log::info('Raw status value: ' . $status . ' (type: ' . gettype($status) . ')');

            // DEBUG: Tampilkan semua kemungkinan konversi
            Log::info('Conversion test:');
            Log::info('true: ' . ($status === true ? 'MATCH' : 'no'));
            Log::info('"true": ' . ($status === 'true' ? 'MATCH' : 'no'));
            Log::info('1: ' . ($status === 1 ? 'MATCH' : 'no'));
            Log::info('"1": ' . ($status === '1' ? 'MATCH' : 'no'));

            // Convert to boolean - FIXED VERSION
            $finalStatus = false; // default
            
            if ($status === true || $status === 'true' || $status === 1 || $status === '1') {
                $finalStatus = true;
                Log::info('Converted to: TRUE');
            } else {
                $finalStatus = false;
                Log::info('Converted to: FALSE');
            }

            Log::info('Final status: ' . ($finalStatus ? 'TRUE' : 'FALSE'));

            $device = Device::find($id);
            
            if (!$device) {
                Log::error('Device not found: ' . $id);
                return response()->json([
                    'success' => false,
                    'message' => 'Device not found'
                ], 404);
            }

            // Update device
            $device->status = $finalStatus;
            $device->save();

            Log::info('Device updated successfully: ' . $device->label . ' -> ' . ($device->status ? 'ON' : 'OFF'));

            return response()->json([
                'success' => true,
                'message' => 'Device updated successfully',
                'device' => [
                    'id' => $device->id,
                    'label' => $device->label,
                    'status' => (bool)$device->status,
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
}