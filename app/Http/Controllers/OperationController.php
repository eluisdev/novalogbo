<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\BillingNote;
use Illuminate\Http\Request;
use App\Models\BillingNoteItem;
use App\Models\City;
use App\Models\Cost;
use App\Models\ExchangeRate;
use App\Models\Incoterm;
use App\Models\QuantityDescription;
use App\Models\Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OperationController extends Controller
{
    public function index()
    {
        $billingNotes = BillingNote::with(['quotation.customer', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('operations.index', compact('billingNotes'));
    }

    public function create()
    {
        $quotations = Quotation::with(['customer'])
            ->where('status', 'accepted')
            ->orderBy('created_at', 'desc')
            ->get();
        $customers = Customer::orderBy('name')->get();
        return view('operations.create', compact('customers', 'quotations'));
    }

    public function showCreateFromQuotation($id)
    {
        //Validar que la cotización no tenga ya una billing note
        $quotation = Quotation::with(['customer', 'costDetails.cost'])
            ->findOrFail($id);

        $costs = Cost::where('is_active', 1)->get();

        if ($quotation->billingNote) {
            return redirect()->route('operations.create')
                ->with('error', 'Esta cotización ya tiene una nota de facturación asociada.');
        }

        return view('operations.confirm_create', compact('quotation', 'costs'));
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
            'reference_customer' => $quotation->reference_customer,
            'delivery_date' => $quotation->delivery_date,
            'insurance' => $quotation->insurance,
            'payment_method' => $quotation->payment_method,
            'validity' => $quotation->validity,
            'juncture' => $quotation->juncture,
            'observations' => $quotation->observations,
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

        return view('operations.showQuotation', ['quotation_data' => $response]);
    }

    public function searchQuotations(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
            'customer_nit' => 'nullable|exists:customers,NIT',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $query = Quotation::with(['customer', 'costDetails.cost'])
            ->where('status', 'accepted');
        // Para validar si esta cotizacion ya tiene billing note
        // ->whereDoesntHave('billingNote'); // Solo cotizaciones sin billing note

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('reference_number', 'like', '%' . $request->search . '%')
                    ->orWhere('reference_customer', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('customer_nit')) {
            $query->where('customer_nit', $request->customer_nit);
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        $quotations = $query->orderBy('created_at', 'desc')->get();
        //dd(response()->json($quotations));
        return response()->json($quotations);
    }

    public function storeFromQuotation(Request $request, $id)
    {
        // dd($request);
        $quotation = Quotation::with(['customer', 'costDetails.cost'])
            ->findOrFail($id);
        $validated = $request->validate([
            'costsDetails' => 'required|array',
        ]);

        // Verificar nuevamente que no exista billing note
        // if ($quotation->billingNote) {
        //     return redirect()->back()
        //         ->with('error', 'Esta cotización ya tiene una nota de facturación asociada.');
        // }

        DB::beginTransaction();
        try {
            $year = Carbon::now()->format('y');
            $sequence = BillingNote::whereYear('created_at', Carbon::now()->year)->count() + 1;
            $sequenceFormatted = str_pad($sequence, 3, '0', STR_PAD_LEFT);

            $numbers = [
                'op_number' => "OP-{$sequenceFormatted}-{$year}",
                'note_number' => "No-{$sequenceFormatted}-{$year}"
            ];

            $checkUnique = function ($number, $field) use ($year) {
                return !BillingNote::where($field, $number)
                    ->whereYear('created_at', Carbon::now()->year)
                    ->exists();
            };

            $maxAttempts = 100; // Para evitar bucles infinitos en casos extremos
            $attempts = 0;

            while (!$checkUnique($numbers['op_number'], 'op_number') || !$checkUnique($numbers['note_number'], 'note_number')) {
                $sequence++;
                $sequenceFormatted = str_pad($sequence, 3, '0', STR_PAD_LEFT);
                $numbers['op_number'] = "OP-{$sequenceFormatted}-{$year}";
                $numbers['note_number'] = "No-{$sequenceFormatted}-{$year}";
                $attempts++;
                if ($attempts > $maxAttempts) {
                    error_log("Error al generar números de nota únicos después de {$maxAttempts} intentos.");
                    $numbers = [
                        'op_number' => null,
                        'note_number' => null
                    ];
                    break;
                }
            }

            // Calcular total amount
            $totalAmount = collect($validated['cost_details'])->sum(function ($item) {
                return $item['amount'];
            });

            // Crear la billing note
            $billingNote = BillingNote::create([
                'op_number' => $numbers['op_number'],
                'note_number' => $numbers['note_number'],
                'emission_date' => Carbon::now(),
                'total_amount' => $totalAmount,
                'currency' => $quotation->currency,
                'exchange_rate' => $quotation->exchange_rate,
                'user_id' => Auth::id(),
                'quotation_id' => $quotation->id,
                'customer_nit' => $quotation->customer_nit,
                'status' => 'pending',
            ]);

            // Crear items
            foreach ($validated['cost_details'] as $itemData) {
                BillingNoteItem::create([
                    'billing_note_id' => $billingNote->id,
                    'description' => $itemData['description'],
                    'type' => $itemData['type'],
                    'amount' => $itemData['amount'],
                    'currency' => $itemData['currency'],
                    'exchange_rate' => $itemData['exchange_rate'],
                ]);
            }
            DB::commit();

            return redirect()->route('operations.show', $billingNote->id)
                ->with('success', 'Nota de Cobranza creada exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Error creando la nota de cobranza: ' . $e->getMessage());
        }
    }
    public function show($id)
    {
        $billingNote = BillingNote::with(['quotation.customer', 'user', 'items'])
            ->findOrFail($id);

        return view('operations.show', compact('billingNote'));
    }

    public function showPrueba($id)
    {
        // Crear el array principal con los datos de attributes
        $quotation = [
            'id' => 5,
            'delivery_date' => "2025-04-25 21:52:39",
            'reference_number' => "005/25",
            'reference_customer' => "Referencia x",
            'currency' => "USD",
            'exchange_rate' => 6.96,
            'amount' => 1000,
            'status' => "accepted",
            'insurance' => "seguro 1",
            'payment_method' => "pago 1",
            'validity' => "validez 1",
            'observations' => "Observaciones 1",
            'juncture' => null,
            'users_id' => 2,
            'customer_nit' => 4492865,
            'name' => "Enrique luis",
            'created_at' => "2025-04-25 19:35:35",
            'updated_at' => "2025-04-25 21:52:39"
        ];

        // Crear el array de costsDetails
        $costsDetails = [
            [
                'id' => "1",
                'amount' => "1000",
                'is_amount_parallel' => "0",
                'amount_parallel' => null,
                'exchange_rate' => "6.96",
                'enabled' => "1",
                'concept' => "FLETE TERRESTRE",
                'type' => "cost",
            ],
            [
                'id' => "2",
                'amount' => "2000",
                'is_amount_parallel' => "0",
                'amount_parallel' => null,
                'exchange_rate' => "6.96",
                'enabled' => "1",
                'concept' => "FLETE AEREO",
                'type' => "cost",
            ],
            [
                'id' => "3",
                'amount' => "1000",
                'is_amount_parallel' => "0",
                'amount_parallel' => null,
                'exchange_rate' => "8.96",
                'enabled' => "1",
                'concept' => "COSTO 1",
                'type' => "cost",
            ],
            [
                'id' => "4",
                'amount' => "1000",
                'is_amount_parallel' => "0",
                'amount_parallel' => null,
                'exchange_rate' => "6.96",
                'enabled' => "1",
                'concept' => "DESVIO DE EMBARCACION",
                'type' => "charge"
            ]
        ];

        return view('operations.show', [
            'quotation' => $quotation,
            'costsDetails' => $costsDetails
        ]);
    }

    public function editPrueba($id)
    {
        // Crear el array principal con los datos de attributes
        $quotation = Quotation::with(['customer'])
            ->findOrFail($id);
        $costs = Cost::where('is_active', 1)->get();
        // Crear el array de costsDetails
        $costsDetails = [
            [
                'id' => "1",
                'amount' => "1000",
                'is_amount_parallel' => "0",
                'amount_parallel' => null,
                'exchange_rate' => "6.96",
                'concept' => "FLETE TERRESTRE",
                'type' => "cost",
            ],
            [
                'id' => "2",
                'amount' => "2000",
                'is_amount_parallel' => "0",
                'amount_parallel' => null,
                'exchange_rate' => "6.96",
                'concept' => "FLETE AEREO",
                'type' => "cost",
            ],
            [
                'id' => "7",
                'amount' => "1000",
                'is_amount_parallel' => "0",
                'amount_parallel' => null,
                'exchange_rate' => "6.96",
                'concept' => "DESVIO DE EMBARCACION",
                'type' => "charge"
            ],
            [
                'id' => "3",
                'amount' => "1000",
                'is_amount_parallel' => "0",
                'amount_parallel' => null,
                'exchange_rate' => "8.96",
                'concept' => "COSTO 1",
                'type' => "cost",
            ],
        ];

        return view('operations.edit', [
            'quotation' => $quotation,
            'costsDetails' => $costsDetails,
            'costs' => $costs
        ]);
    }

    public function edit($id)
    {
        $billingNote = BillingNote::with(['quotation.customer', 'user', 'items'])
            ->findOrFail($id);

        return view('operations.edit', compact('billingNote'));
    }
    public function destroy($id)
    {
        $billingNote = BillingNote::findOrFail($id);
        $billingNote->delete();

        return redirect()->route('operations.index')
            ->with('success', 'Nota de Cobranza eliminada exitosamente');
    }
    public function toggleStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string',
        ]);
        $billingNote = BillingNote::findOrFail($id);
        $billingNote->status = $request->status;
        $billingNote->save();

        return redirect()->route('operations.index')->with('success', 'Estado de la nota de cobranza actualizado con éxito.');
    }
}
