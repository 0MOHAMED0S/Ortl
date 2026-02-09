<?php

namespace Database\Seeders;

use App\Models\ContactSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContactSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ContactSetting::create([
        'email' => 'info@wartil.com',
        'phone' => '201110562097',
    ]);
    }
}
