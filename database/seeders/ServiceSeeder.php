<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            'Flete marítimo',
            'Recojo del embarque en origen',
            'Gestión en puerto de origen',
            'Gestión en puerto de destino',
            'THC en puerto de origen',
            'THC en puerto de destino',
            'Emisión de CRT',
            'Emisión de BL',
            'Seguimiento e información constante',
            'Cargos en puerto de destino',
            'Cargos en puerto de origen',
            'Flete terrestre',
            'Flete aéreo',
            'Gestión aduanera en origen',
            'Gestión aduanera en destino',
            'Pagos ASPB',
            'Desconsolidación del cntr en puerto',
            'Desconsolidación del embarque',
            'Transporte local (recinto aduana - almacen cliente)',
            'Transbordo de embarque',
            'Emisión DGR',
            'Manipuleo de embarque',
            'Desconsolidación aérea',
            'Seguro de embarque',
            'Inspecciones adicionales (rayos x)',
            'Reembalaje o repaletizado',
            'Inspección narcóticos en aeropuerto de origen',
            'Pago de impuestos de aduana en destino',
            'Entrega de embarque al consignatario'
        ];

        foreach ($services as $service) {
            Service::create([
                'name' => $service,
                'is_active' => true
            ]);
        }
    }
}
