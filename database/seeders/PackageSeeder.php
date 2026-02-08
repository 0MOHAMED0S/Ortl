<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Package;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            // 1. Basic Package (Blue/Outline)
            [
                'name'          => 'باقة التلاوة',
                'price'         => 250.00,
                'discount'      => 0, // No discount = Standard Design
                'base_minutes'  => 100,
                'bonus_minutes' => 0,
                'validity_days' => 30,
                'description'   => 'تصحيح آلي ومراجعة الأحكام الأساسية',
                'status'        => 'active',
            ],

            // 2. Featured Package (Orange/Popular)
            // Logic: discount > 0 triggers the "Featured" look
            [
                'name'          => 'باقة البداية',
                'price'         => 940.00, // Original Price
                'discount'      => 50,     // 50% OFF -> Final Price ~470
                'base_minutes'  => 150,
                'bonus_minutes' => 30,
                'validity_days' => 30,
                'description'   => 'مثالية للبدء في الحفظ والمراجعة',
                'status'        => 'active',
            ],

            // 3. VIP Package (Green/Mastery)
            // Logic: 'VIP' in name triggers the Green look
            [
                'name'          => 'باقة الإتقان VIP',
                'price'         => 850.00,
                'discount'      => 0,
                'base_minutes'  => 300,
                'bonus_minutes' => 60,
                'validity_days' => 60,
                'description'   => 'متابعة خاصة مع معلم مجاز',
                'status'        => 'active',
            ],
        ];

        foreach ($packages as $pkg) {
            Package::updateOrCreate(['name' => $pkg['name']], $pkg);
        }
    }
}
