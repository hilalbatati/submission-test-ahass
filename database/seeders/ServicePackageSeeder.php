<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ServicePackage;
use Illuminate\Database\Seeder;

class ServicePackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Servis Rutin (Oli & Filter)',
                'price' => 150000,
            ],
            [
                'name' => 'Ganti Oli Mesin',
                'price' => 85000,
            ],
            [
                'name' => 'Tune Up',
                'price' => 120000,
            ],
            [
                'name' => 'Servis Ringan',
                'price' => 100000,
            ],
            [
                'name' => 'Servis Besar',
                'price' => 250000,
            ],
        ];

        foreach ($packages as $pkg) {
            ServicePackage::updateOrCreate(
                ['name' => $pkg['name']],
                ['price' => $pkg['price']]
            );
        }
    }
}
