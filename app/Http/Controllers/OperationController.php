<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Cost;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\BillingNote;
use Illuminate\Http\Request;
use App\Models\BillingNoteItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Style\Language;
use App\Helpers\NumberToWordsConverter;
use PhpOffice\PhpWord\Shared\Converter;

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
        // Validar que la cotización no tenga ya una billing note
        // if ($quotation->billingNote) {
        //     return redirect()->route('operations.create')
        //         ->with('error', 'Esta cotización ya tiene una nota de facturación asociada.');
        // }

        $quotation = Quotation::with(['customer', 'costDetails.cost'])
            ->findOrFail($id);
        $costs = Cost::where('is_active', true)
            ->orderBy('name')
            ->get();
        return view('operations.confirm_create', compact('quotation', 'costs'));
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
        $quotation = Quotation::with('customer')
            ->findOrFail($id);

        $validated = $request->validate(
            [
                'costsDetails' => 'required|array',
                'costsDetails.*.amount' => 'required|numeric',
                'costsDetails.*.description' => 'nullable|string',
                'costsDetails.*.concept' => 'required|string',
                'costsDetails.*.type' => 'required|string|in:cost,charge',
                'costsDetails.*.is_amount_parallel' => 'sometimes|boolean',
                'costsDetails.*.amount_parallel' => 'nullable|numeric',
                'costsDetails.*.exchange_rate' => 'nullable|numeric',
            ],
            [
                'costsDetails.required' => 'Los detalles de costos son obligatorios.',
                'costsDetails.*.amount.required' => 'El monto es obligatorio.',
                'costsDetails.*.type.in' => 'El tipo debe ser "cost" o "charge".',
                'costsDetails.*.concept.required' => 'El concepto descripción es obligatorio.',
            ]
        );

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

            $maxAttempts = 100;
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

            // Calcular total amount considerando is_amount_parallel
            $totalAmount = collect($validated['costsDetails'])->sum(function ($item) {
                return $item['is_amount_parallel'] == '1' ? $item['amount_parallel'] : $item['amount'];
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
            foreach ($validated['costsDetails'] as $itemData) {
                if (!isset($itemData['enabled']) || $itemData['enabled'] == '1') {
                    BillingNoteItem::create([
                        'billing_note_id' => $billingNote->id,
                        'description' => $itemData['concept'] ?? $itemData['description'],
                        'is_amount_parallel' => $itemData['is_amount_parallel'] == '1',
                        'type' => $itemData['type'],
                        'amount' => $itemData['amount'],
                        'amount_parallel' => $itemData['amount_parallel'] ?? null,
                        'currency' => $quotation->currency,
                        'exchange_rate' => $itemData['exchange_rate'] ?? $quotation->exchange_rate,
                    ]);
                }
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
        $billingNote = BillingNote::with(['items', 'quotation', 'customer'])
            ->findOrFail($id);

        $costsDetails = $billingNote->items->map(function ($item) {
            return [
                'amount' => $item->amount,
                'is_amount_parallel' => $item->is_amount_parallel ? '1' : '0',
                'amount_parallel' => $item->amount_parallel,
                'exchange_rate' => $item->exchange_rate,
                'enabled' => '1',
                'concept' => $item->description,
                'type' => $item->type,
                'cost_id' => $item->id,
            ];
        })->toArray();
        dd([
            'billingNote' => $billingNote,
            'costsDetails' => $costsDetails,
        ]);
        return view('operations.show', [
            'billingNote' => $billingNote,
            'costsDetails' => $costsDetails,
        ]);
    }
    public function edit($id)
    {
        $billingNote = BillingNote::with(['items', 'quotation', 'customer'])
            ->findOrFail($id);

        $costsDetails = $billingNote->items->map(function ($item) {
            return [
                'amount' => $item->amount,
                'is_amount_parallel' => $item->is_amount_parallel ? '1' : '0',
                'amount_parallel' => $item->amount_parallel,
                'exchange_rate' => $item->exchange_rate,
                'enabled' => '1',
                'concept' => $item->description,
                'type' => $item->type,
                'cost_id' => $item->id,
            ];
        })->toArray();
        $costs = Cost::where('is_active', true)
            ->orderBy('name')
            ->get();
        return view('operations.edit', [
            'billingNote' => $billingNote,
            'costsDetails' => $costsDetails,
            'costs' => $costs,
        ]);
    }
    public function update(Request $request, $id)
    {
        $billingNote = BillingNote::findOrFail($id);

        $validated = $request->validate(
            [
                'costsDetails' => 'required|array',
                'costsDetails.*.amount' => 'required|numeric',
                'costsDetails.*.description' => 'nullable|string',
                'costsDetails.*.concept' => 'required|string',
                'costsDetails.*.type' => 'required|string|in:cost,charge',
                'costsDetails.*.is_amount_parallel' => 'sometimes|boolean',
                'costsDetails.*.amount_parallel' => 'nullable|numeric',
                'costsDetails.*.exchange_rate' => 'nullable|numeric',
            ],
            [
                'costsDetails.required' => 'Los detalles de costos son obligatorios.',
                'costsDetails.*.amount.required' => 'El monto es obligatorio.',
                'costsDetails.*.type.in' => 'El tipo debe ser "cost" o "charge".',
                'costsDetails.*.concept.required' => 'El concepto descripción es obligatorio.',
            ]
        );

        DB::beginTransaction();
        try {
            // Calcular nuevo total amount considerando is_amount_parallel
            $totalAmount = collect($validated['costsDetails'])->sum(function ($item) {
                return $item['is_amount_parallel'] == '1' ? $item['amount_parallel'] : $item['amount'];
            });

            // Actualizar la billing note
            $billingNote->update([
                'total_amount' => $totalAmount,
                'exchange_rate' => $billingNote->exchange_rate,
            ]);

            // Eliminar items antiguos
            $billingNote->items()->delete();

            // Crear nuevos items
            foreach ($validated['costsDetails'] as $itemData) {
                if (!isset($itemData['enabled']) || $itemData['enabled'] == '1') {
                    BillingNoteItem::create([
                        'billing_note_id' => $billingNote->id,
                        'description' => $itemData['concept'] ?? $itemData['description'],
                        'is_amount_parallel' => $itemData['is_amount_parallel'] == '1',
                        'type' => $itemData['type'],
                        'amount' => $itemData['amount'],
                        'amount_parallel' => $itemData['amount_parallel'] ?? null,
                        'currency' => $billingNote->currency,
                        'exchange_rate' => $itemData['exchange_rate'] ?? $billingNote->exchange_rate,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('operations.show', $billingNote->id)
                ->with('success', 'Nota de Cobranza actualizada exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Error actualizando la nota de cobranza: ' . $e->getMessage());
        }
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
    public function downloadOperation(Request $request)
    {
        $request->validate(
            [
                'id' => 'required|integer|exists:billing_notes,id',
                'visible' => 'required|boolean',
                'is_parallel' => 'sometimes|boolean',
            ],
            [
                'id.required' => 'El ID de la nota de cobranza es obligatorio.',
                'id.integer' => 'El ID de la nota de cobranza debe ser un número entero.',
                'id.exists' => 'La nota de cobranza no existe.',
                'visible.required' => 'El campo visible es obligatorio.',
            ]
        );
        $billingNote = BillingNote::with(['items', 'quotation.customer'])
            ->findOrFail($request->id);
        $visible = $request->visible ?? true;
        $isParallel = $request->is_parallel ?? false;
        try {
            $docuement = $this->generateWordDocument($billingNote, $visible, $isParallel);
            return $docuement;
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error al descargar la nota de cobranza: ' . $e->getMessage());
        }
    }

    private function generateWordDocument(BillingNote $billingNote, $visible, $isParallel = false)
    {
        $phpWord = new PhpWord();
        // Configurar el idioma español para el documento
        $phpWord->getSettings()->setThemeFontLang(new Language(Language::ES_ES));

        // Establecer propiedades del documento en español
        $properties = $phpWord->getDocInfo();
        $properties->setTitle('Documento');
        $properties->setCreator('NOVALOGISTIC BOLIVIA SRL');
        $properties->setCompany('NOVALOGISTIC BOLIVIA SRL');
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

        // Números de documento
        $section->addText(
            $billingNote->note_number,
            ['size' => 11, 'bold' => true],
            ['spaceAfter' => 0, 'align' => 'right']
        );

        $section->addText(
            $billingNote->op_number,
            ['size' => 11, 'bold' => true],
            ['spaceAfter' => Converter::pointToTwip(11), 'align' => 'right']
        );

        // Título del documento
        $section->addText(
            'NOTA DE COBRANZA',
            ['size' => 11, 'underline' => 'single', 'bold' => true, 'allCaps' => true],
            ['spaceAfter' => Converter::pointToTwip(15), 'align' => 'center']
        );

        // Estilos
        $fontStyle = ['name' => 'Montserrat', 'size' => 11, 'bold' => true];
        $valueStyle = ['name' => 'Montserrat', 'size' => 11];
        $paragraphStyle = ['spaceAfter' => 0, 'spaceBefore' => 0, 'spacing' => 0];

        $tableStyle = [
            'cellMargin' => 50,
            'width' => 100,
            'unit' => 'pct',
        ];

        // Tabla de información del cliente
        $table = $section->addTable($tableStyle);

        // Información del cliente
        $row = $table->addRow();
        $cell = $row->addCell(5000, ['gridSpan' => 2]);
        $textRun = $cell->addTextRun($paragraphStyle);
        $textRun->addText("CLIENTE: ", $fontStyle);
        $textRun->addText("\t" . $billingNote->customer->name, $valueStyle);

        $row = $table->addRow();
        $cell = $row->addCell(5000, ['gridSpan' => 2]);
        $textRun = $cell->addTextRun($paragraphStyle);
        $textRun->addText("FECHA: ", $fontStyle);
        $textRun->addText("\t" . Carbon::parse($billingNote->emission_date)->format('d/m/Y'), $valueStyle);

        $row = $table->addRow();
        $cell = $row->addCell(5000);
        $textRun = $cell->addTextRun($paragraphStyle);
        $textRun->addText("TC: ", $fontStyle);
        $textRun->addText("\t\t" . number_format($billingNote->exchange_rate, 2), $valueStyle);

        $cell = $row->addCell(5000);
        $textRun = $cell->addTextRun(array_merge($paragraphStyle, ['alignment' => 'right']));

        if ($billingNote->quotation->reference_customer != null) {
            $textRun->addText("REF: ", $fontStyle);
            $textRun->addText($billingNote->quotation->reference_customer, $valueStyle);
        }

        $tableStyle = [
            'borderColor' => '000000',
            'cellMarginLeft' => 50,
            'cellMarginRight' => 50,
            'width' => 100,
        ];
        $phpWord->addTableStyle('conceptsTable', $tableStyle);
        $table = $section->addTable('conceptsTable');



        // Encabezados de la tabla (siempre mostrar ambas columnas)
        $table->addRow();
        $table->addCell(Converter::cmToTwip(10), [
            'valign' => 'center',
            'borderSize' => 10,
        ])->addText('DESCRIPCIÓN', [
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
        $table->addCell(Converter::cmToTwip(4), [
            'valign' => 'center',
            'borderSize' => 10,
        ])->addText('MONTO BS', [
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
        $table->addCell(Converter::cmToTwip(4), [
            'valign' => 'center',
            'borderSize' => 10,
        ])->addText('MONTO ' . $billingNote->currency, [
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

        // Variables para calcular totales
        $totalForeign = 0;
        $totalBs = 0;
        $itemsToShow = [];

        // Primero determinamos qué items mostrar y calcular montos
        foreach ($billingNote->items as $item) {
            // Usar amount_parallel si está marcado, sino amount normal
            $amount = $item->is_amount_parallel ? $item->amount_parallel : $item->amount;

            if ($isParallel) {
                if ($item->exchange_rate != $billingNote->exchange_rate) {
                    // Calcular la diferencia absoluta entre los tipos de cambio
                    $exchangeRateDifference = abs($billingNote->exchange_rate - $item->exchange_rate);
                    $amountBs = $amount * $exchangeRateDifference;

                    $itemsToShow[] = [
                        'description' => $item->description,
                        'amount' => $amount,
                        'amountBs' => $amountBs,
                        'show' => true
                    ];
                    $totalForeign += $amount;
                    $totalBs += $amountBs;
                }
            } else {
                // Mostrar todos los items, convertir usando exchange_rate de billing_note
                $amountBs = $amount * $billingNote->exchange_rate;
                $itemsToShow[] = [
                    'description' => $item->description,
                    'amount' => $amount,
                    'amountBs' => $amountBs,
                    'show' => true
                ];
                $totalForeign += $amount;
                $totalBs += $amountBs;
            }
        }

        // Mostrar los items en la tabla
        foreach ($itemsToShow as $item) {
            $table->addRow();
            $table->addCell(Converter::cmToTwip(10), [
                'valign' => 'center',
                'borderSize' => 10,
            ])->addText(
                $item['description'],
                ['size' => 11],
                [
                    'spaceBefore' => 0,
                    'spaceAfter' => 0,
                    'spacing' => 0,
                    'lineHeight' => 1.0,
                    'align' => 'left'
                ]
            );
            $table->addCell(Converter::cmToTwip(4), [
                'valign' => 'center',
                'borderSize' => 10,
            ])->addText(
                number_format($item['amountBs'], 2, ',', '.'),
                ['size' => 11],
                [
                    'spaceBefore' => 0,
                    'spaceAfter' => 0,
                    'spacing' => 0,
                    'lineHeight' => 1.0,
                    'align' => 'right'
                ]
            );
            $table->addCell(Converter::cmToTwip(4), [
                'valign' => 'center',
                'borderSize' => 10,
            ])->addText(
                number_format($item['amount'], 2, ',', '.'),
                ['size' => 11],
                [
                    'spaceBefore' => 0,
                    'spaceAfter' => 0,
                    'spacing' => 0,
                    'lineHeight' => 1.0,
                    'align' => 'right'
                ]
            );
        }

        // Rellenar filas vacías si es necesario (basado en items originales)
        $rowsToAdd = max(0, 16 - count($billingNote->items));
        for ($i = 0; $i < $rowsToAdd; $i++) {
            $table->addRow();
            $table->addCell(Converter::cmToTwip(10), [
                'valign' => 'center',
                'borderTopSize' => 0,
                'borderBottomSize' => 0,
                'borderTopColor' => 'FFFFFF',
                'borderBottomColor' => 'FFFFFF',
                'borderBottomSize' => 0,
                'borderRightSize' => 10,
                'borderLeftSize' => 10,
            ])->addText('', [
                'size' => 11
            ], [
                'spaceBefore' => 0,
                'spaceAfter' => 0,
                'spacing' => 0,
                'lineHeight' => 1.0,
                'align' => 'left'
            ]);
            $table->addCell(Converter::cmToTwip(4), [
                'valign' => 'center',
                'borderTopSize' => 0,
                'borderBottomSize' => 0,
                'borderTopColor' => 'FFFFFF',
                'borderBottomColor' => 'FFFFFF',
                'borderLeftSize' => 10,
                'borderRightSize' => 10,
            ])->addText('', [
                'size' => 11
            ], [
                'spaceBefore' => 0,
                'spaceAfter' => 0,
                'spacing' => 0,
                'lineHeight' => 1.0,
                'align' => 'right'
            ]);
            $table->addCell(Converter::cmToTwip(4), [
                'valign' => 'center',
                'borderTopSize' => 0,
                'borderBottomSize' => 0,
                'borderTopColor' => 'FFFFFF',
                'borderBottomColor' => 'FFFFFF',
                'borderLeftSize' => 10,
                'borderRightSize' => 10,
            ])->addText('', [
                'size' => 11
            ], [
                'spaceBefore' => 0,
                'spaceAfter' => 0,
                'spacing' => 0,
                'lineHeight' => 1.0,
                'align' => 'right'
            ]);
        }

        // Total (siempre mostrar ambos)
        $table->addRow();
        $table->addCell(6000, ['borderSize' => 10])->addText(
            'TOTAL',
            ['bold' => true, 'size' => 11, 'allCaps' => true],
            [
                'spaceBefore' => 0,
                'spaceAfter' => 0,
                'spacing' => 0,
                'lineHeight' => 1.0,
                'align' => 'right'
            ]
        );
        $table->addCell(2000, ['borderSize' => 10])->addText(
            number_format($totalBs, 2, ',', '.'),
            ['bold' => true, 'size' => 11],
            [
                'spaceBefore' => 0,
                'spaceAfter' => 0,
                'spacing' => 0,
                'lineHeight' => 1.0,
                'align' => 'right'
            ]
        );
        $table->addCell(2000, ['borderSize' => 10])->addText(
            number_format($totalForeign, 2, ',', '.'),
            ['bold' => true, 'size' => 11],
            [
                'spaceBefore' => 0,
                'spaceAfter' => 0,
                'spacing' => 0,
                'lineHeight' => 1.0,
                'align' => 'right'
            ]
        );

        // Literal del total en ambas monedas
        if ($billingNote->currency == 'USD') {
            $currencyInWords = 'DÓLARES AMERICANOS';
        } elseif ($billingNote->currency == 'EUR') {
            $currencyInWords = 'EUROS';
        } else {
            $currencyInWords = strtoupper($billingNote->currency);
        }

        $totalInWordsForeign = NumberToWordsConverter::convertToWords(
            $totalForeign,
            $currencyInWords
        );

        $totalInWordsBs = NumberToWordsConverter::convertToWords(
            $totalBs,
            'BOLIVIANOS'
        );

        $row = $table->addRow();
        $cell = $row->addCell(5000, [
            'valign' => 'center',
            'gridSpan' => 3,
            'borderSize' => 10,
        ]);
        $textRun = $cell->addTextRun($paragraphStyle);
        $textRun->addText("Son: ", $fontStyle);
        $textRun->addText($totalInWordsForeign, [
            'size' => 11,
            'allCaps' => true
        ], [
            'spaceBefore' => 0,
            'spaceAfter' => 0,
            'spacing' => 0,
            'lineHeight' => 1.0,
            'align' => 'left'
        ]);

        $row = $table->addRow();
        $cell = $row->addCell(5000, [
            'valign' => 'center',
            'gridSpan' => 3,
            'borderSize' => 10,
        ]);
        $textRun = $cell->addTextRun($paragraphStyle);
        $textRun->addText("Equivalente a: ", $fontStyle);
        $textRun->addText($totalInWordsBs, [
            'size' => 11,
            'allCaps' => true
        ], [
            'spaceBefore' => 0,
            'spaceAfter' => 0,
            'spacing' => 0,
            'lineHeight' => 1.0,
            'align' => 'left'
        ]);




        // Información de la empresa
        $section->addText(
            'NOVALOGBO SRL',
            [
                'size' => 8,
                'bold' => true
            ],
            [
                'spaceBefore' => Converter::pointToTwip(8),
                'spaceAfter' => 0,
            ]
        );
        $section->addText(
            'NIT: 412B48023',
            [
                'size' => 8,
                'bold' => true
            ],
            [
                'spaceAfter' => 0,
                'spaceBefore' => 0,
            ]
        );
        $section->addText(
            'BANCO BISA',
            [
                'size' => 8,
                'bold' => true
            ],
            [
                'spaceAfter' => 0,
                'spaceBefore' => 0,
            ]
        );
        $section->addText(
            'BS: 7994826010',
            [
                'size' => 8,
                'bold' => true
            ],
            [
                'spaceAfter' => 0,
                'spaceBefore' => 0,
            ]
        );
        $section->addText(
            'BS: 7994829064',
            [
                'size' => 8,
                'bold' => true
            ],
            [
                'spaceAfter' => 0,
                'spaceBefore' => 0,
            ]
        );

        // Guardar y descargar el documento
        $cleanRef = str_replace('/', '_', $billingNote->note_number);
        $filename = "Nota_Cobranza_{$cleanRef}.docx";
        $tempFile = tempnam(sys_get_temp_dir(), 'PHPWord');
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }
}
