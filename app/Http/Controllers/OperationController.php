<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\BillingNote;
use Illuminate\Http\Request;
use App\Models\BillingNoteItem;
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

    public function showCreateFromQuotation(Quotation $quotation)
    {
        // Validar que la cotización no tenga ya una billing note
        // if ($quotation->billingNote) {
        //     return redirect()->route('operations.create')
        //         ->with('error', 'Esta cotización ya tiene una nota de facturación asociada.');
        // }

        return view('operations.confirm_create', [
            'quotation' => $quotation->load(['customer', 'costDetails.cost'])
        ]);
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
        $quotation = Quotation::with(['customer', 'costDetails.cost'])
            ->findOrFail($id);
        $validated = $request->validate([
            'cost_details' => 'required|array',
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
