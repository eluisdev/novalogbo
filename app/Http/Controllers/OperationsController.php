<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Cost;
use App\Models\Customer;
use App\Models\ExchangeRate;
use App\Models\Incoterm;
use App\Models\QuantityDescription;
use App\Models\Quotation;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OperationsController extends Controller
{
    public function index()
    {
        $operations = [];

        return view('operations.index', compact("operations"));
    }

    public function showQuotation($id)
    {
        $quotation = Quotation::with([
            'customer',
            'products.origin',
            'products.destination',
            'products.incoterm',
            'products.quantityDescription',
            'services.service',
            'costDetails.cost'
        ])->findOrFail($id);

        // Estructura base similar al array de ejemplo
        $response = [
            'id' => $quotation->id,
            'NIT' => $quotation->customer_nit,
            'currency' => $quotation->currency,
            'exchange_rate' => $quotation->exchange_rate,
            'reference_number' => $quotation->reference_number,
            'status' => $quotation->status,
            'products' => [],
            'services' => [],
            'costs' => []
        ];

        // Procesar productos
        foreach ($quotation->products as $key => $product) {
            $response['products'][$key + 1] = [
                'name' => $product->name,
                'origin_id' => (string)$product->origin_id,
                'destination_id' => (string)$product->destination_id,
                'weight' => (string)$product->weight,
                'incoterm_id' => (string)$product->incoterm_id,
                'quantity' => $product->quantity,
                'quantity_description_id' => (string)$product->quantity_description_id,
                'volume' => (string)$product->volume,
                'volume_unit' => $product->volume_unit,
                'description' => $product->description,
                // Agregar nombres adicionales
                'origin_name' => $product->origin->name,
                'destination_name' => $product->destination->name,
                'incoterm_name' => $product->incoterm->code,
                'quantity_description_name' => $product->quantityDescription->name
            ];
        }

        // Procesar servicios (manteniendo la estructura include/exclude)
        foreach ($quotation->services as $service) {
            $response['services'][$service->service_id] = $service->included ? 'include' : 'exclude';
            // Agregar nombre del servicio
            $response['service_names'][$service->service_id] = $service->service->name;
        }

        foreach ($quotation->costDetails as $costDetail) {
            $response['costs'][$costDetail->cost_id] = [
                'enabled' => '1',
                'amount' => (string)$costDetail->amount,
                'cost_id' => (string)$costDetail->cost_id,
                'concept' => $costDetail->concept,
                // Agregar nombre del costo
                'cost_name' => $costDetail->cost->name
            ];
        }

        $response['customer_info'] = [
            'name' => $quotation->customer->name,
            'email' => $quotation->customer->email,
            'phone' => $quotation->customer->phone
        ];

        return view('operations.showQuotation', ['quotation_data' => $response]);
    }

    public function create($id)
    {
        $quotation = Quotation::with([
            'customer',
            'products.origin.country',
            'products.destination.country',
            'products.incoterm',
            'products.quantityDescription',
            'services.service',
            'costDetails.cost'
        ])->findOrFail($id);

        // Estructura base para el formulario de edición
        $formData = [
            'id' => $quotation->id,
            'NIT' => $quotation->customer_nit,
            'reference_number' => $quotation->reference_number,
            'reference_customer' => $quotation->reference_customer,
            'currency' => $quotation->currency,
            'exchange_rate' => $quotation->exchange_rate,
            'reference_number' => $quotation->reference_number,
            'customer' => $quotation->customer,
            'products' => [],
            'services' => [],
            'costs' => []
        ];

        // Procesar productos para edición
        foreach ($quotation->products as $key => $product) {
            $formData['products'][$key + 1] = [
                'name' => $product->name,
                'origin_id' => (string)$product->origin_id,
                'destination_id' => (string)$product->destination_id,
                'weight' => (string)$product->weight,
                'incoterm_id' => (string)$product->incoterm_id,
                'quantity' => $product->quantity,
                'quantity_description_id' => (string)$product->quantity_description_id,
                'volume' => (string)$product->volume,
                'volume_unit' => $product->volume_unit,
                'description' => $product->description,
                // Datos adicionales para mostrar en el formulario
                'origin_name' => $product->origin->name,
                'origin_country_id' => $product->origin->country->id,
                'destination_name' => $product->destination->name,
                'destination_country_id' => $product->destination->country->id,
                'incoterm_name' => $product->incoterm->code,
                'quantity_description_name' => $product->quantityDescription->name
            ];
        }

        // Procesar servicios para edición (formato include/exclude)
        foreach ($quotation->services as $service) {
            $formData['services'][$service->service_id] = $service->included ? 'include' : 'exclude';
        }

        // Procesar costos para edición
        foreach ($quotation->costDetails as $costDetail) {
            $formData['costs'][$costDetail->cost_id] = [
                'enabled' => '1', // Todos los costos guardados están habilitados
                'amount' => (string)$costDetail->amount,
                'cost_id' => (string)$costDetail->cost_id,
                'concept' => $costDetail->concept,
                'cost_name' => $costDetail->cost->name
            ];
        }

        // Obtener listas completas para los selects del formulario
        $formSelects = [
            'incoterms' => Incoterm::where('is_active', 1)->get(),
            'customers' => Customer::where('active', 1)->get(),
            'cities' => City::whereNull('deleted_at')->get(),
            'services' => Service::where('is_active', 1)->get(),
            'costs' => Cost::where('is_active', 1)->get(),
            'exchangeRates' => ExchangeRate::where('active', 1)->get(),
            'QuantityDescriptions' => QuantityDescription::where('is_active', 1)->get(),
        ];

        // Preparar ciudades por país para selects anidados


        return view(
            'operations.create',
            [
                'quotation_data' => [
                    'formData' => $formData, // Datos específicos de esta cotización
                    'formSelects' => $formSelects, // Listas completas para selects
                    'quotation_id' => $id, // ID de la cotización para el formulario,
                ],
            ]
        );
    }

    public function previewCreate()
    {
        if (Auth::user()->role_id === 1) {
            $quotations = Quotation::with(['customer', 'user'])
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $quotations = Quotation::with(['customer'])
                ->where('users_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->get();
        }
        return view('operations.previewCreate', compact("quotations"));
    }
}
