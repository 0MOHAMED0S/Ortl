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
        $this->info('Fetching exchange rates from FastForex (Base EGP)...');
        $ratesResponse = Http::get('https://api.fastforex.io/fetch-all', [
            'from' => 'EGP',
            'api_key' => env('FASTFOREX_API_KEY')
        ])->json();

        $rates = $ratesResponse['results'] ?? [];

        if (empty($rates)) {
            $this->error('Failed to fetch rates from FastForex.');
            return;
        }

        $countries = Country::whereNotNull('currency_code')->get();
        $updatedCount = 0;

        foreach ($countries as $country) {
            $currencyCode = $country->currency_code;
            $rate = $rates[$currencyCode] ?? null;

            if ($rate !== null) {
                $country->update(['rate_to_usd' => $rate]);
                $this->info("Updated {$country->name} ({$currencyCode}) => 1 EGP = {$rate} {$currencyCode}");
                $updatedCount++;
            } else {
                $this->warn("No rate found for {$currencyCode}");
            }
        }

        $this->info("Successfully updated exchange rates for {$updatedCount} countries!");
    }
}
//php artisan countries:update-exchange

