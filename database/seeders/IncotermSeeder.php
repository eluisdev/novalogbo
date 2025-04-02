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
            ['code' => 'EXW', 'name' => 'Ex Works', 'description' => 'El vendedor pone la mercancía a disposición del comprador en sus instalaciones.', 'is_active' => true],
            ['code' => 'FCA', 'name' => 'Free Carrier', 'description' => 'El vendedor entrega la mercancía al transportista designado por el comprador.', 'is_active' => true],
            ['code' => 'CPT', 'name' => 'Carriage Paid To', 'description' => 'El vendedor paga el flete hasta el destino acordado.', 'is_active' => true],
            ['code' => 'CIP', 'name' => 'Carriage and Insurance Paid To', 'description' => 'Similar a CPT pero con seguro pagado por el vendedor.', 'is_active' => true],
            ['code' => 'FOB', 'name' => 'Free On Board', 'description' => 'El vendedor entrega la mercancía a bordo del buque en el puerto de embarque.', 'is_active' => true],
            ['code' => 'DPU', 'name' => 'Delivered at Place Unloaded', 'description' => 'El vendedor entrega la mercancía descargada en el lugar acordado.', 'is_active' => true],
            ['code' => 'CIF', 'name' => 'Cost, Insurance and Freight', 'description' => 'El vendedor paga costos, flete y seguro hasta el puerto de destino.', 'is_active' => true],
            ['code' => 'CFR', 'name' => 'Cost and Freight', 'description' => 'El vendedor paga costos y flete hasta el puerto de destino.', 'is_active' => true],
            ['code' => 'DDP', 'name' => 'Delivered Duty Paid', 'description' => 'El vendedor asume todos los costos y riesgos hasta la entrega en destino.', 'is_active' => true],
        ];

        foreach ($incoterms as $incoterm) {
            Incoterm::create($incoterm);
        }
    }
}
