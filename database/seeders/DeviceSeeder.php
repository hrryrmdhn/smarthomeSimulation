<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Device;

class DeviceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // buat isi data di tabel databasenya, mau beda? tinggal ganti dulu aja devicenya sesuai yang diinginkan, baru jalanin seednya, dengan semua nya dalam kondisi awal false / 0 / off
        $devices = [
            ['label' => 'Lampu Kamar', 'status' => false, 'mode' => 'manual', 'auto_threshold' => null],
            ['label' => 'Lampu Ruang Tamu', 'status' => false, 'mode' => 'manual', 'auto_threshold' => null],
            ['label' => 'Lampu Dapur', 'status' => false, 'mode' => 'manual', 'auto_threshold' => null],
            
            // Device 4-6: Auto mode dengan threshold suhu
            ['label' => 'Kipas Angin', 'status' => false, 'mode' => 'auto', 'auto_threshold' => 18.0],
            ['label' => 'AC Living Room', 'status' => false, 'mode' => 'auto', 'auto_threshold' => 30.0],
            ['label' => 'Air Purifier', 'status' => false, 'mode' => 'auto', 'auto_threshold' => 32.0],
        ];

        foreach ($devices as $device) {
            Device::create($device);
        };
    }
}
