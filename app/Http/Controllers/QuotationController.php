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
use App\Models\Continent;
use App\Models\Quotation;
use App\Models\CostDetail;
use App\Models\ExchangeRate;
use Illuminate\Http\Request;
use App\Models\productDetail;
use PhpOffice\PhpWord\PhpWord;
use App\Models\QuotationService;
use PhpOffice\PhpWord\IOFactory;
use Illuminate\Support\Facades\DB;
use App\Models\QuantityDescription;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpWord\SimpleType\JcTable;
use PhpOffice\PhpWord\SimpleType\TblWidth;

class QuotationController extends Controller
{
    //

    public function index()
    {
        if (Auth::user()->role_id === 1) {
            $quotations = Quotation::with(['customer', 'user'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        } else {
            $quotations = Quotation::with(['customer'])
                ->where('users_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->paginate(10);
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
                'message' => 'Cliente creado exitosamente',
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
        $validatedData = $request->validate([
            'reference_number' => 'required|integer',
            'currency' => 'required|string|max:3',
            'exchange_rate' => 'required|numeric',
            'NIT' => 'required|exists:customers,NIT',
            'products' => 'nullable|array',
            'products.*.origin_id' => 'required_with:products',
            'products.*.destination_id' => 'required_with:products',
            'products.*.incoterm_id' => 'required_with:products',
            // 'products.*.quantity' => 'required_with:products|numeric',
            'products.*.quantity' => 'required_with:products|string',
            'products.*.quantity_description_id' => 'required_with:products',
            'products.*.weight' => 'nullable|numeric',
            'products.*.volume' => 'nullable|numeric',
            'products.*.volume_unit' => 'nullable|string|max:10',
            'products.*.description' => 'nullable|string',
            'costs' => 'nullable|array',
            'services' => 'nullable|array',
        ]);
        
        DB::beginTransaction();

        try {
            $quotation = new Quotation();
            $quotation->delivery_date = Carbon::now();
            $quotation->reference_number = $validatedData['reference_number'];
            $quotation->currency = $validatedData['currency'];
            $quotation->exchange_rate = $validatedData['exchange_rate'];
            $quotation->amount = 0;
            $quotation->customer_nit = $validatedData['NIT'];
            $quotation->users_id = Auth::id();
            $quotation->status = 'pending';
            $quotation->save();
            if ($request->has('products')) {
                foreach ($request->products as $product) {
                    $productDetail = new Product();
                    $productDetail->quotation_id = $quotation->id;
                    $productDetail->origin_id = $product['origin_id'];
                    $productDetail->destination_id = $product['destination_id'];
                    $productDetail->incoterm_id = $product['incoterm_id'];
                    $productDetail->quantity = $product['quantity'];
                    $productDetail->quantity_description_id = $product['quantity_description_id'];
                    $productDetail->weight = $product['weight'];
                    $productDetail->volume = $product['volume'];
                    $productDetail->volume_unit = $product['volume_unit'];
                    $productDetail->description = $product['description'];
                    $productDetail->save();
                }
            }
            $costTotal = 0;
            // Process cost details for this quotation detail
            if ($request->has('costs')) {
                foreach ($request->costs as $cost) {
                    $costDetail = new CostDetail();
                    $costDetail->quotation_id = $quotation->id;
                    if (isset($cost['enabled']) && $cost['enabled']) {
                        $costDetail->cost_id = $cost['cost_id'];
                        $costDetail->concept = $cost['concept'];
                        $costDetail->amount = $cost['amount'];
                        $costTotal += $cost['amount'];
                        $costDetail->currency = $quotation->currency;
                        $costDetail->save();
                    }
                }
            }
            // Process services
            if ($request->has('services')) {
                foreach ($request->services as $key => $service) {
                    $quotationService = new QuotationService();
                    $quotationService->quotation_id = $quotation->id;
                    $quotationService->service_id = $key;
                    $quotationService->included = $service == 'include' ? true : false;
                    $quotationService->save();
                }
            }
            $quotation->amount = $costTotal;
            $quotation->save();

            DB::commit();

            return redirect()->route('quotations.show', $quotation->id)
                ->with('success', 'Cotización creada satisfactoriamente.');
        } catch (\Exception $e) {
            dd(Customer::find($request->NIT)->name);
            DB::rollBack();
            return back()
            ->withInput()
            ->with('error', 'Error creating quotation: ' . $e->getMessage());
        }
    }
    public function show($id)
    {
        $quotation = Quotation::with([
            'customer',
            'user',
            'products.origin',
            'products.destination',
            'products.incoterm',
            'products.costDetails',
            'products.quantityDescription',
            'services.service'
        ])->findOrFail($id);

        return view('quotations.show', compact('quotation'));
    }

    public function edit($id)
    {
        $quotation = Quotation::with([
            'customer',
            'user',
            'products.origin',
            'products.destination',
            'products.incoterm',
            'products.costDetails',
            'products.quantityDescription',
            'services.service'
        ])->findOrFail($id);

        $incoterms = Incoterm::where('is_active', 1)->get();
        $services = Service::where('is_active', 1)->get();
        $countries = Country::whereNull('deleted_at')->get();
        $costs = Cost::where('is_active', 1)->get();
        $exchangeRates = ExchangeRate::where('active', 1)->get();
        $customers = Customer::where('active', 1)->get();
        $QuantityDescriptions = QuantityDescription::where('is_active', 1)->get();
        $continents = Continent::whereNull('deleted_at')->get();
        $cities = City::whereNull('deleted_at')->get();

        $originCities = [];
        $destinationCities = [];

        foreach ($quotation->details as $detail) {
            if ($detail->origin && $detail->origin->country) {
                $originCities[$detail->origin->country->id] = City::where('country_id', $detail->origin->country->id)
                    ->whereNull('deleted_at')
                    ->get();
            }

            if ($detail->destination && $detail->destination->country) {
                $destinationCities[$detail->destination->country->id] = City::where('country_id', $detail->destination->country->id)
                    ->whereNull('deleted_at')
                    ->get();
            }
        }

        return view('quotations.edit', compact(
            'quotation',
            'incoterms',
            'services',
            'countries',
            'costs',
            'exchangeRates',
            'customers',
            'originCities',
            'destinationCities',
            'QuantityDescriptions',
            'cities',
        ));
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            //'delivery_date' => 'required|date',
            'reference_number' => 'required|integer',
            'currency' => 'required|string|max:3',
            'exchange_rate' => 'required|numeric',
            'NIT' => 'required|exists:customers,NIT',
            'products' => 'nullable|array',
            'products.*.origin_id' => 'required_with:products',
            'products.*.destination_id' => 'required_with:products',
            'products.*.incoterm_id' => 'required_with:products',
            'products.*.quantity' => 'required_with:products|numeric',
            'products.*.quantity_description_id' => 'required_with:products',
            'products.*.weight' => 'nullable|numeric',
            'products.*.volume' => 'nullable|numeric',
            'products.*.volume_unit' => 'nullable|string|max:10',
            'products.*.description' => 'nullable|string',
            'products.*.costs' => 'nullable|array',
            'products.*.costs.*.cost_id' => 'required_with:products.*.costs',
            'products.*.costs.*.concept' => 'nullable|string',
            'products.*.costs.*.amount' => 'required_with:products.*.costs|numeric',
            'products.*.costs.*.currency' => 'nullable|string|max:3',
            'services' => 'nullable|array',
        ]);

        DB::beginTransaction();

        try {
            $quotation = Quotation::findOrFail($id);
            $quotation->delivery_date = Carbon::now();
            $quotation->reference_number = $validatedData['reference_number'];
            $quotation->currency = $validatedData['currency'];
            $quotation->exchange_rate = $validatedData['exchange_rate'];
            $quotation->customer_nit = $validatedData['NIT'];
            $quotation->save();

            // Eliminar productos y costos existentes para reemplazarlos
            $quotation->products()->each(function ($product) {
                $product->costDetails()->delete();
                $product->delete();
            });

            // Eliminar servicios existentes
            $quotation->services()->delete();

            // Agregar nuevos productos y costos
            if ($request->has('products')) {
                foreach ($request->products as $product) {
                    $productDetail = new Product();
                    $productDetail->quotation_id = $quotation->id;
                    $productDetail->origin_id = $product['origin_id'];
                    $productDetail->destination_id = $product['destination_id'];
                    $productDetail->incoterm_id = $product['incoterm_id'];
                    $productDetail->quantity = $product['quantity'];
                    $productDetail->quantity_description_id = $product['quantity_description_id'];
                    $productDetail->weight = $product['weight'];
                    $productDetail->volume = $product['volume'];
                    $productDetail->volume_unit = $product['volume_unit'];
                    $productDetail->description = $product['description'];
                    $productDetail->amount = 0;
                    $productDetail->save();

                    if (isset($product['costs'])) {
                        foreach ($product['costs'] as $cost) {
                            $costDetail = new CostDetail();
                            $costDetail->quotation_detail_id = $productDetail->id;
                            $costDetail->cost_id = $cost['cost_id'];
                            $costDetail->concept = $cost['concept'];
                            $costDetail->amount = $cost['amount'];
                            $productDetail->amount += $cost['amount'];
                            $costDetail->currency = $cost['currency'] ?? 'USD';
                            $costDetail->save();
                        }
                        $productDetail->save();
                    }
                }
            }

            // Agregar nuevos servicios
            if ($request->has('services')) {
                foreach ($request->services as $service) {
                    $quotationService = new QuotationService();
                    $quotationService->quotation_id = $quotation->id;
                    $quotationService->service_id = $service['id'];
                    $quotationService->included = $service['included'] ?? false;
                    $quotationService->save();
                }
            }

            // Actualizar monto total
            $totalAmount = Product::where('quotation_id', $quotation->id)->sum('amount');
            $quotation->amount = $totalAmount;
            $quotation->save();

            DB::commit();

            return redirect()->route('quotations.show', $quotation->id)
                ->with('success', 'Cotización actualizada satisfactoriamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
            ->withInput()
            ->with('error', 'Error actualizando cotización: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected,cancelled'
        ]);

        $quotation = Quotation::findOrFail($id);
        $quotation->status = $request->status;
        $quotation->save();

        return redirect()->route('quotations.show', $quotation->id)
            ->with('success', 'Estado de la cotización actualizado satisfactoriamente.');
    }


    public function destroy($id)
    {
        $user = Auth::user();
        $isAdmin = $user->role_id === 1;

        $quotation = Quotation::findOrFail($id);

        // Check access permissions
        if (!$isAdmin && $quotation->users_id !== $user->id) {
            return abort(403, 'Acción no autorizada.');
        }

        DB::beginTransaction();

        try {
            $quotation->delete();

            DB::commit();

            return redirect()->route('quotations.index')
                ->with('success', 'Cotización eliminada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
    public function generarCotizacion(Request $request)
    {

        // // Validar los datos del request
        // $validated = $request->validate([
        //     'NIT' => 'required|string',
        //     'currency' => 'required|string',
        //     'exchange_rate' => 'required|numeric',
        //     'reference_number' => 'required|string',
        //     'products' => 'required|array',
        //     'services' => 'required|array',
        //     'logistic_costs' => 'required|array'
        // ]);

        $validated =  [
            'NIT' => '1419568',
            'currency' => 'USD',
            'exchange_rate' => '6.96',
            'reference_number' => '1254125',
            'delivery_date' => '2023-10-01',
            'products' => [
                1 => [
                    'product_name' => 'Product1',
                    'origin_id' => '41',
                    'destination_id' => '64',
                    'weight' => '45',
                    'incoterm_id' => '6',
                    'unit_quantity' => '1',
                    'quantity' => '40',
                    'quantity_description' => 'box',
                    'volume_value' => '55',
                    'volume_unit' => 'KG'
                ]
            ],
            'services' => [
                1 => 'include',
                3 => 'include',
                7 => 'exclude',
                9 => 'exclude',
                13 => 'include',
                17 => 'include'
            ],
            'logistic_costs' => [
                1 => [
                    'enabled' => 'on',
                    'amount' => '550',
                    'id' => '1'
                ],
                2 => [
                    'enabled' => 'on',
                    'amount' => '500',
                    'id' => '2'
                ]
            ]
        ];
        $simulatedClientData = [
            'nit' => '1419568',
            'name' => 'Lucas S.A.',
            'email' => 'cliente@simulado.com',
            'phone' => '123456789',
            'address' => 'Dirección simulada 123'
        ];
        $clientData = $simulatedClientData;
        $productsData = $this->getProductsData($validated['products']);
        $servicesData = $this->getServicesData($validated['services']);
        $costsData = $this->getCostsData($validated['logistic_costs']);

        $totalCost = array_reduce($costsData, function ($carry, $item) {
            return $carry + floatval(str_replace(',', '', $item['amount']));
        }, 0);
        $totalCostFormatted = number_format($totalCost, 2, ',', '.');
        $deliveryDate = Carbon::parse($validated['delivery_date'])->locale('es')->isoFormat('D [de] MMMM [de] YYYY');


        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Montserrat');
        $pageWidthInches = 8.52;
        $headerHeightInches = 2.26; // Altura deseada para la imagen del encabezado en pulgadas
        $footerHeightInches = 1.83; // Altura deseada para la imagen del pie de página en pulgadas

        $pageWidthPoints = $pageWidthInches * 72;
        $headerHeightPoints = $headerHeightInches * 72;
        $footerHeightPoints = $footerHeightInches * 72;

        $section = $phpWord->addSection([
            'paperSize' => 'Letter',
            //'headerHeight' => Converter::inchToTwip(1.95), // Altura del header
            //'footerHeight' => Converter::inchToTwip(1.7)   // Altura del footer
            'marginTop' => Converter::inchToTwip(2.26),
            'marginBottom' => Converter::inchToTwip(1.97),
        ]);

        $header = $section->addHeader();
        $header->addImage(
            storage_path('app/templates/Herder.png'),
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
            storage_path('app/templates/Footer.png'),
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
            'REF: COTIZACION 016/25',
            ['size' => 11, 'underline' => 'single', 'bold' => true, 'allCaps' => true],
            ['spaceAfter' => Converter::pointToTwip(11)]
        );
        $section->addText(
            'Estimado cliente, por medio la presente tenemos el agrado de enviarle nuestra cotización de acuerdo con su requerimiento e información proporcionada.',
            ['size' => 11],
            ['spaceAfter' => Converter::pointToTwip(11)]
        );

        // Crear tabla de datos del envío
        $tableStyle = [
            'borderColor' => '000000',
            'cellMarginLeft' => 50,
            'cellMarginRight' => 50, // Elimina todos los márgenes internos de las celdas
            'layout' => \PhpOffice\PhpWord\Style\Table::LAYOUT_FIXED
        ];
        $phpWord->addTableStyle('shipmentTable', $tableStyle);
        $table = $section->addTable('shipmentTable');
        $compactParagraphStyle = [
            'spaceBefore' => 0,
            'spaceAfter' => 0,
            'spacing' => 0, // Interlineado 1
            'lineHeight' => 1.0
        ];
        $table->addRow(Converter::cmToTwip(1.7));
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
        $table->addCell(Converter::cmToTwip(0.5), [
            'valign' => 'center',
        ]);

        // Segunda fila
        $table->addRow(Converter::cmToTwip(1.7));
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
        ])->addText($productsData[0]['origin']['country'], [
            'size' => 11
        ], $compactParagraphStyle);
        $table->addCell(Converter::cmToTwip(0.5), [
            'valign' => 'center',
        ]);
        $table->addCell(Converter::cmToTwip(2), [
            'valign' => 'bottom',
            'bgColor' => 'bdd6ee',
            'borderSize' => 1,
        ])->addText('CANTIDAD', [
            'bold' => true,
            'size' => 11
        ], $compactParagraphStyle);
        $table->addCell(Converter::cmToTwip(3), [
            'valign' => 'bottom',
            'borderSize' => 1,
        ])->addText($productsData[0]['quantity']['value'], [
            'size' => 11
        ], $compactParagraphStyle);

        // Tercera fila
        $table->addRow(Converter::cmToTwip(1.7));
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
        ])->addText($productsData[0]['destination']['country'], [
            'size' => 11
        ], $compactParagraphStyle);
        // Fila vacía (0.5 cm de ancho) - RESTAURADA
        $table->addCell(Converter::cmToTwip(0.5), [
            'valign' => 'center',
        ]);
        $table->addCell(Converter::cmToTwip(2), [
            'valign' => 'bottom',
            'bgColor' => 'bdd6ee',
            'borderSize' => 1,
        ])->addText('PESO', [
            'bold' => true,
            'size' => 11
        ], $compactParagraphStyle);
        $table->addCell(Converter::cmToTwip(3), [
            'valign' => 'bottom',
            'borderSize' => 1,
        ])->addText($productsData[0]['weight'] . " " . 'KG', [
            'size' => 11
        ], $compactParagraphStyle);

        // Cuarta fila
        $table->addRow();
        $table->addCell(Converter::cmToTwip(3), [
            'valign' => 'center',
            'bgColor' => 'bdd6ee',
            'borderSize' => 1,
        ])->addText('INCOTERM', [
            'bold' => true,
            'size' => 11
        ], $compactParagraphStyle, [
            'spaceBefore' => 0,
            'spaceAfter' => 0
        ]);
        $table->addCell(Converter::cmToTwip(7), [
            'valign' => 'center',
            'borderSize' => 1,
        ])->addText($productsData[0]['incoterm'], [
            'size' => 11
        ], $compactParagraphStyle, [
            'spaceBefore' => 0,
            'spaceAfter' => 0
        ]);

        // Fila vacía (0.5 cm de ancho) - RESTAURADA
        $table->addCell(Converter::cmToTwip(0.5), [
            'valign' => 'center',
        ])->addText('', [
            'spaceBefore' => 0,
            'spaceAfter' => 0
        ]);
        $table->addCell(Converter::cmToTwip(2), [
            'valign' => 'center',
            'bgColor' => 'bdd6ee',
            'borderSize' => 1,
        ])->addText('M3', [
            'bold' => true,
            'size' => 11
        ], $compactParagraphStyle, [
            'spaceBefore' => 0,
            'spaceAfter' => 0
        ]);
        $table->addCell(Converter::cmToTwip(3), [
            'valign' => 'center',
            'borderSize' => 1,
        ])->addText($productsData[0]['volume']['value'] . " " . $productsData[0]['volume']['unit'], [
            'size' => 11
        ], $compactParagraphStyle, [
            'spaceBefore' => 0,
            'spaceAfter' => 0
        ]);

        // Texto después de la tabla
        $section->addTextBreak(1);
        $section->addText(
            'Para el requerimiento de transporte y logística los costos se encuentran líneas abajo',
            ['size' => 11],
            ['spaceAfter' => Converter::pointToTwip(11)]
        );

        // Opción de pago (en negrita)
        $section->addText(
            'OPCION 1) PAGO EN EFECTIVO EN BS DE EN BOLIVIA',
            ['bold' => true, 'size' => 11],
            ['spaceAfter' => Converter::pointToTwip(11)]
        );

        $table = $section->addTable([
            'width' => 400,
            'unit' => 'pct',
            'alignment' => JcTable::CENTER,
            'cellMargin' => 50,
        ]);
        // Primera fila de la tabla
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
            'spacing' => 0, // Interlineado 1
            'lineHeight' => 1.0,
            'align' => 'center'
        ]);
        $table->addCell(Converter::cmToTwip(3), [
            'valign' => 'center',
            'bgColor' => 'bdd6ee',
            'borderSize' => 1,
        ])->addText('MONTO USD', [
            'bold' => true,
            'size' => 11,
            'allCaps' => true
        ], [
            'spaceBefore' => 0,
            'spaceAfter' => 0,
            'spacing' => 0, // Interlineado 1
            'lineHeight' => 1.0,
            'align' => 'right'
        ]);

        // Filas de costos
        foreach ($costsData as $cost) {
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
            ])->addText($cost['amount'], [
                'size' => 11
            ], [
                'spaceBefore' => 0,
                'spaceAfter' => 0,
                'spacing' => 0,
                'lineHeight' => 1.0,
                'align' => 'right'
            ]);
        }

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
            'spacing' => 0, // Interlineado 1
            'lineHeight' => 1.0,
            'align' => 'left'
        ]);
        $table->addCell(Converter::cmToTwip(3), [
            'valign' => 'center',
            'borderSize' => 1,
        ])->addText($totalCostFormatted, [
            'size' => 11,
            'allCaps' => true
        ], [
            'spaceBefore' => 0,
            'spaceAfter' => 0,
            'spacing' => 0, // Interlineado 1
            'lineHeight' => 1.0,
            'align' => 'right'
        ]);
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
        $section->addText(
            'El servicio incluye:',
            ['size' => 11, 'bold' => true],
            ['spaceAfter' => Converter::pointToTwip(11)]
        );
        // Crear la lista con guiones
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
        // Añadir tu lista con los elementos
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
        $section->addText(
            'El servicio no incluye:',
            ['size' => 11, 'bold' => true],
            [
                'spaceAfter' => Converter::pointToTwip(11),
                'spaceBefore' => Converter::pointToTwip(11)
            ]
        );
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
        );
        // Crear el párrafo con formato mixto
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
        $paragraphStyle = array(
            'spaceAfter' => Converter::pointToTwip(11),
        );
        // Crear el párrafo con formato mixto
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
        // Crear el párrafo con formato mixto
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
        // Crear el párrafo con formato mixto
        $textrun = $section->addTextRun($paragraphStyle);
        $textrun->addText(
            'Observaciones: ',
            [
                'bold' => true,
                'size' => 11,
            ]
        );
        $textrun->addText(
            'Se debe considerar como un tiempo de tránsito 48 a 50 días hasta puerto de Iquique. ',
            [
                'size' => 11,
            ]
        );
        $section->addText(
            'Atentamente:',
            ['size' => 11],
            ['spaceAfter' => Converter::pointToTwip(11)]
        );
        $section->addText(
            'Aidee Callisaya.',
            ['size' => 11]
        );
        $section->addText(
            'Responsable Comercial',
            [
                'size' => 11,
                'bold' => true
            ]

        );
        // Guardar el documento
        $filename = 'cotizacion_016_CASART2.docx';
        $tempFile = tempnam(sys_get_temp_dir(), 'PHPWord');
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempFile);
        // Descargar el archivo
        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }

    private function getClientData($nit)
    {
        $client = Customer::where('nit', $nit)->firstOrFail();

        return [
            'nit' => $client->nit,
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
            $origin = City::with('country')->findOrFail($product['origin_id']);
            $destination = City::with('country')->findOrFail($product['destination_id']);
            $incoterm = Incoterm::findOrFail($product['incoterm_id']);
            //$quantity_descripcion = QuantityDescription::findOrFail($product['quantity_description']);

            $processedProducts[] = [
                'name' => $product['product_name'],
                'origin' => [
                    'city' => $origin->name,
                    'country' => $origin->country->name
                ],
                'destination' => [
                    'city' => $destination->name,
                    'country' => $destination->country->name
                ],
                'weight' => $product['weight'],
                'incoterm' => $incoterm->code,
                'quantity' => [
                    'value' => $product['quantity'],
                    'unit' => $product['quantity_description']
                ],
                'volume' => [
                    'value' => $product['volume_value'],
                    'unit' => $product['volume_unit']
                ]
            ];
        }

        return $processedProducts;
    }
    private function getServicesData($services)
    {
        $included = [];
        $excluded = [];

        foreach ($services as $serviceId => $status) {
            $service = Service::findOrFail($serviceId);

            if ($status === 'include') {
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
    private function getCostsData($costs)
    {
        $processedCosts = [];

        foreach ($costs as $cost) {
            $logisticCost = Cost::findOrFail($cost['id']);

            $processedCosts[] = [
                'name' => $logisticCost->name,
                'description' => $logisticCost->description,
                'amount' => $cost['amount'],
                'currency' => $logisticCost->currency
            ];
        }
        return $processedCosts;
    }
}
