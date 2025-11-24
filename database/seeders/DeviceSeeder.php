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
