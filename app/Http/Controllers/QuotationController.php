<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Cost;
use App\Models\Country;
use App\Models\Service;
use App\Models\Customer;
use App\Models\Incoterm;
use App\Models\Continent;
use App\Models\Quotation;
use App\Models\CostDetail;
use App\Models\ExchangeRate;
use Illuminate\Http\Request;
use App\Models\QuotationDetail;
use App\Models\QuotationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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
        $costs = Cost::where('is_active', 1)->get();
        $exchangeRates = ExchangeRate::where('active', 1)->get();
        $customers = Customer::where('active', 1)->get();

        return view('quotations.create', compact(
            'incoterms',
            'services',
            'countries',
            'costs',
            'exchangeRates',
            'customers'
        ));
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

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            // 'delivery_date' => 'required|date',
            'reference_number' => 'required|integer',
            'currency' => 'required|string|max:3',
            'exchange_rate' => 'required|numeric',
            'amount' => 'required|numeric',
            'customer_nit' => 'required|exists:customers,NIT',
        ]);

        DB::beginTransaction();

        try {
            $quotation = new Quotation();
            // $quotation->delivery_date = $validatedData['delivery_date'];
            $quotation->delivery_date = now();
            $quotation->reference_number = $validatedData['reference_number'];
            $quotation->currency = $validatedData['currency'];
            $quotation->exchange_rate = $validatedData['exchange_rate'];
            $quotation->amount = $validatedData['amount'];
            $quotation->customer_nit = $validatedData['customer_nit'];
            $quotation->users_id = Auth::id();
            $quotation->status = 'pending';
            $quotation->save();

            if ($request->has('details')) {
                foreach ($request->details as $detail) {
                    $quotationDetail = new QuotationDetail();
                    $quotationDetail->quotation_id = $quotation->id;
                    $quotationDetail->origin_id = $detail['origin_id'];
                    $quotationDetail->destination_id = $detail['destination_id'];
                    $quotationDetail->incoterm_id = $detail['incoterm_id'];
                    $quotationDetail->quantity = $detail['quantity'];
                    $quotationDetail->quantity_description = $detail['quantity_description'];
                    $quotationDetail->weight = $detail['weight'];
                    $quotationDetail->volume = $detail['volume'];
                    $quotationDetail->volume_unit = $detail['volume_unit'];
                    $quotationDetail->description = $detail['description'];
                    $quotationDetail->save();

                    // Process cost details for this quotation detail
                    if (isset($detail['costs'])) {
                        foreach ($detail['costs'] as $cost) {
                            $costDetail = new CostDetail();
                            $costDetail->quotation_detail_id = $quotationDetail->id;
                            $costDetail->cost_id = $cost['cost_id'];
                            $costDetail->concept = $cost['concept'];
                            $costDetail->amount = $cost['amount'];
                            $costDetail->currency = $cost['currency'] ?? 'USD';
                            $costDetail->save();
                        }
                    }
                }
            }

            // Process services
            if ($request->has('services')) {
                foreach ($request->services as $serviceId => $included) {
                    if ($included) {
                        $quotationService = new QuotationService();
                        $quotationService->quotation_id = $quotation->id;
                        $quotationService->service_id = $serviceId;
                        $quotationService->included = true;
                        $quotationService->save();
                    }
                }
            }

            DB::commit();

            return redirect()->route('quotations.show', $quotation->id)
                ->with('success', 'Quotation created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error creating quotation: ' . $e->getMessage());
        }
    }
    public function show($id)
    {
        $user = Auth::user();
        $isAdmin = $user->role_id === 1;

        $quotation = Quotation::with([
            'customer',
            'details.origin',
            'details.destination',
            'details.incoterm',
            'details.costs',
            'services'
        ])->findOrFail($id);

        if (!$isAdmin && $quotation->users_id !== $user->id) {
            return abort(403, 'Unauthorized action.');
        }

        return view('quotations.show', compact('quotation'));
    }

    public function edit($id)
    {
        $user = Auth::user();
        $isAdmin = $user->role_id === 1;

        $quotation = Quotation::with([
            'details.origin.country',
            'details.destination.country',
            'details.incoterm',
            'details.costs.cost',
            'services'
        ])->findOrFail($id);

        if (!$isAdmin && $quotation->users_id !== $user->id) {
            return abort(403, 'Unauthorized action.');
        }

        $incoterms = Incoterm::where('is_active', 1)->get();
        $services = Service::where('is_active', 1)->get();
        $countries = Country::whereNull('deleted_at')->get();
        $costs = Cost::where('is_active', 1)->get();
        $exchangeRates = ExchangeRate::where('active', 1)->get();
        $customers = Customer::where('active', 1)->get();

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
            'destinationCities'
        ));
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $isAdmin = $user->role_id === 1;

        $quotation = Quotation::findOrFail($id);

        if (!$isAdmin && $quotation->users_id !== $user->id) {
            return abort(403, 'Acción no autorizada.');
        }

        $validatedData = $request->validate([
            // 'delivery_date' => 'required|date',
            'reference_number' => 'required|integer',
            'currency' => 'required|string|max:3',
            'exchange_rate' => 'required|numeric',
            'amount' => 'required|numeric',
            'customer_nit' => 'required|exists:customers,NIT',
            'status' => 'required|in:pending,approved,rejected',
            // Add validation for the other fields as necessary
        ]);

        DB::beginTransaction();

        try {
            // $quotation->delivery_date = $validatedData['delivery_date'];
            $quotation->delivery_date = now();
            $quotation->reference_number = $validatedData['reference_number'];
            $quotation->currency = $validatedData['currency'];
            $quotation->exchange_rate = $validatedData['exchange_rate'];
            $quotation->amount = $validatedData['amount'];
            $quotation->customer_nit = $validatedData['customer_nit'];
            $quotation->status = $validatedData['status'];
            $quotation->save();


            QuotationDetail::where('quotation_id', $quotation->id)->delete();

            if ($request->has('details')) {
                foreach ($request->details as $detail) {
                    $quotationDetail = new QuotationDetail();
                    $quotationDetail->quotation_id = $quotation->id;
                    $quotationDetail->origin_id = $detail['origin_id'];
                    $quotationDetail->destination_id = $detail['destination_id'];
                    $quotationDetail->incoterm_id = $detail['incoterm_id'];
                    $quotationDetail->quantity = $detail['quantity'];
                    $quotationDetail->quantity_description = $detail['quantity_description'];
                    $quotationDetail->weight = $detail['weight'];
                    $quotationDetail->volume = $detail['volume'];
                    $quotationDetail->volume_unit = $detail['volume_unit'];
                    $quotationDetail->description = $detail['description'];
                    $quotationDetail->save();

                    if (isset($detail['costs'])) {
                        foreach ($detail['costs'] as $cost) {
                            $costDetail = new CostDetail();
                            $costDetail->quotation_detail_id = $quotationDetail->id;
                            $costDetail->cost_id = $cost['cost_id'];
                            $costDetail->concept = $cost['concept'];
                            $costDetail->amount = $cost['amount'];
                            $costDetail->currency = $cost['currency'] ?? 'USD';
                            $costDetail->save();
                        }
                    }
                }
            }

            QuotationService::where('quotation_id', $quotation->id)->delete();

            if ($request->has('services')) {
                foreach ($request->services as $serviceId => $included) {
                    if ($included) {
                        $quotationService = new QuotationService();
                        $quotationService->quotation_id = $quotation->id;
                        $quotationService->service_id = $serviceId;
                        $quotationService->included = true;
                        $quotationService->save();
                    }
                }
            }

            DB::commit();

            return redirect()->route('quotations.show', $quotation->id)
                ->with('success', 'Cotización actualizada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error cotización no actualizada: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $user = Auth::user();
        $isAdmin = $user->role_id === 1;

        if (!$isAdmin) {
            return abort(403, 'Acción no autorizada.');
        }

        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $quotation = Quotation::findOrFail($id);
        $quotation->status = $request->status;
        $quotation->save();

        return redirect()->route('quotations.show', $quotation->id)
            ->with('success', 'Cotización actualizada correctamente.');
    }

    public function generatePDF($id)
    {
        $user = Auth::user();
        $isAdmin = $user->role_id === 1;

        $quotation = Quotation::with([
            'customer',
            'details.origin',
            'details.destination',
            'details.incoterm',
            'details.costs.cost',
            'services.service'
        ])->findOrFail($id);

        if (!$isAdmin && $quotation->users_id !== $user->id) {
            return abort(403, 'Acción no autorizada.');
        }

        return redirect()->route('quotations.show', $quotation->id)
            ->with('info', 'PDF generado correctamente.');
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
}
