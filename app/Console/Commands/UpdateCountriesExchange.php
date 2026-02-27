<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Country;
use Illuminate\Support\Facades\Http;

class UpdateCountriesExchange extends Command
{
    protected $signature = 'countries:update-exchange';
    protected $description = 'Fetch all countries and update exchange rates and phone codes';

    public function handle()
    {
        $this->info('Fetching all countries from REST Countries API...');

        $countries = Http::get('https://restcountries.com/v3.1/all?fields=name,cca2,currencies,idd')->json();

        // 🟢 قائمة بأكواد الدول العربية والدول الأجنبية الشهيرة (ISO 3166-1 alpha-2)
        $activeCountryCodes = [
            // --- الدول العربية ---
            'EG', 'SA', 'AE', 'QA', 'KW', 'BH', 'OM', 'JO', 'LB', 'SY',
            'IQ', 'PS', 'YE', 'MA', 'DZ', 'TN', 'LY', 'SD', 'SO', 'MR',
            'DJ', 'KM',
            // --- الدول الأجنبية الشهيرة ---
            'US', 'GB', 'CA', 'AU', 'DE', 'FR', 'IT', 'ES', 'JP', 'CN',
            'IN', 'RU', 'BR', 'TR', 'KR', 'MY', 'SE', 'SG', 'CH', 'NL'
        ];

        foreach ($countries as $c) {

            // Skip if no currency defined
            if (!isset($c['currencies']) || empty($c['currencies'])) continue;

            $currencyCode = array_key_first($c['currencies']);
            if (!$currencyCode) continue;

            $currency = $c['currencies'][$currencyCode];

            // 1 USD = X Local Currency
            $rateResponse = Http::get('https://api.fastforex.io/fetch-one', [
                'from' => 'USD',
                'to' => $currencyCode,
                'api_key' => env('FASTFOREX_API_KEY')
            ])->json();

            $rate = $rateResponse['result'][$currencyCode] ?? null;

            // Phone code
            $phoneCode = null;
            if (isset($c['idd']['root']) && isset($c['idd']['suffixes']) && count($c['idd']['suffixes']) > 0) {
                $phoneCode = $c['idd']['root'] . $c['idd']['suffixes'][0];
            }

            // 🟢 تحديد حالة الدولة بناءً على وجودها في المصفوفة
            $isActive = in_array($c['cca2'], $activeCountryCodes);

            // Save or update
            Country::updateOrCreate(
                ['code' => $c['cca2']],
                [
                    'name' => $c['name']['common'],
                    'currency_code' => $currencyCode,
                    'currency_name' => $currency['name'] ?? null,
                    'currency_symbol' => $currency['symbol'] ?? null,
                    'rate_to_usd' => $rate,
                    'phone_code' => $phoneCode,
                    'status' => $isActive, // حفظ الحالة هنا
                ]
            );

            // طباعة رسالة توضح حالة الدولة في الـ Terminal
            $statusText = $isActive ? '✅ Active' : '❌ Inactive';
            $this->info("Updated {$c['name']['common']} ({$currencyCode}) => 1 USD = {$rate} {$currencyCode}, Phone: {$phoneCode} | [{$statusText}]");
        }

        $this->info('All countries updated successfully!');
    }
}
//php artisan countries:update-exchange

