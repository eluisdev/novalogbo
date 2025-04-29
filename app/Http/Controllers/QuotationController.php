<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\City;
use App\Models\Cost;
use App\Models\Country;
use App\Models\Product;
use App\Models\Service;
use App\Models\Customer;
use App\Models\Incoterm;
use App\Models\Quotation;
use App\Models\CostDetail;
use App\Models\ExchangeRate;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\PhpWord;
use App\Models\QuotationService;
use PhpOffice\PhpWord\IOFactory;
use Illuminate\Support\Facades\DB;
use App\Models\QuantityDescription;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\SimpleType\JcTable;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class QuotationController extends Controller
{
    //

    public function index()
    {
        if (Auth::user()->role_id === 1 || Auth::user()->role_id === 3) {
            $quotations = Quotation::with(['customer', 'user'])
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $quotations = Quotation::with(['customer'])
                ->where('users_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('quotations.index', compact('quotations'));
    }


    public function create()
    {
        $incoterms = Incoterm::where('is_active', 1)->get();
        $services = Service::where('is_active', 1)->get();
        $countries = Country::whereNull('deleted_at')->get();
        $cities = City::whereNull('deleted_at')->get();
        $costs = Cost::where('is_active', 1)->get();
        $exchangeRates = ExchangeRate::where('active', 1)->get();
        $customers = Customer::where('active', 1)->get();
        $QuantityDescriptions = QuantityDescription::where('is_active', 1)->get();

        return view('quotations.create', compact(
            'incoterms',
            'services',
            'countries',
            'cities',
            'costs',
            'exchangeRates',
            'customers',
            'QuantityDescriptions'
        ));
    }

    public function searchCustomer(Request $request)
    {
        $search = $request->get('search');

        $customers = Customer::where('NIT', 'LIKE', "%{$search}%")
            ->orWhere('name', 'LIKE', "%{$search}%")
            ->where('active', true)
            ->select('NIT as id', 'name', 'email')
            ->limit(10)
            ->get();

        return response()->json($customers);
    }

    public function searchQuantityDescription(Request $request)
    {
        $search = $request->get('search');

        $quantityDescription = QuantityDescription::where('name', 'LIKE', "%{$search}%")
            ->where('is_active', true)
            ->get();

        return response()->json($quantityDescription);
    }

    public function searchLocation(Request $request)
    {
        $request->validate([
            'searchTerm' => 'required|string|max:255',
        ]);

        $searchTerm = trim(strtolower($request->input('searchTerm')));

        if (strlen($searchTerm) < 2) {
            return response()->json(['success' => false]);
        }

        try {
            $searchPattern = "%{$searchTerm}%";

            // 1. Países que coinciden (con todas sus ciudades)
            $matchingCountries = Country::whereRaw('LOWER(name) LIKE ?', [$searchPattern])
                ->with(['cities' => function ($query) {
                    $query->select('id', 'name', 'country_id');
                }])
                ->get(['id', 'name']);

            // 2. Ciudades que coinciden (con su país)
            $matchingCities = City::whereRaw('LOWER(name) LIKE ?', [$searchPattern])
                ->with(['country' => function ($query) {
                    $query->select('id', 'name');
                }])
                ->get(['id', 'name', 'country_id']);

            // Procesar resultados
            $results = $matchingCountries->map(function ($country) use ($searchTerm) {
                return [
                    'id' => $country->id,
                    'name' => $country->name,
                    'type' => 'country',
                    'match_type' => 'country',
                    'cities' => $country->cities->map(function ($city) use ($country, $searchTerm) {
                        return [
                            'id' => $city->id,
                            'name' => $city->name,
                            'type' => 'city',
                            'match_type' => str_contains(strtolower($city->name), $searchTerm) ? 'city' : null,
                            'country_id' => $country->id,
                            'country_name' => $country->name
                        ];
                    })->toArray()
                ];
            })->toArray();

            // Agregar ciudades coincidentes cuyos países no coincidieron
            $processedCountryIds = collect($results)->pluck('id')->toArray();
            $processedCityIds = collect($results)->pluck('cities.*.id')->flatten()->toArray();

            foreach ($matchingCities as $city) {
                if (!in_array($city->id, $processedCityIds)) {
                    $country = $city->country;

                    if (in_array($country->id, $processedCountryIds)) {
                        $countryIndex = array_search($country->id, array_column($results, 'id'));
                        $results[$countryIndex]['cities'][] = [
                            'id' => $city->id,
                            'name' => $city->name,
                            'type' => 'city',
                            'match_type' => 'city',
                            'country_id' => $country->id,
                            'country_name' => $country->name
                        ];
                    } else {
                        $results[] = [
                            'id' => $country->id,
                            'name' => $country->name,
                            'type' => 'country',
                            'match_type' => null,
                            'cities' => [[
                                'id' => $city->id,
                                'name' => $city->name,
                                'type' => 'city',
                                'match_type' => 'city',
                                'country_id' => $country->id,
                                'country_name' => $country->name
                            ]]
                        ];
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => config('app.debug') ? $e->getMessage() : null
            ]);
        }
    }

    public function storeCustomer(Request $request)
    {
        try {
            // Validar los datos de entrada
            $validator = Validator::make($request->all(), [
                'NIT' => 'required|integer|unique:customers',
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:customers,email',
                'phone' => 'nullable|string|max:20',
                'cellphone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:255',
                'department' => 'nullable|string|max:100',
                'role_id' => 'required|exists:roles,id',
            ], [
                'NIT.required' => 'El NIT\CI es obligatorio.',
                'NIT.integer' => 'El NIT\CI debe ser un número entero.',
                'NIT.unique' => 'Este NIT\CI ya está en uso.',
                'name.required' => 'La razón social es obligatoria.',
                'email.required' => 'El correo electrónico es obligatorio.',
                'email.email' => 'El correo electrónico debe ser una dirección válida.',
                'email.unique' => 'Este correo electrónico ya está en uso.',
                'phone.nullable' => 'El teléfono es opcional.',
                'cellphone.nullable' => 'El celular es opcional.',
                'address.nullable' => 'La dirección es opcional.',
                'department.nullable' => 'El departamento o lugar de residencia es opcional.',
                'role_id.required' => 'El rol es obligatorio.',
                'role_id.exists' => 'El rol seleccionado no es válido.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors(),
                    'customer' => null
                ], 422);
            }

            // Crear un nuevo cliente
            $customer = Customer::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Cliente creado exitosamente',
                'customer' => $customer,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el cliente: ' . $e->getMessage(),
                'customer' => null
            ], 500);
        }
    }

    public function storeQuantityDescripcion(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'is_active' => 'required|boolean',
            ], [
                'name.required' => 'El nombre es obligatorio.',
                'name.string' => 'El nombre debe ser una cadena de texto.',
                'name.max' => 'El nombre no puede exceder los 255 caracteres.',
                'is_active.required' => 'El estado es obligatorio.',
                'is_active.boolean' => 'El estado debe ser verdadero o falso.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors(),
                    'quantityDescription' => null
                ], 422);
            }

            $quantityDescription = QuantityDescription::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Descripcion de cantidad creada exitosamente',
                'quantityDescription' => $quantityDescription,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el cliente: ' . $e->getMessage(),
                'quantityDescription' => null
            ], 500);
        }
    }

    public function store(Request $request)
    {
        // dd($request);
        $validatedData = $request->validate(
            [
                'reference_customer' => 'nullable|string',
                'reference_number' => 'nullable|string',
                'currency' => 'required|string|max:10',
                'exchange_rate' => 'required|numeric',
                'NIT' => 'required|exists:customers,NIT',
                'products' => 'nullable|array',
                'products.*.name' => 'nullable|string',
                'products.*.origin_id' => 'required_with:products',
                'products.*.destination_id' => 'required_with:products',
                'products.*.incoterm_id' => 'required_with:products',
                'products.*.quantity' => 'required_with:products|string',
                'products.*.quantity_description_id' => 'nullable|exists:quantity_descriptions,id',
                'products.*.weight' => 'nullable|numeric',
                'products.*.volume' => 'nullable|numeric',
                'products.*.volume_unit' => 'nullable|string|max:10',
                'products.*.description' => 'nullable|string',
                'products.*.is_container' => 'nullable|boolean',
                'costs' => 'required|array',
                'services' => 'nullable|array',
                'observations' => 'nullable|string',
                'insurance' => 'nullable|string',
                'payment_method' => 'nullable|string',
                'validity' => 'nullable|string',
                'juncture' => 'nullable|string',
                'is_parallel' => 'nullable|boolean',
            ],
            [
                'currency.required' => 'La moneda es obligatoria.',
                'currency.string' => 'La moneda debe ser una cadena de texto.',
                'currency.max' => 'La moneda no puede exceder los 10 caracteres.',
                'exchange_rate.required' => 'El tipo de cambio es obligatorio.',
                'exchange_rate.numeric' => 'El tipo de cambio debe ser un número.',
                'NIT.required' => 'El NIT es obligatorio.',
                'NIT.exists' => 'El NIT no existe en la base de datos.',
                'products.array' => 'Los productos deben ser un arreglo.',
                'products.*.origin_id.required_with' => 'El origen es obligatorio.',
                'products.*.destination_id.required_with' => 'El destino es obligatorio.',
                'products.*.incoterm_id.required_with' => 'El incoterm es obligatorio.',
                'products.*.quantity.required_with' => 'La cantidad es obligatoria.',
                'products.*.quantity.string' => 'La cantidad debe ser una cadena de texto.',
                'products.*.weight.numeric' => 'El peso debe ser un número.',
                'products.*.volume.numeric' => 'El volumen debe ser un número.',
                'products.*.volume_unit.string' => 'La unidad de volumen debe ser una cadena de texto.',
                'products.*.volume_unit.max' => 'La unidad de volumen no puede exceder los 10 caracteres.',
                'products.*.description.string' => 'La descripción debe ser una cadena de texto.',
                'costs.required' => 'Los costos son obligatorios.',
            ]
        );

        DB::beginTransaction();

        try {
            $quotation = new Quotation();
            $quotation->delivery_date = Carbon::now();
            $currentYear = Carbon::now()->year;
            $maxAttempts = 100;
            $attempts = 0;
            do {
                $count = Quotation::whereYear('created_at', $currentYear)->count();
                $nextNumber = str_pad($count + 1, 3, '0', STR_PAD_LEFT);
                $referenceNumber = "{$nextNumber}/" . substr($currentYear, -2);
                $isUnique = !Quotation::where('reference_number', $referenceNumber)
                    ->whereYear('created_at', $currentYear)
                    ->exists();
                $attempts++;
                if ($attempts > $maxAttempts) {
                    error_log("Error al generar un número de referencia de cotización único después de {$maxAttempts} intentos.");
                    $quotation->reference_number = null;
                    break;
                }
            } while (!$isUnique);

            $quotation->reference_number = $referenceNumber;
            $quotation->reference_customer = $validatedData['reference_customer'] ?? null;
            $quotation->currency = $validatedData['currency'];
            $quotation->exchange_rate = $validatedData['exchange_rate'];
            $quotation->amount = 0;
            $quotation->customer_nit = $validatedData['NIT'];
            $quotation->users_id = Auth::id();
            $quotation->insurance = $validatedData['insurance'] ?? null;
            $quotation->payment_method = $validatedData['payment_method'] ?? null;
            $quotation->validity = $validatedData['validity'] ?? null;
            $quotation->juncture = $validatedData['juncture'] ?? null;
            $quotation->observations = $validatedData['observations'] ?? null;
            $quotation->status = 'pending';
            $quotation->is_parallel = $validatedData['is_parallel'] ?? false;
            $quotation->save();

            $totalAmount = 0;

            if ($request->has('products')) {
                foreach ($request->products as $product) {
                    $productDetail = new Product();
                    $productDetail->quotation_id = $quotation->id;
                    $productDetail->name = $product['name'] ?? '';
                    $productDetail->origin_id = $product['origin_id'];
                    $productDetail->destination_id = $product['destination_id'];
                    $productDetail->incoterm_id = $product['incoterm_id'];
                    $productDetail->quantity = $product['quantity'];
                    $productDetail->quantity_description_id = $product['quantity_description_id'] ?? null;
                    $productDetail->weight = $product['weight'];
                    $productDetail->volume = $product['volume'];
                    $productDetail->volume_unit = $product['volume_unit'];
                    $productDetail->description = $product['description'] ?? '';
                    $productDetail->is_container = $product['is_container'] ?? false;
                    $productDetail->save();
                }
            }

            // Process cost details for this quotation
            if ($request->has('costs')) {
                foreach ($request->costs as $cost) {
                    if (isset($cost['enabled']) && $cost['enabled']) {
                        $costDetail = new CostDetail();
                        $costDetail->quotation_id = $quotation->id;
                        $costDetail->cost_id = $cost['cost_id'];
                        $costDetail->concept = $cost['concept'] ?? '';
                        $costDetail->amount = $cost['amount'];
                        $costDetail->amount_parallel = $cost['amount_parallel'] ?? null;
                        $costDetail->currency = $quotation->currency;
                        $costDetail->save();

                        $totalAmount += $cost['amount'];
                    }
                }
            }

            // Process services
            if ($request->has('services')) {
                foreach ($request->services as $key => $service) {
                    if ($service !== 'none') {
                        $quotationService = new QuotationService();
                        $quotationService->quotation_id = $quotation->id;
                        $quotationService->service_id = $key;
                        $quotationService->included = $service == 'include' ? true : false;
                        $quotationService->save();
                    }
                }
            }

            // Update total amount
            $quotation->amount = $totalAmount;
            $quotation->save();

            DB::commit();

            return redirect()->route('quotations.show', $quotation->id)
                ->with('success', 'Cotización creada satisfactoriamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Error creando la cotización: ' . $e->getMessage());
        }
    }
    public function show($id)
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
            'reference_customer' => $quotation->reference_customer,
            'delivery_date' => $quotation->delivery_date,
            'insurance' => $quotation->insurance ?? null,
            'payment_method' => $quotation->payment_method ?? null,
            'validity' => $quotation->validity ?? null,
            'juncture' => $quotation->juncture ?? null,
            'observations' => $quotation->observations ?? null,
            'is_parallel' => $quotation->is_parallel ?? false,
            'products' => [],
            'services' => [],
            'costs' => []
        ];

        foreach ($quotation->products as $key => $product) {
            $response['products'][$key + 1] = [
                'name' => $product->name,
                'origin_id' => (string)$product->origin_id,
                'destination_id' => (string)$product->destination_id,
                'weight' => (string)$product->weight,
                'incoterm_id' => (string)$product->incoterm_id,
                'quantity' => $product->quantity,
                'quantity_description_id' => (string)$product->quantity_description_id ?? null,
                'volume' => (string)$product->volume,
                'volume_unit' => $product->volume_unit,
                'description' => $product->description,
                'origin_name' => $product->origin->name,
                'destination_name' => $product->destination->name,
                'incoterm_name' => $product->incoterm->code,
                'quantity_description_name' => $product->quantityDescription->name ?? null,
                'is_container' => $product->is_container,
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
                'amount_parallel' => (string)$costDetail->amount_parallel,
                'cost_id' => (string)$costDetail->cost_id,
                'concept' => $costDetail->concept,
                'cost_name' => $costDetail->cost->name
            ];
        }

        $response['customer_info'] = [
            'name' => $quotation->customer->name,
            'email' => $quotation->customer->email,
            'phone' => $quotation->customer->phone
        ];

        return view('quotations.show', ['quotation_data' => $response]);
    }

    public function edit($id)
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
            'status' => $quotation->status,
            'delivery_date' => $quotation->delivery_date,
            'insurance' => $quotation->insurance ?? null,
            'payment_method' => $quotation->payment_method ?? null,
            'validity' => $quotation->validity ?? null,
            'juncture' => $quotation->juncture ?? null,
            'observations' => $quotation->observations ?? null,
            'is_parallel' => $quotation->is_parallel ?? false,
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
                'quantity_description_id' => (string)$product->quantity_description_id ?? null,
                'volume' => (string)$product->volume,
                'volume_unit' => $product->volume_unit,
                'description' => $product->description,
                // Datos adicionales para mostrar en el formulario
                'origin_name' => $product->origin->name,
                'origin_country_id' => $product->origin->country->id,
                'destination_name' => $product->destination->name,
                'destination_country_id' => $product->destination->country->id,
                'incoterm_name' => $product->incoterm->code,
                'quantity_description_name' => $product->quantityDescription->name ?? null,
                'is_container' => $product->is_container,
            ];
        }

        // Procesar servicios para edición (formato include/exclude)
        foreach ($quotation->services as $service) {
            $formData['services'][$service->service_id] = $service->included ? 'include' : 'exclude';
        }

        // Procesar costos para edición
        foreach ($quotation->costDetails as $costDetail) {
            $formData['costs'][$costDetail->cost_id] = [
                'enabled' => '1',
                'amount' => (string)$costDetail->amount,
                'amount_parallel' => (string)$costDetail->amount_parallel,
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
            'quotations.edit',
            [
                'quotation_data' => [
                    'formData' => $formData, // Datos específicos de esta cotización
                    'formSelects' => $formSelects, // Listas completas para selects
                    'quotation_id' => $id, // ID de la cotización para el formulario,
                ],
            ]
        );
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'NIT' => 'required|exists:customers,NIT',
            'currency' => 'required|string|max:10',
            'exchange_rate' => 'required|numeric',
            'reference_customer' => 'nullable|string',
            'reference_number' => 'nullable|string',
            'products' => 'required|array',
            'products.*.name' => 'nullable|string',
            'products.*.origin_id' => 'required|exists:cities,id',
            'products.*.destination_id' => 'required|exists:cities,id',
            'products.*.incoterm_id' => 'required|exists:incoterms,id',
            'products.*.quantity' => 'required|string',
            'products.*.quantity_description_id' => 'nullable|exists:quantity_descriptions,id',
            'products.*.weight' => 'nullable|numeric',
            'products.*.volume' => 'nullable|numeric',
            'products.*.volume_unit' => 'nullable|string|max:10',
            'products.*.description' => 'nullable|string',
            'products.*.is_container' => 'nullable|boolean',
            'services' => 'nullable|array',
            'costs' => 'required|array',
            'costs.*.cost_id' => 'required|exists:costs,id',
            'costs.*.amount' => 'nullable|numeric',
            'costs.*.amount_parallel' => 'nullable|numeric',
            'costs.*.concept' => 'nullable|string',
            'observations' => 'nullable|string',
            'insurance' => 'nullable|string',
            'payment_method' => 'nullable|string',
            'validity' => 'nullable|string',
            'juncture' => 'nullable|string',
            'is_parallel' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {

            $quotation = Quotation::findOrFail($id);

            $junctureValue = $validatedData['juncture'] ?? null;

            if (isset($validatedData['is_parallel']) && $validatedData['is_parallel']) {
                $junctureValue = $validatedData['juncture'] ?? null;
            } else {
                $junctureValue = null;
            }

            // Actualizar datos básicos de la cotización
            $quotation->update([
                'customer_nit' => $validatedData['NIT'],
                'delivery_date' => Carbon::now(),
                'currency' => $validatedData['currency'],
                'exchange_rate' => $validatedData['exchange_rate'],
                'reference_number' => $validatedData['reference_number'],
                'reference_customer' => $validatedData['reference_customer'] ?? null,
                'insurance' => $validatedData['insurance'] ?? null,
                'payment_method' => $validatedData['payment_method'] ?? null,
                'validity' => $validatedData['validity'] ?? null,
                'juncture' => $junctureValue,
                'observations' => $validatedData['observations'] ?? null,
                'is_parallel' => $validatedData['is_parallel'] ?? false,
                'status' => 'pending',
                'amount' => 0 // Se recalculará al final
            ]);

            // Eliminar productos, servicios y costos existentes
            $quotation->products()->delete();
            $quotation->services()->delete();
            $quotation->costDetails()->delete();
            $quotation->billingNote()->delete();
            $quotation->invoices()->delete();

            //dd($validatedData['products']);
            // Procesar y guardar los nuevos productos
            foreach ($validatedData['products'] as $productData) {

                $quantityDescriptionId = null;
                if (isset($productData['is_container']) && $productData['is_container']) {
                    $quantityDescriptionId = null;
                } elseif (isset($productData['quantity_description_id'])) {
                    $quantityDescriptionId = $productData['quantity_description_id'];
                }

                $product = new Product([
                    'quotation_id' => $quotation->id,
                    'name' => $productData['name'] ?? null,
                    'origin_id' => $productData['origin_id'],
                    'destination_id' => $productData['destination_id'],
                    'incoterm_id' => $productData['incoterm_id'],
                    'quantity' => $productData['quantity'],
                    'quantity_description_id' => $quantityDescriptionId,
                    'is_container' => $productData['is_container'] ?? false,
                    'weight' => $productData['weight'] ?? null,
                    'volume' => $productData['volume'] ?? null,
                    'volume_unit' => $productData['volume_unit'] ?? null,
                    'description' => $productData['description'] ?? $productData['name']
                ]);
                $product->save();
            }

            // Procesar y guardar los servicios
            if ($request->has('services')) {
                foreach ($validatedData['services'] as $serviceId => $status) {
                    // if (is_numeric($serviceId)) { // Asegurar que es un ID válido
                    if (is_numeric($serviceId) && $status !== 'none') { // Asegurar que es un ID válido
                        $quotationService = new QuotationService([
                            'quotation_id' => $quotation->id,
                            'service_id' => $serviceId,
                            'included' => $status === 'include'
                        ]);
                        $quotationService->save();
                    }
                }
            }

            $totalAmount = 0;
            foreach ($validatedData['costs'] as $costId => $costData) {
                if (isset($costData['amount'])) {
                    $amountParallelValue = null;
                    if (isset($validatedData['is_parallel']) && $validatedData['is_parallel']) {
                        $amountParallelValue = $costData['amount_parallel'] ?? null;
                    }
                    $costDetail = new CostDetail([
                        'quotation_id' => $quotation->id,
                        'cost_id' => $costData['cost_id'],
                        'amount' => $costData['amount'],
                        'amount_parallel' => $amountParallelValue,
                        'currency' => $validatedData['currency'],
                        'concept' => $costData['concept'] ?? ''
                    ]);
                    $costDetail->save();

                    $totalAmount += $costData['amount'];
                }
            }
            // Actualizar el monto total de la cotización
            $quotation->amount = $totalAmount;
            $quotation->save();

            DB::commit();

            return redirect()->route('quotations.show', $quotation->id)
                ->with('success', 'Cotización actualizada exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error al actualizar la cotización: ' . $e->getMessage());
        }
    }


    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required'
        ]);

        $quotation = Quotation::findOrFail($id);
        $quotation->delivery_date = Carbon::now();
        $quotation->status = $request->status;
        $quotation->save();

        return redirect()->route('quotations.show', $quotation->id)
            ->with('success', 'Estado de la cotización actualizado satisfactoriamente.');
    }


    public function destroy($id)
    {
        $quotation = Quotation::findOrFail($id);

        // Verificar permisos (solo admin o el creador puede eliminar)
        if (Auth::user()->role_id !== 1 && $quotation->users_id !== Auth::id()) {
            return back()->with('error', 'No tienes permiso para eliminar esta cotización');
        }

        DB::beginTransaction();

        try {
            $quotation->products()->delete();
            $quotation->services()->delete();
            $quotation->costDetails()->delete();

            $quotation->delete();

            DB::commit();

            return redirect()->route('quotations.index')
                ->with('success', 'Cotización eliminada exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al eliminar la cotización: ' . $e->getMessage());
        }
    }

    public function generarCotizacion(Request $request)
    {
        $validated = $request->validate([
            'quotation_id' => 'required|integer',
            'visible' => 'required|boolean'
        ]);
        $visible = $validated['visible'] ?? true;

        $quotation = Quotation::with('user.role')->findOrFail($validated['quotation_id']);
        // Get client data from the quotation
        $clientData = $this->getClientData($quotation->customer_nit);

        // Get products, services and costs data from the quotation
        $productsData = $this->getProductsData($quotation->products);

        $servicesData = $this->getServicesData($quotation->services);
        $costsData = $this->getCostsData($quotation->costDetails);

        // Calculate totals in both currencies
        $totalCostBS = 0;
        $totalCostForeign = 0;
        $totalCostForeignParallel = 0;

        foreach ($costsData as $item) {
            $amount =  $item['amount'];
            if ($item['currency'] == 'BS') {
                $totalCostBS += $amount;
                $totalCostForeign += $amount / $quotation->exchange_rate;
                $totalCostForeignParallel =  $totalCostForeignParallel + ($item['amount_parallel'] ??   $item['amount']) / $quotation->exchange_rate;
            } else {
                $totalCostForeign += $amount;
                $totalCostForeignParallel =  $totalCostForeignParallel + ($item['amount_parallel'] ?? $item['amount']);
                $totalCostBS += $amount * $quotation->exchange_rate;
            }
        }

        $totalCostBSFormatted = number_format($totalCostBS, 2, ',', '.');
        $totalCostForeignFormatted = number_format($totalCostForeign, 2, ',', '.');
        $totalCostForeignFormattedParallel = number_format($totalCostForeignParallel, 2, ',', '.');
        $deliveryDate = Carbon::parse($quotation->delivery_date)->locale('es')->isoFormat('D [de] MMMM [de] YYYY');

        $quotationRef = $quotation->reference_number;

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Montserrat');
        $pageWidthInches = 8.52;
        $headerHeightInches = 2.26;
        $footerHeightInches = 1.83;

        $pageWidthPoints = $pageWidthInches * 72;
        $headerHeightPoints = $headerHeightInches * 72;
        $footerHeightPoints = $footerHeightInches * 72;

        $section = $phpWord->addSection([
            'paperSize' => 'Letter',
            'marginTop' => Converter::inchToTwip(2.26),
            'marginBottom' => Converter::inchToTwip(1.97),
        ]);

        if ($visible) {
            $header = $section->addHeader();
            $header->addImage(
                public_path('images/Header.png'),
                [
                    'width' => $pageWidthPoints,
                    'height' => $headerHeightPoints,
                    'positioning' => 'absolute',
                    'posHorizontal' => \PhpOffice\PhpWord\Style\Image::POSITION_HORIZONTAL_LEFT,
                    'posHorizontalRel' => 'page',
                    'posVerticalRel' => 'page',
                    'marginTop' => 0,
                    'marginLeft' => 0
                ]
            );
            $footer = $section->addFooter();
            $footer->addImage(
                public_path('images/Footer.png'),
                [
                    'width' => $pageWidthPoints,
                    'height' => $footerHeightPoints,
                    'positioning' => 'absolute',
                    'posHorizontal' => \PhpOffice\PhpWord\Style\Image::POSITION_HORIZONTAL_LEFT,
                    'posHorizontalRel' => 'page',
                    'posVertical' => \PhpOffice\PhpWord\Style\Image::POSITION_VERTICAL_BOTTOM,
                    'posVerticalRel' => 'page',
                    'marginLeft' => 0,
                    'marginBottom' => 0
                ]
            );
        }

        // Resto del encabezado del documento...
        $section->addText(
            $deliveryDate,
            ['size' => 11],
            ['spaceAfter' => Converter::pointToTwip(11), 'align' => 'right']
        );
        $section->addText(
            'Señores',
            ['size' => 11],
            ['spaceAfter' => Converter::pointToTwip(11)]
        );
        $section->addText(
            $clientData['name'],
            ['size' => 11, 'bold' => true, 'allCaps' => true],
            ['spaceAfter' => Converter::pointToTwip(11)]
        );
        $section->addText(
            'Presente. -',
            ['size' => 11],
            ['spaceAfter' => Converter::pointToTwip(11)]
        );
        $section->addText(
            'REF: COTIZACIÓN ' . $quotationRef,
            ['size' => 11, 'underline' => 'single', 'bold' => true, 'allCaps' => true],
            ['spaceAfter' => Converter::pointToTwip(11)]
        );
        $section->addText(
            'Estimado cliente, por medio la presente tenemos el agrado de enviarle nuestra cotización de acuerdo con su requerimiento e información proporcionada.',
            ['size' => 11],
            ['spaceAfter' => Converter::pointToTwip(11)]
        );

        // Tabla de datos del envío para múltiples productos
        $tableStyle = [
            'borderColor' => '000000',
            'cellMarginLeft' => 50,
            'cellMarginRight' => 50,
            'cellMarginTop' => 50,
            'cellMarginBottom' => 50,
            'layout' => \PhpOffice\PhpWord\Style\Table::LAYOUT_FIXED,
            'spacing' => 0,
            'lineHeight' => 1.0
        ];
        $phpWord->addTableStyle('shipmentTable', $tableStyle);
        $table = $section->addTable('shipmentTable');
        $compactParagraphStyle = [
            'spaceBefore' => 0,
            'spaceAfter' => 0,
            'spacing' => 0,
            'lineHeight' => 1.0,
        ];

        // Primera fila - Cliente
        $table->addRow();
        $table->addCell(Converter::cmToTwip(3), [
            'valign' => 'center',
            'bgColor' => 'bdd6ee',
            'borderSize' => 1,
        ])->addText('CLIENTE', [
            'bold' => true,
            'size' => 11,
            'allCaps' => true
        ], $compactParagraphStyle);
        $table->addCell(Converter::cmToTwip(7), [
            'valign' => 'center',
            'borderSize' => 1,
        ])->addText($clientData['name'], [
            'bold' => true,
            'allCaps' => true,
            'size' => 11
        ], $compactParagraphStyle);
        $table->addCell(Converter::cmToTwip(0.5))->addText('', [
            'valign' => 'center',
        ], [
            'spaceBefore' => 0,
            'spaceAfter' => 0,
            'spacing' => 0,
            'lineHeight' => 1.0,
        ]);

        // Filas para cada producto
        foreach ($productsData as $index => $product) {
            $rowSpan = $index === 0 ? 1 : count($productsData) - 1;

            // Segunda fila - Origen y Cantidad
            $table->addRow();
            $table->addCell(Converter::cmToTwip(3), [
                'valign' => 'center',
                'bgColor' => 'bdd6ee',
                'borderSize' => 1,
            ])->addText('ORIGEN', [
                'bold' => true,
                'size' => 11
            ], $compactParagraphStyle);
            $table->addCell(Converter::cmToTwip(7), [
                'valign' => 'center',
                'borderSize' => 1,
            ])->addText($product['origin']['city'] . ', ' . $product['origin']['country'], [
                'size' => 11
            ], $compactParagraphStyle);
            $table->addCell(Converter::cmToTwip(0.5))->addText('', [
                'valign' => 'center',
            ], [
                'spaceBefore' => 0,
                'spaceAfter' => 0,
                'spacing' => 0,
                'lineHeight' => 1.0,
            ]);
            $table->addCell(Converter::cmToTwip(2), [
                'valign' => 'center',
                'bgColor' => 'bdd6ee',
                'borderSize' => 1,
            ])->addText('CANTIDAD', [
                'bold' => true,
                'size' => 11
            ], $compactParagraphStyle);

            $table->addCell(Converter::cmToTwip(3), [
                'valign' => 'center',
                'borderSize' => 1,
            ])->addText($product['quantity']['value'] . ' ' .  $product['quantity']['unit'], [
                'size' => 11
            ], $compactParagraphStyle);

            // Tercera fila - Destino y Peso
            $table->addRow();
            $table->addCell(Converter::cmToTwip(3), [
                'valign' => 'center',
                'bgColor' => 'bdd6ee',
                'borderSize' => 1,
            ])->addText('DESTINO', [
                'bold' => true,
                'size' => 11
            ], $compactParagraphStyle);
            $table->addCell(Converter::cmToTwip(7), [
                'valign' => 'center',
                'borderSize' => 1,
            ])->addText($product['destination']['city'] . ', ' . $product['destination']['country'], [
                'size' => 11
            ], $compactParagraphStyle);
            $table->addCell(Converter::cmToTwip(0.5))->addText('', [
                'valign' => 'center',
            ], [
                'spaceBefore' => 0,
                'spaceAfter' => 0,
                'spacing' => 0,
                'lineHeight' => 1.0,
            ]);
            $table->addCell(Converter::cmToTwip(2), [
                'valign' => 'center',
                'bgColor' => 'bdd6ee',
                'borderSize' => 1,
            ])->addText('PESO', [
                'bold' => true,
                'size' => 11
            ], $compactParagraphStyle);
            $table->addCell(Converter::cmToTwip(3), [
                'valign' => 'center',
                'borderSize' => 1,
            ])->addText($product['weight'] . " " . 'KG', [
                'size' => 11
            ], $compactParagraphStyle);

            // Cuarta fila - Incoterm y Volumen
            $table->addRow();
            $table->addCell(Converter::cmToTwip(3), [
                'valign' => 'center',
                'bgColor' => 'bdd6ee',
                'borderSize' => 1,
            ])->addText('INCOTERM', [
                'bold' => true,
                'size' => 11
            ], $compactParagraphStyle);
            $table->addCell(Converter::cmToTwip(7), [
                'valign' => 'center',
                'borderSize' => 1,
            ])->addText($product['incoterm'], [
                'size' => 11
            ], $compactParagraphStyle);

            $table->addCell(Converter::cmToTwip(0.5))->addText('', [
                'valign' => 'center',
            ], [
                'spaceBefore' => 0,
                'spaceAfter' => 0,
                'spacing' => 0,
                'lineHeight' => 1.0,
            ]);
            $table->addCell(Converter::cmToTwip(2), [
                'valign' => 'center',
                'bgColor' => 'bdd6ee',
                'borderSize' => 1,
            ])->addText($product['volume']['unit'] == 'm3' ? 'M3' : 'KG/VOL', [
                'bold' => true,
                'size' => 11
            ], $compactParagraphStyle);
            $table->addCell(Converter::cmToTwip(3), [
                'valign' => 'center',
                'borderSize' => 1,
            ])->addText($product['volume']['unit'] == 'm3' ? $product['volume']['value'] . " " . 'M3' : $product['volume']['value'] . " " . 'KG/VOL', [
                'size' => 11
            ], $compactParagraphStyle);

            // Agregar separación entre productos si hay más de uno
            if ($index < count($productsData) - 1) {
                $table->addRow();
                $table->addCell(Converter::cmToTwip(15.5), ['gridSpan' => 5])
                    ->addText('', ['size' => 1], [
                        'spaceBefore' => 0,
                        'spaceAfter' => 0,
                        'spacing' => 0,
                        'lineHeight' => 1.0,
                    ]);
            }
        }

        // Texto después de la tabla

        // $section->addText(
        //     'OPCIÓN 1) PAGO EN EFECTIVO EN ' . $quotation->currency,
        //     ['bold' => true, 'size' => 11],
        //     ['spaceAfter' => Converter::pointToTwip(11)]
        // );
        $section->addTextBreak(1);
        $section->addText(
            'Para el requerimiento de transporte y logística los costos se encuentran líneas abajo',
            ['size' => 11],
            ['spaceAfter' => Converter::pointToTwip(11)]
        );

        if (!$quotation->is_parallel) {
            $section->addText(
                'OPCIÓN 1) PAGO EN EFECTIVO EN BS DE EN BOLIVIA',
                ['bold' => true, 'size' => 11,  'bgColor' => 'ffff00',],
                ['spaceAfter' => Converter::pointToTwip(11)]
            );
            $table = $section->addTable([
                'width' => 400,
                'unit' => 'pct',
                'alignment' => JcTable::CENTER,
                'cellMargin' => 50,
            ]);

            // Encabezado de la tabla
            $table->addRow();
            $table->addCell(Converter::cmToTwip(10), [
                'valign' => 'center',
                'bgColor' => 'bdd6ee',
                'borderSize' => 1,
            ])->addText('CONCEPTO', [
                'bold' => true,
                'size' => 11,
                'allCaps' => true
            ], [
                'spaceBefore' => 0,
                'spaceAfter' => 0,
                'spacing' => 0,
                'lineHeight' => 1.0,
                'align' => 'center'
            ]);
            $table->addCell(Converter::cmToTwip(3), [
                'valign' => 'center',
                'bgColor' => 'bdd6ee',
                'borderSize' => 1,
            ])->addText('MONTO ' . $quotation->currency, [
                'bold' => true,
                'size' => 11,
                'allCaps' => true
            ], [
                'spaceBefore' => 0,
                'spaceAfter' => 0,
                'spacing' => 0,
                'lineHeight' => 1.0,
                'align' => 'right'
            ]);

            // Filas de costos en moneda extranjera
            foreach ($costsData as $cost) {
                $amount = floatval(str_replace(',', '', $cost['amount']));
                $amountForeign = $cost['currency'] == 'BS' ? $amount / $quotation->exchange_rate : $amount;
                $amountFormatted = number_format($amountForeign, 2, ',', '.');

                $table->addRow();
                $table->addCell(Converter::cmToTwip(10), [
                    'valign' => 'center',
                    'borderSize' => 1,
                ])->addText($cost['name'], [
                    'size' => 11
                ], [
                    'spaceBefore' => 0,
                    'spaceAfter' => 0,
                    'spacing' => 0,
                    'lineHeight' => 1.0,
                    'align' => 'left'
                ]);
                $table->addCell(Converter::cmToTwip(3), [
                    'valign' => 'center',
                    'borderSize' => 1,
                ])->addText($amountFormatted, [
                    'size' => 11
                ], [
                    'spaceBefore' => 0,
                    'spaceAfter' => 0,
                    'spacing' => 0,
                    'lineHeight' => 1.0,
                    'align' => 'right'
                ]);
            }

            // Total en moneda extranjera
            $table->addRow();
            $table->addCell(Converter::cmToTwip(10), [
                'valign' => 'center',
                'borderSize' => 1,
            ])->addText('TOTAL', [
                'size' => 11,
                'allCaps' => true
            ], [
                'spaceBefore' => 0,
                'spaceAfter' => 0,
                'spacing' => 0,
                'lineHeight' => 1.0,
                'align' => 'left'
            ]);
            $table->addCell(Converter::cmToTwip(3), [
                'valign' => 'center',
                'borderSize' => 1,
            ])->addText($totalCostForeignFormatted, [
                'size' => 11,
                'allCaps' => true,
                'bgColor' => 'ffff00',
            ], [
                'spaceBefore' => 0,
                'spaceAfter' => 0,
                'spacing' => 0,
                'lineHeight' => 1.0,
                'align' => 'right'
            ]);

            // Nota sobre el tipo de cambio
            $section->addText(
                '** De acuerdo con el TC paralelo vigente.',
                [
                    'size' => 11,
                    'bold' => true
                ],
                [
                    'spaceAfter' => Converter::pointToTwip(11),
                    'spaceBefore' => Converter::pointToTwip(11),
                ]
            );
        } else {
            $section->addText(
                'OPCION 1 ) PAGO EN BOLIVIA A UN TC DE ' . $quotation->exchange_rate,
                ['bold' => true, 'size' => 11,  'bgColor' => 'ffff00',],
                ['spaceAfter' => Converter::pointToTwip(11)]
            );
            $table = $section->addTable([
                'width' => 400,
                'unit' => 'pct',
                'alignment' => JcTable::CENTER,
                'cellMargin' => 50,
            ]);

            // Encabezado de la tabla
            $table->addRow();
            $table->addCell(Converter::cmToTwip(10), [
                'valign' => 'center',
                'bgColor' => 'bdd6ee',
                'borderSize' => 1,
            ])->addText('CONCEPTO', [
                'bold' => true,
                'size' => 11,
                'allCaps' => true
            ], [
                'spaceBefore' => 0,
                'spaceAfter' => 0,
                'spacing' => 0,
                'lineHeight' => 1.0,
                'align' => 'center'
            ]);
            $table->addCell(Converter::cmToTwip(3), [
                'valign' => 'center',
                'bgColor' => 'bdd6ee',
                'borderSize' => 1,
            ])->addText('MONTO ' . $quotation->currency, [
                'bold' => true,
                'size' => 11,
                'allCaps' => true
            ], [
                'spaceBefore' => 0,
                'spaceAfter' => 0,
                'spacing' => 0,
                'lineHeight' => 1.0,
                'align' => 'right'
            ]);

            // Filas de costos en moneda extranjera
            foreach ($costsData as $cost) {
                $amount = floatval(str_replace(',', '', $cost['amount_parallel'] ?? $cost['amount']));
                $amountForeign = $cost['currency'] == 'BS' ? $amount / $quotation->exchange_rate : $amount;
                $amountFormatted = number_format($amountForeign, 2, ',', '.');

                $table->addRow();
                $table->addCell(Converter::cmToTwip(10), [
                    'valign' => 'center',
                    'borderSize' => 1,
                ])->addText($cost['name'], [
                    'size' => 11
                ], [
                    'spaceBefore' => 0,
                    'spaceAfter' => 0,
                    'spacing' => 0,
                    'lineHeight' => 1.0,
                    'align' => 'left'
                ]);
                $table->addCell(Converter::cmToTwip(3), [
                    'valign' => 'center',
                    'borderSize' => 1,
                ])->addText($amountFormatted, [
                    'size' => 11
                ], [
                    'spaceBefore' => 0,
                    'spaceAfter' => 0,
                    'spacing' => 0,
                    'lineHeight' => 1.0,
                    'align' => 'right'
                ]);
            }

            // Total en moneda extranjera
            $table->addRow();
            $table->addCell(Converter::cmToTwip(10), [
                'valign' => 'center',
                'borderSize' => 1,
            ])->addText('TOTAL', [
                'size' => 11,
                'allCaps' => true
            ], [
                'spaceBefore' => 0,
                'spaceAfter' => 0,
                'spacing' => 0,
                'lineHeight' => 1.0,
                'align' => 'left'
            ]);
            $table->addCell(Converter::cmToTwip(3), [
                'valign' => 'center',
                'borderSize' => 1,
            ])->addText($totalCostForeignFormattedParallel, [
                'size' => 11,
                'allCaps' => true,
                'bgColor' => 'ffff00',
            ], [
                'spaceBefore' => 0,
                'spaceAfter' => 0,
                'spacing' => 0,
                'lineHeight' => 1.0,
                'align' => 'right'
            ]);



            $section->addText(
                'OPCION 2 ) PAGO EFECTIVO EN USD O DE ACUERDO CON EL TC PARALELO ',
                ['bold' => true, 'size' => 11,  'bgColor' => 'ffff00',],
                [
                    'spaceAfter' => Converter::pointToTwip(11),
                    'spaceBefore' => Converter::pointToTwip(11),
                ]
            );
            $table = $section->addTable([
                'width' => 400,
                'unit' => 'pct',
                'alignment' => JcTable::CENTER,
                'cellMargin' => 50,
            ]);

            // Encabezado de la tabla
            $table->addRow();
            $table->addCell(Converter::cmToTwip(10), [
                'valign' => 'center',
                'bgColor' => 'bdd6ee',
                'borderSize' => 1,
            ])->addText('CONCEPTO', [
                'bold' => true,
                'size' => 11,
                'allCaps' => true
            ], [
                'spaceBefore' => 0,
                'spaceAfter' => 0,
                'spacing' => 0,
                'lineHeight' => 1.0,
                'align' => 'center'
            ]);
            $table->addCell(Converter::cmToTwip(3), [
                'valign' => 'center',
                'bgColor' => 'bdd6ee',
                'borderSize' => 1,
            ])->addText('MONTO ' . $quotation->currency, [
                'bold' => true,
                'size' => 11,
                'allCaps' => true
            ], [
                'spaceBefore' => 0,
                'spaceAfter' => 0,
                'spacing' => 0,
                'lineHeight' => 1.0,
                'align' => 'right'
            ]);

            // Filas de costos en moneda extranjera
            foreach ($costsData as $cost) {
                $amount = floatval(str_replace(',', '', $cost['amount']));
                $amountForeign = $cost['currency'] == 'BS' ? $amount / $quotation->exchange_rate : $amount;
                $amountFormatted = number_format($amountForeign, 2, ',', '.');

                $table->addRow();
                $table->addCell(Converter::cmToTwip(10), [
                    'valign' => 'center',
                    'borderSize' => 1,
                ])->addText($cost['name'], [
                    'size' => 11
                ], [
                    'spaceBefore' => 0,
                    'spaceAfter' => 0,
                    'spacing' => 0,
                    'lineHeight' => 1.0,
                    'align' => 'left'
                ]);
                $table->addCell(Converter::cmToTwip(3), [
                    'valign' => 'center',
                    'borderSize' => 1,
                ])->addText($amountFormatted, [
                    'size' => 11
                ], [
                    'spaceBefore' => 0,
                    'spaceAfter' => 0,
                    'spacing' => 0,
                    'lineHeight' => 1.0,
                    'align' => 'right'
                ]);
            }

            // Total en moneda extranjera
            $table->addRow();
            $table->addCell(Converter::cmToTwip(10), [
                'valign' => 'center',
                'borderSize' => 1,
            ])->addText('TOTAL', [
                'size' => 11,
                'allCaps' => true
            ], [
                'spaceBefore' => 0,
                'spaceAfter' => 0,
                'spacing' => 0,
                'lineHeight' => 1.0,
                'align' => 'left'
            ]);
            $table->addCell(Converter::cmToTwip(3), [
                'valign' => 'center',
                'borderSize' => 1,
            ])->addText($totalCostForeignFormatted, [
                'size' => 11,
                'allCaps' => true,
                'bgColor' => 'ffff00',
            ], [
                'spaceBefore' => 0,
                'spaceAfter' => 0,
                'spacing' => 0,
                'lineHeight' => 1.0,
                'align' => 'right'
            ]);

            // Nota sobre el tipo de cambio
            $section->addText(
                '',
                [
                    'size' => 11,
                    'bold' => true
                ],
            );
        }

        if (count($servicesData['included']) > 0) {
            // Resto del documento (servicios incluidos/excluidos, etc.)
            $section->addText(
                'El servicio incluye:',
                ['size' => 11, 'bold' => true],
                ['spaceAfter' => Converter::pointToTwip(11)]
            );
        }


        $listStyleName = 'bulletStyle';
        $phpWord->addNumberingStyle(
            $listStyleName,
            array(
                'type' => 'singleLevel',
                'levels' => array(
                    array('format' => 'upperLetter', 'text' => '-', 'left' => 720, 'hanging' => 720, 'tabPos' => 1080),
                )
            )
        );

        foreach ($servicesData['included'] as $service) {
            $section->addListItem(
                $service,
                0,
                ['size' => 11],
                $listStyleName,
                [
                    'spaceAfter' => 0,
                    'spacing' => 0,
                    'lineHeight' => 1.0
                ]
            );
        }

        if (count($servicesData['excluded']) > 0) {
            $section->addText(
                'El servicio no incluye:',
                ['size' => 11, 'bold' => true],
                [
                    'spaceAfter' => Converter::pointToTwip(11),
                    'spaceBefore' => Converter::pointToTwip(11)
                ]
            );
        }


        foreach ($servicesData['excluded'] as $service) {
            $section->addListItem(
                $service,
                0,
                ['size' => 11],
                $listStyleName,
                [
                    'spaceAfter' => 0,
                    'spacing' => 0,
                    'lineHeight' => 1.0
                ]
            );
        }
        $paragraphStyle = array(
            'spaceBefore' => Converter::pointToTwip(11),
            'spaceAfter' => Converter::pointToTwip(11),
            'lineHeight' => 1.0,
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH
        );
        if ($quotation->insurance) {
            $textrun = $section->addTextRun($paragraphStyle);
            $textrun->addText(
                'Seguro: ',
                [
                    'bold' => true,
                    'size' => 11,
                ]
            );
            $textrun->addText(
                $quotation->insurance,
                [
                    'size' => 11,
                ]
            );
        } else {
            $textrun = $section->addTextRun($paragraphStyle);
            $textrun->addText(
                'Seguro: ',
                [
                    'bold' => true,
                    'size' => 11,
                ]
            );
            $textrun->addText(
                'Se recomienda tener una póliza de seguro para el embarque, ofrecemos la misma de manera adicional considerando el 0.35% sobre el valor declarado, con un min de 30 usd, previa autorización por la compañía de seguros.',
                [
                    'size' => 11,
                ]
            );
        }
        $paragraphStyle = array(
            'spaceAfter' => Converter::pointToTwip(11),
        );
        if ($quotation->payment_method) {
            $textrun = $section->addTextRun($paragraphStyle);
            $textrun->addText(
                'Forma de pago: ',
                [
                    'bold' => true,
                    'size' => 11,
                ]
            );
            $textrun->addText(
                $quotation->payment_method,
                [
                    'size' => 11,
                ]
            );
        } else {
            $textrun = $section->addTextRun($paragraphStyle);
            $textrun->addText(
                'Forma de pago: ',
                [
                    'bold' => true,
                    'size' => 11,
                ]
            );
            $textrun->addText(
                'Una vez se confirme el arribo del embarque a puerto de destino.',
                [
                    'size' => 11,
                ]
            );
        }
        if ($quotation->validity) {
            $textrun = $section->addTextRun($paragraphStyle);
            $textrun->addText(
                'Validez: ',
                [
                    'bold' => true,
                    'size' => 11,
                ]
            );
            $textrun->addText(
                $quotation->validity,
                [
                    'size' => 11,
                ]
            );
        } else {

            $textrun = $section->addTextRun($paragraphStyle);
            $textrun->addText(
                'Validez: ',
                [
                    'bold' => true,
                    'size' => 11,
                ]
            );
            $textrun->addText(
                'Los fletes son válidos hasta 10 días, posterior a ese tiempo, validar si los costos aún están vigentes.',
                [
                    'size' => 11,
                ]
            );
        }
        if ($quotation->observations) {
            $textrun = $section->addTextRun($paragraphStyle);
            $textrun->addText(
                'Observaciones: ',
                [
                    'bold' => true,
                    'size' => 11,
                ]
            );
            $textrun->addText(
                $quotation->observations,
                [
                    'size' => 11,
                ]
            );
        } else {

            $textrun = $section->addTextRun($paragraphStyle);
            $textrun->addText(
                'Observaciones: ',
                [
                    'bold' => true,
                    'size' => 11,
                ]
            );
            $textrun->addText(
                'Se debe considerar como un tiempo de tránsito 48 a 50 días hasta puerto. ',
                [
                    'size' => 11,
                ],

            );
        }

        if ($quotation->is_parallel) {
            $juncture = $quotation->juncture ?? 113;
            $section->addText(
                '**Debido a la coyuntura actual, en la presente cotización se está aplicando el costo de transferencia del ' . $juncture . '% sobre los recargos generados en origen, de acuerdo con la comisión que cobra nuestro banco actualmente, si esta llega a variar, considerar la modificación de ese monto de acuerdo con la tarifa vigente.',
                [
                    'size' => 11,
                    'bgColor' => 'ffff00',
                ],
                [
                    'spaceAfter' => Converter::pointToTwip(11),
                    'lineHeight' => 1.0,
                    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH
                ]
            );
        }

        $section->addText(
            'Atentamente:',
            ['size' => 11],
            ['spaceAfter' => Converter::pointToTwip(11)]
        );

        // Get contact information from quotation

        $contactName = $quotation->users_id
            ? $quotation->user->name . ' ' . $quotation->user->surname
            : 'Aidee Callisaya.';

        $contactPosition = $quotation->users_id
            ? 'Responsable ' . match ($quotation->user->role->description) {
                'operator' => 'Operador.',
                'commercial' => 'Comercial.',
                'admin' => 'Logística y Comex.',
                default => $quotation->user->role->name,
            }
            : 'Responsable Comercial';

        $section->addText(
            $contactName,
            ['size' => 11]
        );
        $section->addText(
            $contactPosition,
            [
                'size' => 11,
                'bold' => true
            ]
        );

        // Create filename using quotation reference
        $cleanRef = str_replace('/', '_', $quotationRef);
        $filename = 'cotizacion_' . $cleanRef . '.docx';

        $tempFile = tempnam(sys_get_temp_dir(), 'PHPWord');
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempFile);

        // Descargar el archivo
        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }

    private function getClientData($nit)
    {
        $client = Customer::where('NIT', $nit)->firstOrFail();

        return [
            'nit' => $client->NIT,
            'name' => $client->name,
            'email' => $client->email,
            'phone' => $client->phone,
            'address' => $client->address
        ];
    }

    private function getProductsData($products)
    {
        $processedProducts = [];

        foreach ($products as $product) {
            // Suponiendo que estos modelos existen y tienen las relaciones correctas
            $origin = City::with('country')->findOrFail($product->origin_id);
            $destination = City::with('country')->findOrFail($product->destination_id);
            $incoterm = Incoterm::findOrFail($product->incoterm_id);

            $quantityDetails = [
                'value' => $product->quantity,
                'unit' => '' // Valor por defecto para la unidad
            ];

            if (!$product->is_container) {
                $quantityDescription = QuantityDescription::findOrFail($product->quantity_description_id);
                $quantityDetails['unit'] = $quantityDescription->name;
            }

            $processedProducts[] = [
                'name' => $product->name,
                'origin' => [
                    'city' => $origin->name,
                    'country' => $origin->country->name
                ],
                'destination' => [
                    'city' => $destination->name,
                    'country' => $destination->country->name
                ],
                'weight' => $product->weight,
                'incoterm' => $incoterm->code,
                'quantity' => $quantityDetails,
                'volume' => [
                    'value' => $product->volume,
                    'unit' => $product->volume_unit
                ]
            ];
        }

        return $processedProducts;
    }

    private function getServicesData($quotationServices)
    {
        $included = [];
        $excluded = [];

        foreach ($quotationServices as $quotationService) {
            $service = $quotationService->service;

            if ($quotationService->included) {
                $included[] = $service->name;
            } else {
                $excluded[] = $service->name;
            }
        }

        return [
            'included' => $included,
            'excluded' => $excluded
        ];
    }

    private function getCostsData($costDetails)
    {
        $processedCosts = [];

        foreach ($costDetails as $costDetail) {
            $cost = $costDetail->cost;

            $processedCosts[] = [
                'name' => $cost->name,
                'description' => $cost->description ?? '',
                'amount' => $costDetail->amount,
                'currency' => $costDetail->currency,
                'amount_parallel' => $costDetail->amount_parallel ?? null,
            ];
        }

        return $processedCosts;
    }

    public function generateExcel(Request $request)
    {
        $validated = $request->validate([
            'quotation_id' => 'required|integer',
        ]);

        $quotation = Quotation::with('user.role')->findOrFail($validated['quotation_id']);
        $clientData = $this->getClientData($quotation->customer_nit);
        $productsData = $this->getProductsData($quotation->products);
        $servicesData = $this->getServicesData($quotation->services);
        $costsData = $this->getCostsData($quotation->costDetails);

        // Calculate totals in both currencies
        $totalCostBS = 0;
        $totalCostForeign = 0;
        $totalCostForeignParallel = 0;

        foreach ($costsData as $item) {
            $amount = floatval(str_replace(',', '', $item['amount']));
            if ($item['currency'] == 'BS') {
                $totalCostBS += $amount;
                $totalCostForeign += $amount / $quotation->exchange_rate;
                $totalCostForeignParallel = $totalCostForeignParallel + ($item['amount_parallel'] ?? $item['amount']) / $quotation->exchange_rate;
            } else {
                $totalCostForeign += $amount;
                $totalCostForeignParallel = $totalCostForeignParallel + ($item['amount_parallel'] ?? $item['amount']);
                $totalCostBS += $amount * $quotation->exchange_rate;
            }
        }

        $totalCostBSFormatted = number_format($totalCostBS, 2, ',', '.');
        $totalCostForeignFormatted = number_format($totalCostForeign, 2, ',', '.');
        $totalCostForeignFormattedParallel = number_format($totalCostForeignParallel, 2, ',', '.');
        $deliveryDate = Carbon::parse($quotation->delivery_date)->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
        $quotationRef = $quotation->reference_number;

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Cotización');

        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial');
        $spreadsheet->getDefaultStyle()->getFont()->setSize(10);

        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_LETTER);
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
        $sheet->getPageMargins()->setTop(2.26);
        $sheet->getPageMargins()->setBottom(1.97);
        $sheet->getPageMargins()->setLeft(1);
        $sheet->getPageMargins()->setRight(1);

        // Set column widths for better readability
        $sheet->getColumnDimension('A')->setWidth(2);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(35);
        $sheet->getColumnDimension('D')->setWidth(3);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(18);

        // Define common styles
        $headerStyle = [
            'font' => [
                'bold' => true,
                'size' => 11,
                'color' => ['rgb' => '000000'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'BDD6EE'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];

        $cellStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];

        $titleStyle = [
            'font' => [
                'bold' => true,
                'size' => 12,
                'underline' => true,
            ],
        ];

        $subtitleStyle = [
            'font' => [
                'bold' => true,
                'size' => 11,
            ],
        ];

        $yellowHighlight = [
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFF00'],
            ],
        ];

        $currentRow = 3;

        $sheet->setCellValue("B{$currentRow}", $deliveryDate);
        $sheet->getStyle("B{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $currentRow += 2;

        $sheet->setCellValue("B{$currentRow}", "Señores");
        $currentRow++;

        $sheet->setCellValue("B{$currentRow}", $clientData['name']);
        $sheet->getStyle("B{$currentRow}")->applyFromArray($titleStyle);
        $currentRow++;

        $sheet->setCellValue("B{$currentRow}", "Presente. -");
        $currentRow += 2;

        // Reference number with improved styling
        $sheet->setCellValue("B{$currentRow}", "REF: COTIZACIÓN " . $quotationRef);
        $sheet->getStyle("B{$currentRow}")->applyFromArray($titleStyle);
        $sheet->getStyle("B{$currentRow}:F{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E8F3FD');
        $sheet->getStyle("B{$currentRow}:F{$currentRow}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
        $sheet->mergeCells("B{$currentRow}:F{$currentRow}");
        $currentRow += 2;

        // Introduction text with better formatting
        $sheet->setCellValue("B{$currentRow}", "Estimado cliente, por medio la presente tenemos el agrado de enviarle nuestra cotización de acuerdo con su requerimiento e información proporcionada.");
        $sheet->getStyle("B{$currentRow}:F{$currentRow}")->getAlignment()->setWrapText(true);
        $sheet->mergeCells("B{$currentRow}:F{$currentRow}");
        $currentRow += 2;

        // Product information tables with enhanced styling
        foreach ($productsData as $index => $product) {
            // Create a product header with background color
            $sheet->setCellValue("B{$currentRow}", "INFORMACIÓN DEL PRODUCTO " . ($index + 1));
            $sheet->getStyle("B{$currentRow}:F{$currentRow}")->applyFromArray($subtitleStyle);
            $sheet->getStyle("B{$currentRow}:F{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DCE6F1');
            $sheet->getStyle("B{$currentRow}:F{$currentRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->mergeCells("B{$currentRow}:F{$currentRow}");
            $sheet->getStyle("B{$currentRow}:F{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $currentRow++;

            // Primera fila - Cliente
            $sheet->setCellValue("B{$currentRow}", "CLIENTE");
            $sheet->getStyle("B{$currentRow}")->applyFromArray($headerStyle);

            $sheet->setCellValue("C{$currentRow}", $clientData['name']);
            $sheet->getStyle("C{$currentRow}")->applyFromArray($cellStyle);
            $sheet->getStyle("C{$currentRow}")->getFont()->setBold(true);
            $currentRow++;

            // Segunda fila - Origen y Cantidad
            $sheet->setCellValue("B{$currentRow}", "ORIGEN");
            $sheet->getStyle("B{$currentRow}")->applyFromArray($headerStyle);

            $sheet->setCellValue("C{$currentRow}", $product['origin']['city'] . ', ' . $product['origin']['country']);
            $sheet->getStyle("C{$currentRow}")->applyFromArray($cellStyle);

            $sheet->setCellValue("E{$currentRow}", "CANTIDAD");
            $sheet->getStyle("E{$currentRow}")->applyFromArray($headerStyle);

            $sheet->setCellValue("F{$currentRow}", $product['quantity']['value'] . ' ' .  $product['quantity']['unit']);
            $sheet->getStyle("F{$currentRow}")->applyFromArray($cellStyle);
            $currentRow++;

            // Tercera fila - Destino y Peso
            $sheet->setCellValue("B{$currentRow}", "DESTINO");
            $sheet->getStyle("B{$currentRow}")->applyFromArray($headerStyle);

            $sheet->setCellValue("C{$currentRow}", $product['destination']['city'] . ', ' . $product['destination']['country']);
            $sheet->getStyle("C{$currentRow}")->applyFromArray($cellStyle);

            $sheet->setCellValue("E{$currentRow}", "PESO");
            $sheet->getStyle("E{$currentRow}")->applyFromArray($headerStyle);

            $sheet->setCellValue("F{$currentRow}", $product['weight'] . " KG");
            $sheet->getStyle("F{$currentRow}")->applyFromArray($cellStyle);
            $currentRow++;

            // Cuarta fila - Incoterm y Volumen
            $sheet->setCellValue("B{$currentRow}", "INCOTERM");
            $sheet->getStyle("B{$currentRow}")->applyFromArray($headerStyle);

            $sheet->setCellValue("C{$currentRow}", $product['incoterm']);
            $sheet->getStyle("C{$currentRow}")->applyFromArray($cellStyle);

            $volumeLabel = $product['volume']['unit'] == 'm3' ? 'M3' : 'KG/VOL';
            $sheet->setCellValue("E{$currentRow}", $volumeLabel);
            $sheet->getStyle("E{$currentRow}")->applyFromArray($headerStyle);

            $volumeValue = $product['volume']['value'] . " " . $volumeLabel;
            $sheet->setCellValue("F{$currentRow}", $volumeValue);
            $sheet->getStyle("F{$currentRow}")->applyFromArray($cellStyle);
            $currentRow++;

            // Add space between products if there's more than one
            if ($index < count($productsData) - 1) {
                $currentRow += 2;
            }
        }

        $currentRow += 2;

        // Transition text with improved styling
        $sheet->setCellValue("B{$currentRow}", "Para el requerimiento de transporte y logística los costos se encuentran líneas abajo");
        $sheet->getStyle("B{$currentRow}:F{$currentRow}")->getAlignment()->setWrapText(true);
        $sheet->getStyle("B{$currentRow}:F{$currentRow}")->getFont()->setItalic(true);
        $sheet->mergeCells("B{$currentRow}:F{$currentRow}");
        $currentRow += 2;

        if (!$quotation->is_parallel) {
            // Payment option 1 - BS payment
            $sheet->setCellValue("B{$currentRow}", "OPCIÓN 1) PAGO EN EFECTIVO EN BS DE BOLIVIA");
            $sheet->getStyle("B{$currentRow}:F{$currentRow}")->applyFromArray($subtitleStyle);
            $sheet->getStyle("B{$currentRow}:F{$currentRow}")->applyFromArray($yellowHighlight);
            $sheet->getStyle("B{$currentRow}:F{$currentRow}")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
            $sheet->mergeCells("B{$currentRow}:F{$currentRow}");
            $currentRow += 2;

            // Table header
            $sheet->setCellValue("B{$currentRow}", "CONCEPTO");
            $sheet->getStyle("B{$currentRow}:D{$currentRow}")->applyFromArray($headerStyle);
            $sheet->mergeCells("B{$currentRow}:D{$currentRow}");

            $sheet->setCellValue("E{$currentRow}", "MONTO " . $quotation->currency);
            $sheet->getStyle("E{$currentRow}:F{$currentRow}")->applyFromArray($headerStyle);
            $sheet->getStyle("E{$currentRow}:F{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->mergeCells("E{$currentRow}:F{$currentRow}");
            $currentRow++;

            // Cost rows in foreign currency
            foreach ($costsData as $cost) {
                $amount = floatval(str_replace(',', '', $cost['amount']));
                $amountForeign = $cost['currency'] == 'BS' ? $amount / $quotation->exchange_rate : $amount;
                $amountFormatted = number_format($amountForeign, 2, ',', '.');

                $sheet->setCellValue("B{$currentRow}", $cost['name']);
                $sheet->getStyle("B{$currentRow}:D{$currentRow}")->applyFromArray($cellStyle);
                $sheet->mergeCells("B{$currentRow}:D{$currentRow}");
                $sheet->setCellValue("E{$currentRow}", $amountFormatted);
                $sheet->getStyle("E{$currentRow}:F{$currentRow}")->applyFromArray($cellStyle);
                $sheet->getStyle("E{$currentRow}:F{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("E{$currentRow}:F{$currentRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->mergeCells("E{$currentRow}:F{$currentRow}");
                $currentRow++;
            }

            // Total in foreign currency with highlighted styling
            $sheet->setCellValue("B{$currentRow}", "TOTAL");
            $sheet->getStyle("B{$currentRow}:D{$currentRow}")->applyFromArray([
                'font' => ['bold' => true],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);
            $sheet->mergeCells("B{$currentRow}:D{$currentRow}");
            $sheet->setCellValue("E{$currentRow}", $totalCostForeignFormatted);
            $sheet->getStyle("E{$currentRow}:F{$currentRow}")->applyFromArray([
                'font' => ['bold' => true],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
            ]);
            $sheet->getStyle("E{$currentRow}:F{$currentRow}")->applyFromArray($yellowHighlight);
            $sheet->getStyle("E{$currentRow}:F{$currentRow}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->mergeCells("E{$currentRow}:F{$currentRow}");
            $currentRow += 2;

            // Exchange rate note
            $sheet->setCellValue("B{$currentRow}", "** De acuerdo con el TC paralelo vigente.");
            $sheet->getStyle("B{$currentRow}")->getFont()->setBold(true);
            $currentRow += 2;
        } else {
            // Payment option 1 - BS payment with parallel rate
            $sheet->setCellValue("B{$currentRow}", "OPCIÓN 1) PAGO EN BOLIVIA A UN TC DE " . $quotation->exchange_rate);
            $sheet->getStyle("B{$currentRow}:F{$currentRow}")->applyFromArray($subtitleStyle);
            $sheet->getStyle("B{$currentRow}:F{$currentRow}")->applyFromArray($yellowHighlight);
            $sheet->getStyle("B{$currentRow}:F{$currentRow}")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
            $sheet->mergeCells("B{$currentRow}:F{$currentRow}");
            $currentRow += 2;

            // Table header
            $sheet->setCellValue("B{$currentRow}", "CONCEPTO");
            $sheet->getStyle("B{$currentRow}:D{$currentRow}")->applyFromArray($headerStyle);
            $sheet->mergeCells("B{$currentRow}:D{$currentRow}");

            $sheet->setCellValue("E{$currentRow}", "MONTO " . $quotation->currency);
            $sheet->getStyle("E{$currentRow}:F{$currentRow}")->applyFromArray($headerStyle);
            $sheet->getStyle("E{$currentRow}:F{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->mergeCells("E{$currentRow}:F{$currentRow}");
            $currentRow++;

            // Cost rows in foreign currency with parallel amounts
            foreach ($costsData as $cost) {
                $amount = floatval(str_replace(',', '', $cost['amount_parallel'] ?? $cost['amount']));
                $amountForeign = $cost['currency'] == 'BS' ? $amount / $quotation->exchange_rate : $amount;
                $amountFormatted = number_format($amountForeign, 2, ',', '.');

                $sheet->setCellValue("B{$currentRow}", $cost['name']);
                $sheet->getStyle("B{$currentRow}:D{$currentRow}")->applyFromArray($cellStyle);
                $sheet->mergeCells("B{$currentRow}:D{$currentRow}");
                $sheet->setCellValue("E{$currentRow}", $amountFormatted);
                $sheet->getStyle("E{$currentRow}:F{$currentRow}")->applyFromArray($cellStyle);
                $sheet->getStyle("E{$currentRow}:F{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("E{$currentRow}:F{$currentRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->mergeCells("E{$currentRow}:F{$currentRow}");
                $currentRow++;
            }

            // Total in foreign currency with parallel amounts
            $sheet->setCellValue("B{$currentRow}", "TOTAL");
            $sheet->getStyle("B{$currentRow}:D{$currentRow}")->applyFromArray([
                'font' => ['bold' => true],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);
            $sheet->mergeCells("B{$currentRow}:D{$currentRow}");
            $sheet->setCellValue("E{$currentRow}", $totalCostForeignFormattedParallel);
            $sheet->getStyle("E{$currentRow}:F{$currentRow}")->applyFromArray([
                'font' => ['bold' => true],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
            ]);
            $sheet->getStyle("E{$currentRow}:F{$currentRow}")->applyFromArray($yellowHighlight);
            $sheet->getStyle("E{$currentRow}:F{$currentRow}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->mergeCells("E{$currentRow}:F{$currentRow}");
            $currentRow += 2;

            // Payment option 2 - USD payment
            $sheet->setCellValue("B{$currentRow}", "OPCIÓN 2) PAGO EFECTIVO EN USD O DE ACUERDO CON EL TC PARALELO");
            $sheet->getStyle("B{$currentRow}:F{$currentRow}")->applyFromArray($subtitleStyle);
            $sheet->getStyle("B{$currentRow}:F{$currentRow}")->applyFromArray($yellowHighlight);
            $sheet->getStyle("B{$currentRow}:F{$currentRow}")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
            $sheet->mergeCells("B{$currentRow}:F{$currentRow}");
            $currentRow += 2;

            // Table header
            $sheet->setCellValue("B{$currentRow}", "CONCEPTO");
            $sheet->getStyle("B{$currentRow}:D{$currentRow}")->applyFromArray($headerStyle);
            $sheet->mergeCells("B{$currentRow}:D{$currentRow}");

            $sheet->setCellValue("E{$currentRow}", "MONTO " . $quotation->currency);
            $sheet->getStyle("E{$currentRow}:F{$currentRow}")->applyFromArray($headerStyle);
            $sheet->getStyle("E{$currentRow}:F{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->mergeCells("E{$currentRow}:F{$currentRow}");
            $currentRow++;

            // Cost rows in foreign currency
            foreach ($costsData as $cost) {
                $amount = floatval(str_replace(',', '', $cost['amount']));
                $amountForeign = $cost['currency'] == 'BS' ? $amount / $quotation->exchange_rate : $amount;
                $amountFormatted = number_format($amountForeign, 2, ',', '.');

                $sheet->setCellValue("B{$currentRow}", $cost['name']);
                $sheet->getStyle("B{$currentRow}:D{$currentRow}")->applyFromArray($cellStyle);
                $sheet->mergeCells("B{$currentRow}:D{$currentRow}");
                $sheet->setCellValue("E{$currentRow}", $amountFormatted);
                $sheet->getStyle("E{$currentRow}:F{$currentRow}")->applyFromArray($cellStyle);
                $sheet->getStyle("E{$currentRow}:F{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("E{$currentRow}:F{$currentRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->mergeCells("E{$currentRow}:F{$currentRow}");
                $currentRow++;
            }

            // Total in foreign currency
            $sheet->setCellValue("B{$currentRow}", "TOTAL");
            $sheet->getStyle("B{$currentRow}:D{$currentRow}")->applyFromArray([
                'font' => ['bold' => true],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);
            $sheet->mergeCells("B{$currentRow}:D{$currentRow}");
            $sheet->setCellValue("E{$currentRow}", $totalCostForeignFormatted);
            $sheet->getStyle("E{$currentRow}:F{$currentRow}")->applyFromArray([
                'font' => ['bold' => true],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
            ]);
            $sheet->getStyle("E{$currentRow}:F{$currentRow}")->applyFromArray($yellowHighlight);
            $sheet->getStyle("E{$currentRow}:F{$currentRow}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->mergeCells("E{$currentRow}:F{$currentRow}");
            $currentRow += 2;

            // Juncture note if is_parallel is true
            $juncture = $quotation->juncture ?? 113;
            $sheet->setCellValue("B{$currentRow}", "**Debido a la coyuntura actual, en la presente cotización se está aplicando el costo de transferencia del " . $juncture . "% sobre los recargos generados en origen, de acuerdo con la comisión que cobra nuestro banco actualmente, si esta llega a variar, considerar la modificación de ese monto de acuerdo con la tarifa vigente.");
            $sheet->getStyle("B{$currentRow}:F{$currentRow}")->getAlignment()->setWrapText(true);
            $sheet->getStyle("B{$currentRow}:F{$currentRow}")->applyFromArray($yellowHighlight);
            $sheet->mergeCells("B{$currentRow}:F{$currentRow}");
            $currentRow += 2;
        }

        if (count($servicesData['included']) > 0) {
            $sheet->setCellValue("B{$currentRow}", "El servicio incluye:");
            $sheet->getStyle("B{$currentRow}")->applyFromArray($subtitleStyle);
            $sheet->getStyle("B{$currentRow}:F{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DCE6F1');
            $sheet->getStyle("B{$currentRow}:F{$currentRow}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
            $sheet->mergeCells("B{$currentRow}:F{$currentRow}");
            $currentRow++;
        }

        foreach ($servicesData['included'] as $service) {
            $sheet->setCellValue("B{$currentRow}", "✓");
            $sheet->getStyle("B{$currentRow}")->getFont()->setBold(true);
            $sheet->getStyle("B{$currentRow}")->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKGREEN));

            $sheet->setCellValue("C{$currentRow}", $service);
            $sheet->getStyle("C{$currentRow}:F{$currentRow}")->getAlignment()->setWrapText(true);
            $sheet->mergeCells("C{$currentRow}:F{$currentRow}");
            $currentRow++;
        }

        $currentRow++;

        if (count($servicesData['excluded']) > 0) {
            $sheet->setCellValue("B{$currentRow}", "El servicio no incluye:");
            $sheet->getStyle("B{$currentRow}")->applyFromArray($subtitleStyle);
            $sheet->getStyle("B{$currentRow}:F{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DCE6F1');
            $sheet->getStyle("B{$currentRow}:F{$currentRow}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
            $sheet->mergeCells("B{$currentRow}:F{$currentRow}");
            $currentRow++;
        }

        foreach ($servicesData['excluded'] as $service) {
            $sheet->setCellValue("B{$currentRow}", "✘");
            $sheet->getStyle("B{$currentRow}")->getFont()->setBold(true);
            $sheet->getStyle("B{$currentRow}")->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKRED));

            $sheet->setCellValue("C{$currentRow}", $service);
            $sheet->getStyle("C{$currentRow}:F{$currentRow}")->getAlignment()->setWrapText(true);
            $sheet->mergeCells("C{$currentRow}:F{$currentRow}");
            $currentRow++;
        }

        $currentRow += 2;

        $infoStartRow = $currentRow;

        $sheet->setCellValue("B{$currentRow}", "INFORMACIÓN ADICIONAL");
        $sheet->getStyle("B{$currentRow}:F{$currentRow}")->applyFromArray($subtitleStyle);
        $sheet->getStyle("B{$currentRow}:F{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DCE6F1');
        $sheet->getStyle("B{$currentRow}:F{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->mergeCells("B{$currentRow}:F{$currentRow}");
        $currentRow++;

        if ($quotation->insurance) {
            $sheet->setCellValue("B{$currentRow}", "Seguro:");
            $sheet->getStyle("B{$currentRow}")->getFont()->setBold(true);

            $sheet->setCellValue("C{$currentRow}", $quotation->insurance);
            $sheet->getStyle("C{$currentRow}:F{$currentRow}")->getAlignment()->setWrapText(true);
            $sheet->mergeCells("C{$currentRow}:F{$currentRow}");
        } else {
            $sheet->setCellValue("B{$currentRow}", "Seguro:");
            $sheet->getStyle("B{$currentRow}")->getFont()->setBold(true);
            $sheet->setCellValue("C{$currentRow}", "Se recomienda tener una póliza de seguro para el embarque, ofrecemos la misma de manera adicional considerando el 0.35% sobre el valor declarado, con un min de 30 usd, previa autorización por la compañía de seguros.");
            $sheet->getStyle("C{$currentRow}:F{$currentRow}")->getAlignment()->setWrapText(true);
            $sheet->mergeCells("C{$currentRow}:F{$currentRow}");
        }
        $currentRow++;

        if ($quotation->payment_method) {
            $sheet->setCellValue("B{$currentRow}", "Forma de pago:");
            $sheet->getStyle("B{$currentRow}")->getFont()->setBold(true);

            $sheet->setCellValue("C{$currentRow}", $quotation->payment_method);
            $sheet->getStyle("C{$currentRow}:F{$currentRow}")->getAlignment()->setWrapText(true);
            $sheet->mergeCells("C{$currentRow}:F{$currentRow}");
        } else {
            $sheet->setCellValue("B{$currentRow}", "Forma de pago:");
            $sheet->getStyle("B{$currentRow}")->getFont()->setBold(true);

            $sheet->setCellValue("C{$currentRow}", "Una vez se confirme el arribo del embarque a puerto de destino.");
            $sheet->getStyle("C{$currentRow}:F{$currentRow}")->getAlignment()->setWrapText(true);
            $sheet->mergeCells("C{$currentRow}:F{$currentRow}");
        }
        $currentRow++;

        if ($quotation->validity) {
            $sheet->setCellValue("B{$currentRow}", "Validez:");
            $sheet->getStyle("B{$currentRow}")->getFont()->setBold(true);

            $sheet->setCellValue("C{$currentRow}", $quotation->validity);
            $sheet->getStyle("C{$currentRow}:F{$currentRow}")->getAlignment()->setWrapText(true);
            $sheet->mergeCells("C{$currentRow}:F{$currentRow}");
        } else {
            $sheet->setCellValue("B{$currentRow}", "Validez:");
            $sheet->getStyle("B{$currentRow}")->getFont()->setBold(true);

            $sheet->setCellValue("C{$currentRow}", "Los fletes son válidos hasta 10 días, posterior a ese tiempo, validar si los costos aún están vigentes.");
            $sheet->getStyle("C{$currentRow}:F{$currentRow}")->getAlignment()->setWrapText(true);
            $sheet->mergeCells("C{$currentRow}:F{$currentRow}");
        }
        $currentRow++;

        if ($quotation->observations) {
            $sheet->setCellValue("B{$currentRow}", "Observaciones:");
            $sheet->getStyle("B{$currentRow}")->getFont()->setBold(true);

            $sheet->setCellValue("C{$currentRow}", $quotation->observations);
            $sheet->getStyle("C{$currentRow}:F{$currentRow}")->getAlignment()->setWrapText(true);
            $sheet->mergeCells("C{$currentRow}:F{$currentRow}");
        } else {
            $sheet->setCellValue("B{$currentRow}", "Observaciones:");
            $sheet->getStyle("B{$currentRow}")->getFont()->setBold(true);

            $sheet->setCellValue("C{$currentRow}", "Se debe considerar como un tiempo de tránsito 48 a 50 días hasta puerto.");
            $sheet->getStyle("C{$currentRow}:F{$currentRow}")->getAlignment()->setWrapText(true);
            $sheet->mergeCells("C{$currentRow}:F{$currentRow}");
        }
        $sheet->getStyle("B{$infoStartRow}:F{$currentRow}")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        $currentRow += 2;

        // Contact information
        $contactName = $quotation->users_id
            ? $quotation->user->name . ' ' . $quotation->user->surname
            : 'Aidee Callisaya.';

        $contactPosition = $quotation->users_id
            ? 'Responsable ' . match ($quotation->user->role->description) {
                'operator' => 'Operador.',
                'commercial' => 'Comercial.',
                'admin' => 'Logística y Comex.',
                default => $quotation->user->role->name,
            }
            : 'Responsable Comercial';

        $sheet->setCellValue("B{$currentRow}", "Atentamente:");
        $currentRow++;

        $sheet->setCellValue("B{$currentRow}", $contactName);
        $currentRow++;

        $sheet->setCellValue("B{$currentRow}", $contactPosition);
        $sheet->getStyle("B{$currentRow}")->getFont()->setBold(true);
        $currentRow++;

        $cleanRef = str_replace('/', '_', $quotationRef);
        $filename = 'cotizacion_' . $cleanRef . '.xlsx';

        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'Excel');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }
}
