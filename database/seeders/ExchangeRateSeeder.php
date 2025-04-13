<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\ExchangeRate;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ExchangeRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ExchangeRate::create([
            'source_currency' => 'BOB',
            'target_currency' => 'USD',
            'rate' => 6.96,
            'date' => Carbon::now(),
            'active' => true
        ]);
        ExchangeRate::create([
            'source_currency' => 'BOB',
            'target_currency' => 'EUR',
            'rate' => 7.76,
            'date' => Carbon::now(),
            'active' => true
        ]);
    }
}
