<?php

namespace Database\Seeders;

use App\Models\Incoterm;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class IncotermSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $incoterms = [
            ['code' => 'EXW', 'name' => 'Ex Works', 'is_active' => true],
            ['code' => 'FCA', 'name' => 'Free Carrier',  'is_active' => true],
            ['code' => 'CPT', 'name' => 'Carriage Paid To', 'is_active' => true],
            ['code' => 'CIP', 'name' => 'Carriage and Insurance Paid To', 'is_active' => true],
            ['code' => 'FOB', 'name' => 'Free On Board',  'is_active' => true],
            ['code' => 'DPU', 'name' => 'Delivered at Place Unloaded', 'is_active' => true],
            ['code' => 'CIF', 'name' => 'Cost, Insurance and Freight', 'is_active' => true],
            ['code' => 'CFR', 'name' => 'Cost and Freight', 'is_active' => true],
            ['code' => 'DDP', 'name' => 'Delivered Duty Paid',  'is_active' => true],
        ];

        foreach ($incoterms as $incoterm) {
            Incoterm::create($incoterm);
        }
    }
}
