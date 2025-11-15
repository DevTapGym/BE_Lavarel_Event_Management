<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Location;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            [
                'name' => 'Hội trường C',
                'building' => 'Tòa nhà trung tâm',
                'address' => '140 Lê Trọng Tấn, Phường Sơn Kỳ, Quận Tân Phú, TP.HCM',
                'capacity' => 300,
            ],
            [
                'name' => 'Thư viện HUIT',
                'building' => 'Tầng 3 - Tòa nhà Thư viện',
                'address' => '140 Lê Trọng Tấn, Phường Sơn Kỳ, Quận Tân Phú, TP.HCM',
                'capacity' => 100,
            ],
            [
                'name' => 'Phòng A503',
                'building' => 'Tòa nhà A',
                'address' => '140 Lê Trọng Tấn, Phường Sơn Kỳ, Quận Tân Phú, TP.HCM',
                'capacity' => 80,
            ],
            [
                'name' => 'Phòng B404',
                'building' => 'Tòa nhà B',
                'address' => '140 Lê Trọng Tấn, Phường Sơn Kỳ, Quận Tân Phú, TP.HCM',
                'capacity' => 50,
            ],
            [
                'name' => 'Phòng F201',
                'building' => 'Tòa nhà F',
                'address' => '140 Lê Trọng Tấn, Phường Sơn Kỳ, Quận Tân Phú, TP.HCM',
                'capacity' => 80,
            ],
            [
                'name' => 'Sân thể thao',
                'building' => 'Khu thể chất',
                'address' => '227 Nguyễn Văn Cừ, Quận 5, TP.HCM',
                'capacity' => 400,
            ],
            [
                'name' => 'Phòng hội thảo',
                'building' => 'Tòa nhà C',
                'address' => '140 Lê Trọng Tấn, Phường Sơn Kỳ, Quận Tân Phú, TP.HCM',
                'capacity' => 100,
            ],
        ];

        foreach ($locations as $locationData) {
            Location::firstOrCreate(
                ['name' => $locationData['name'], 'building' => $locationData['building']],
                $locationData
            );
        }

        $this->command->info('Locations seeded successfully! Total: ' . count($locations));
    }
}
