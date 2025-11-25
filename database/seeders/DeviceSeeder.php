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
            ['label' => 'Lampu Kamar', 'status' => false],
            ['label' => 'Lampu Ruang Tamu', 'status' => false],
            ['label' => 'Lampu Dapur', 'status' => false],
            ['label' => 'Lampu Teras', 'status' => false],
            ['label' => 'AC Living Room', 'status' => false],
            ['label' => 'Kipas Angin', 'status' => false],
        ];

        foreach ($devices as $device) {
            Device::create($device);
        };
    }
}
