<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\BillingNote;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpWord\Style\Language;
use PhpOffice\PhpWord\Shared\Converter;
use App\Helpers\NumberToWordsConverter;

class InvoiceController extends Controller
{
    public function view()
    {
        return view('invoices.view');
    }
    public function create()
    {
        $customers = Customer::all();
        $quotations = Quotation::all();
        return view('invoices.create', compact('customers', 'quotations'));
    }
    public function store(Request $request)
    {
        // Valores
    }
    public function show($id)
    {
        $invoice = Invoice::with(['customer', 'items.cost', 'quotation.products'])->findOrFail($id);
        return view('invoices.show', compact('invoice'));
    }
    public function edit($id)
    {
        $invoice = Invoice::with(['customer', 'items.cost', 'quotation.products'])->findOrFail($id);
        $customers = Customer::all();
        $quotations = Quotation::all();
        return view('invoices.edit', compact('invoice', 'customers', 'quotations'));
    }
    public function delete($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->delete();
        return redirect()->route('invoices.view')->with('success', 'Factura eliminada exitosamente.');
    }
    public function toggleStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string'
        ]);
        $invoice = Invoice::findOrFail($id);
        $invoice->status = $request->status;
        $invoice->save();
        return redirect()->route('invoices.view')->with('success', 'Estado de la factura actualizado exitosamente.');
    }

    public function generateInvoiceFromBillingNote(Request $request)
    {
        $request->validate(
            [
                'id' => 'required|integer|exists:billing_notes,id',
                'visible' => 'required|boolean',
                'use_exchange_rate' => 'sometimes|boolean',
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
        //dd($request->all(), $billingNote);

        $visible = $request->visible ?? true;
        $isParallel = $request->use_exchange_rate ?? false;

        DB::beginTransaction();
        try {
            // Check if invoice already exists for this billing note
            $existingInvoice = Invoice::where('billing_note_id', $billingNote->id)->first();

            if (!$existingInvoice) {
                // Create the invoice from billing note data
                $invoice = Invoice::create([
                    'invoice_number' => Invoice::generateInvoiceNumber(),
                    'invoice_date' => now(),
                    'due_date' => now()->addDays(30),
                    'subtotal' => $billingNote->total_amount,
                    'tax_amount' => 0, // Assuming no tax for now
                    'total_amount' => $billingNote->total_amount,
                    'currency' => $billingNote->currency,
                    'exchange_rate' => $billingNote->exchange_rate,
                    'status' => 'issued',
                    'notes' => "Factura generada automáticamente desde la nota de cobranza #{$billingNote->op_number}",
                    'user_id' => Auth::id(),
                    'customer_nit' => $billingNote->customer_nit,
                    'quotation_id' => $billingNote->quotation_id,
                    'billing_note_id' => $billingNote->id,
                ]);

                // Create invoice items from billing note items
                foreach ($billingNote->items as $item) {
                    $amount = $item->is_amount_parallel ? $item->amount_parallel : $item->amount;

                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'description' => $item->description,
                        'is_amount_parallel' => $item->is_amount_parallel,
                        'type' => $item->type,
                        'amount' => $amount,
                        'amount_parallel' => $item->amount_parallel,
                        'quantity' => 1,
                        'unit_price' => $amount,
                        'tax_rate' => 0,
                        'tax_amount' => 0,
                        'subtotal' => $amount,
                        'total' => $amount,
                        'currency' => $item->currency,
                        'exchange_rate' => $item->exchange_rate,
                    ]);
                }

                $invoice->load('items');
            } else {
                $invoice = $existingInvoice;
            }

            DB::commit();

            // Generate the Word document with the invoice data
            return $this->generateInvoiceWordDocument($invoice, $visible, $isParallel);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Error al generar la factura: ' . $e->getMessage());
        }
    }


    private function generateInvoiceWordDocument(Invoice $invoice, $visible, $isParallel = false)
    {
        $phpWord = new PhpWord();
        $phpWord->getSettings()->setThemeFontLang(new Language(Language::ES_ES));
        $properties = $phpWord->getDocInfo();
        $properties->setTitle('Factura');
        $properties->setCreator('NOVALOGISTIC BOLIVIA SRL');
        $properties->setCompany('NOVALOGISTIC BOLIVIA SRL');
        $phpWord->setDefaultFontName('Calibri');
        $phpWord->setDefaultFontSize(9);

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

        $phpWord->setDefaultParagraphStyle([
            'spaceAfter' => 0,
            'spaceBefore' => 0,
            'spacing' => 0,
            'lineHeight' => 1.0,
        ]);

        // Estilos
        $titleStyle = ['bold' => true, 'size' => 15, 'color' => '1F497D'];
        $headerStyle = ['bold' => true, 'size' => 11, 'color' => '1F497D'];
        $tableHeaderStyle = ['bold' => true, 'bgColor' => '1F497D', 'color' => 'FFFFFF'];
        $subHeaderStyle = ['bold' => true, 'size' => 10, 'color' => '1F497D'];
        $paragraphOptions = ['alignment' => 'left'];
        $centerOptions = ['alignment' => 'center', 'spaceAfter' => 0, 'spaceBefore' => 0, 'spacing' => 0, 'lineHeight' => 1.0];
        $rightOptions = ['alignment' => 'right', 'spaceAfter' => 0, 'spaceBefore' => 0, 'spacing' => 0, 'lineHeight' => 1.0];

        // Banner de factura
        $bannerTable = $section->addTable(['borderSize' => 0, 'cellMargin' => 0, 'width' => 100, 'unit' => 'pct']);
        $bannerTable->addRow(600);
        $bannerCell = $bannerTable->addCell(10000, ['bgColor' => 'E8EEF4', 'valign' => 'center', 'borderSize' => 1, 'borderColor' => '1F497D']);
        $bannerCell->addText('FACTURA', $titleStyle, $centerOptions);
        $bannerCell->addText('N°: ' . $invoice->invoice_number, ['bold' => true, 'size' => 13, 'color' => '1F497D'], $centerOptions);
        $section->addTextBreak(1);

        // Información del cliente y factura
        $infoTable = $section->addTable(['borderSize' => 0, 'cellMargin' => 30, 'width' => 100, 'unit' => 'pct']);
        $infoTable->addRow();

        // Celda de información de empresa
        $infoCell1 = $infoTable->addCell(5000, ['valign' => 'top', 'borderSize' => 1, 'borderColor' => 'C0C0C0', 'bgColor' => 'F8F9FA']);
        $infoCell1->addText('INFORMACIÓN DEL CLIENTE', $subHeaderStyle, $centerOptions);
        $clientTable = $infoCell1->addTable(['width' => 100, 'unit' => 'pct']);

        $clientTable->addRow();
        $clientTable->addCell(1500)->addText('Nombre:', ['bold' => true]);
        $clientTable->addCell(3500)->addText($invoice->quotation->customer->name);

        $clientTable->addRow();
        $clientTable->addCell(1500)->addText('NIT:', ['bold' => true]);
        $clientTable->addCell(3500)->addText($invoice->customer_nit);

        $clientTable->addRow();
        $clientTable->addCell(1500)->addText('Email:', ['bold' => true]);
        $clientTable->addCell(3500)->addText($invoice->quotation->customer->email ?? '');

        if ($invoice->quotation->customer->phone) {
            $clientTable->addRow();
            $clientTable->addCell(1500)->addText('Teléfono:', ['bold' => true]);
            $clientTable->addCell(3500)->addText($invoice->quotation->customer->phone);
        }

        if ($invoice->quotation->customer->address) {
            $clientTable->addRow();
            $clientTable->addCell(1500)->addText('Dirección:', ['bold' => true]);
            $clientTable->addCell(3500)->addText($invoice->quotation->customer->address);
        }

        // Celda de información de factura
        $infoCell2 = $infoTable->addCell(5000, ['valign' => 'top', 'borderSize' => 1, 'borderColor' => 'C0C0C0', 'bgColor' => 'F8F9FA']);
        $infoCell2->addText('DETALLES DE FACTURA', $subHeaderStyle, $centerOptions);
        $detailsTable = $infoCell2->addTable(['width' => 100, 'unit' => 'pct']);

        $detailsTable->addRow();
        $detailsTable->addCell(2000)->addText('Fecha de emisión:', ['bold' => true]);
        $detailsTable->addCell(3000)->addText($invoice->invoice_date->format('d/m/Y'));

        $detailsTable->addRow();
        $detailsTable->addCell(3000)->addText('Fecha de vencimiento:', ['bold' => true]);
        $detailsTable->addCell(2000)->addText($invoice->due_date->format('d/m/Y'));

        $detailsTable->addRow();
        $detailsTable->addCell(2000)->addText('Moneda:', ['bold' => true]);
        $detailsTable->addCell(3000)->addText($invoice->currency);

        $detailsTable->addRow();
        $detailsTable->addCell(2000)->addText('Tipo de cambio:', ['bold' => true]);
        $detailsTable->addCell(3000)->addText(number_format($invoice->exchange_rate, 2));

        if ($invoice->quotation) {
            $detailsTable->addRow();
            $detailsTable->addCell(2000)->addText('Cotización:', ['bold' => true]);
            $detailsTable->addCell(3000)->addText($invoice->quotation->reference_number);
        }

        if ($invoice->billingNote) {
            $detailsTable->addRow();
            $detailsTable->addCell(2000)->addText('Nota de Cobranza:', ['bold' => true]);
            $detailsTable->addCell(3000)->addText($invoice->billingNote->op_number);
        }

        $section->addTextBreak(1);

        // Conceptos facturados
        $conceptsBanner = $section->addTable(['borderSize' => 0, 'cellMargin' => 0, 'width' => 100, 'unit' => 'pct']);
        $conceptsBanner->addRow(400);
        $conceptsBannerCell = $conceptsBanner->addCell(10000, ['bgColor' => 'E8EEF4', 'valign' => 'center', 'borderSize' => 1, 'borderColor' => '1F497D']);
        $conceptsBannerCell->addText('DETALLE DE FACTURACIÓN', $headerStyle, $centerOptions);

        $conceptsTable = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '1F497D',
            'cellMarginLeft' => 40,
            'cellMarginRight' => 40,
            'width' => 100,
            'unit' => 'pct',
        ]);

        $conceptsTable->addRow(400, ['bgColor' => '1F497D', 'tblHeader' => true]);
        $conceptsTable->addCell(500, $tableHeaderStyle)->addText('#', ['color' => 'FFFFFF'], $centerOptions);
        $conceptsTable->addCell(4000, $tableHeaderStyle)->addText('Descripción', ['color' => 'FFFFFF'], $centerOptions);
        $conceptsTable->addCell(1000, $tableHeaderStyle)->addText('Cantidad', ['color' => 'FFFFFF'], $centerOptions);
        $conceptsTable->addCell(1500, $tableHeaderStyle)->addText('Precio Unit.', ['color' => 'FFFFFF'], $centerOptions);
        $conceptsTable->addCell(1500, $tableHeaderStyle)->addText('Total', ['color' => 'FFFFFF'], $centerOptions);
        $conceptsTable->addCell(1500, $tableHeaderStyle)->addText('Total BS', ['color' => 'FFFFFF'], $centerOptions);

        $totalForeign = 0;
        $totalBs = 0;
        $itemsToShow = [];

        foreach ($invoice->items as $item) {
            $amount = $item->is_amount_parallel ? $item->amount_parallel : $item->amount;

            if ($isParallel) {
                if ($item->exchange_rate != $invoice->exchange_rate) {
                    $exchangeRateDifference = abs($invoice->exchange_rate - $item->exchange_rate);
                    $amountBs = $amount * $exchangeRateDifference;

                    $itemsToShow[] = [
                        'description' => $item->description,
                        'amount' => $amount,
                        'amountBs' => $amountBs,
                        'quantity' => $item->quantity,
                        'show' => true
                    ];
                    $totalForeign += $amount;
                    $totalBs += $amountBs;
                }
            } else {
                // Mostrar todos los items, convertir usando exchange_rate de billing_note
                $amountBs = $amount * $invoice->exchange_rate;
                $itemsToShow[] = [
                    'description' => $item->description,
                    'amount' => $amount,
                    'amountBs' => $amountBs,
                    'quantity' => $item->quantity,
                    'show' => true
                ];
                $totalForeign += $amount;
                $totalBs += $amountBs;
            }
        }

        $counter = 1;
        $rowCount = 0;
        foreach ($itemsToShow as $item) {
            if ($item['show']) {
                $bgColor = ($rowCount % 2 == 0) ? 'F2F6FC' : 'FFFFFF';
                $conceptsTable->addRow(350, ['bgColor' => $bgColor]);
                $conceptsTable->addCell(500)->addText($counter, null, $centerOptions);
                $conceptsTable->addCell(4000)->addText($item['description'], null, $paragraphOptions);
                $conceptsTable->addCell(1000)->addText($item['quantity'], null, $centerOptions);
                $conceptsTable->addCell(1500)->addText($invoice->currency . ' ' . number_format($item['amount'], 2), null, $rightOptions);
                $conceptsTable->addCell(1500)->addText($invoice->currency . ' ' . number_format($item['amount'], 2), null, $rightOptions);
                $conceptsTable->addCell(1500)->addText('BS ' . number_format($item['amountBs'], 2), null, $rightOptions);
                $counter++;
                $rowCount++;
            }
        }

        $section->addTextBreak(1);

        // Totales en ambas monedas
        $totalsTable = $section->addTable(['borderSize' => 0, 'cellMargin' => 0, 'width' => 100, 'unit' => 'pct']);
        $totalsTable->addRow();
        $totalsTable->addCell(5500);

        $totalsCell = $totalsTable->addCell(4500, [
            'bgColor' => 'F2F6FC',
            'borderSize' => 1,
            'borderColor' => '1F497D',
            'borderTopSize' => 3,
            'borderTopColor' => '1F497D',
            'valign' => 'center'
        ]);

        $totalInnerTable = $totalsCell->addTable(['borderSize' => 0, 'width' => 100, 'unit' => 'pct', 'cellMargin' => 40]);

        // Subtotales
        $totalInnerTable->addRow();
        $totalInnerTable->addCell(2000)->addText('Subtotal:', ['bold' => true], $rightOptions);

        if ($isParallel) {
            $totalInnerTable->addCell(2500)->addText($invoice->currency . ' ' . number_format($totalForeign, 2), null, $rightOptions);
            $totalInnerTable->addCell(2500)->addText('BS ' . number_format($totalBs, 2), null, $rightOptions);
        } else {
            $totalInnerTable->addCell(2500)->addText($invoice->currency . ' ' . number_format($invoice->subtotal, 2), null, $rightOptions);
            $totalInnerTable->addCell(2500)->addText('BS ' . number_format($invoice->subtotal * $invoice->exchange_rate, 2), null, $rightOptions);
        }

        $totalInnerTable->addRow();
        $totalInnerTable->addCell(2000)->addText('IVA (0%):', ['bold' => true], $rightOptions);
        $totalInnerTable->addCell(2500)->addText($invoice->currency . ' ' . number_format($invoice->tax_amount, 2), null, $rightOptions);
        $totalInnerTable->addCell(2500)->addText('BS ' . number_format($invoice->tax_amount * $invoice->exchange_rate, 2), null, $rightOptions);

        $totalInnerTable->addRow(400, ['bgColor' => 'E8EEF4']);
        $totalInnerTable->addCell(2000)->addText('TOTAL:', ['bold' => true, 'size' => 12, 'color' => '1F497D'], $rightOptions);
        $totalInnerTable->addCell(2500)->addText($invoice->currency . ' ' . number_format($totalForeign, 2), ['bold' => true, 'size' => 12, 'color' => '1F497D'], $rightOptions);
        $totalInnerTable->addCell(2500)->addText('BS ' . number_format($totalBs, 2), ['bold' => true, 'size' => 12, 'color' => '1F497D'], $rightOptions);

        $section->addTextBreak(1);

        if ($invoice->currency == 'USD') {
            $currencyInWords = 'DÓLARES AMERICANOS';
        } elseif ($invoice->currency == 'EUR') {
            $currencyInWords = 'EUROS';
        } else {
            $currencyInWords = strtoupper($invoice->currency);
        }

        $totalInWordsForeign = NumberToWordsConverter::convertToWords(
            $totalForeign,
            $currencyInWords
        );

        $totalInWordsBs = NumberToWordsConverter::convertToWords(
            $totalBs,
            'BOLIVIANOS'
        );

        // Tabla para los literales
        $literalTable = $section->addTable(['borderSize' => 1, 'cellMargin' => 20, 'width' => 100, 'unit' => 'pct']);

        $row = $literalTable->addRow();
        $cell = $row->addCell(10000, [
            'valign' => 'center',
            'gridSpan' => 3,
            'borderSize' => 10,
        ]);
        $textRun = $cell->addTextRun(['spaceAfter' => 0, 'spaceBefore' => 0, 'spacing' => 0]);
        $textRun->addText("Son: ", ['bold' => true, 'size' => 8, 'name' => 'Calibri']);
        $textRun->addText($totalInWordsForeign, [
            'size' => 8,
            'allCaps' => true
        ], [
            'spaceBefore' => 0,
            'spaceAfter' => 0,
            'spacing' => 0,
            'lineHeight' => 1.0,
            'align' => 'left',
        ]);

        $row = $literalTable->addRow();
        $cell = $row->addCell(10000, [
            'valign' => 'center',
            'gridSpan' => 3,
            'borderSize' => 10,
        ]);
        $textRun = $cell->addTextRun(['spaceAfter' => 0, 'spaceBefore' => 0, 'spacing' => 0]);
        $textRun->addText("Equivalente a: ", ['bold' => true, 'size' => 8, 'name' => 'Calibri']);
        $textRun->addText($totalInWordsBs, [
            'size' => 8,
            'allCaps' => true
        ], [
            'spaceBefore' => 0,
            'spaceAfter' => 0,
            'spacing' => 0,
            'lineHeight' => 1.0,
            'align' => 'left'
        ]);

        // Información de la empresa
        // $section->addText(
        //     'NOVALOGBO SRL',
        //     [
        //         'size' => 8,
        //         'bold' => true
        //     ],
        //     [
        //         'spaceBefore' => Converter::pointToTwip(8),
        //         'spaceAfter' => 0,
        //     ]
        // );
        // $section->addText(
        //     'NIT: 412B48023',
        //     [
        //         'size' => 8,
        //         'bold' => true
        //     ],
        //     [
        //         'spaceAfter' => 0,
        //         'spaceBefore' => 0,
        //     ]
        // );
        // $section->addText(
        //     'BANCO BISA',
        //     [
        //         'size' => 8,
        //         'bold' => true
        //     ],
        //     [
        //         'spaceAfter' => 0,
        //         'spaceBefore' => 0,
        //     ]
        // );
        // $section->addText(
        //     'BS: 7994826010',
        //     [
        //         'size' => 8,
        //         'bold' => true
        //     ],
        //     [
        //         'spaceAfter' => 0,
        //         'spaceBefore' => 0,
        //     ]
        // );
        // $section->addText(
        //     'BS: 7994829064',
        //     [
        //         'size' => 8,
        //         'bold' => true
        //     ],
        //     [
        //         'spaceAfter' => 0,
        //         'spaceBefore' => 0,
        //     ]
        // );

        // Generar el archivo
        $cleanRef = str_replace('/', '_', $invoice->invoice_number);
        $filename = "Factura_{$cleanRef}.docx";
        $tempFile = tempnam(sys_get_temp_dir(), 'PHPWord');
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }
}
