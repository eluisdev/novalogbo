<?php

namespace Database\Seeders;

use App\Models\Cost;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CostSeeder extends Seeder
{
    public function run()
    {
        $costTypes = [
            'FLETE AEREO',
            'FLETE TERRESTRE',
            'FLETE MARITIMO',
            'COSTOS EN ORIGEN',
            'COSTOS EN PUERTO DE ORIGEN',
            'COSTOS EN DESTINO',
            'COSTOS EN PUERTO DE DESTINO',
            'SERVICIO LOGISTICO',
            'DESCONSOLIDACION',
            'CCFEE',
            'DGR FEE',
            'RECOJO DE EMBARQUE',
            'COSTO REEMBALAJE',
            'COSTO REPALETIZADO',
            'INSPECCION NARCOTICOS',
            'MANIPULEO',
            'COSTO FUMIGACION'
        ];

        foreach ($costTypes as $type) {
            Cost::firstOrCreate([
                'name' => $type
            ], [
                'is_active' => true
            ]);
        }
    }
}
