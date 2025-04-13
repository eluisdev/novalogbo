<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\ExchangeRateSeeder;

class DatabaseSeeder extends Seeder
{

    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserAdminSeeder::class,
            WorldDataSeeder::class,
            IncotermSeeder::class,
            ServiceSeeder::class,
            CostSeeder::class,
            CustomerSeeder::class,
            QuantityDescriptionSeeder::class,
            ExchangeRateSeeder::class,
        ]);
    }
}
