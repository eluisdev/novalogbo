<?php

namespace Database\Seeders;

use App\Models\QuantityDescription;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QuantityDescriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        QuantityDescription::create(["name" => "BOX", "is_active" => true]);
    }
}
