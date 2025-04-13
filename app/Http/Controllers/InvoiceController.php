<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\BillingNote;
use App\Models\InvoiceItem;
use App\Models\ExchangeRate;
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
    // ... (métodos index, create, show se mantienen igual)

    public function download(Request $request)
    {
        $validatedData = $request->validate([
            'quotation_id' => 'required|exists:quotations,id',
            'visible' => 'required|boolean'
        ]);
        $quotationId = $request->quotation_id;
        $visible = $request->visible ?? true;
        $quotation = Quotation::with(['customer', 'costDetails.cost'])->find($quotationId);
        DB::beginTransaction();
        try {
            $existingInvoice = Invoice::where('quotation_id', $quotationId)->first();
            if (!$existingInvoice) {
                $rateValue = $quotation->exchange_rate;
                $subtotal = 0;
                $taxRate = 0.13; // IVA de Bolivia (13%)

                foreach ($quotation->costDetails as $costDetail) {
                    $subtotal += $costDetail->amount;
                }

                $taxAmount = $subtotal * $taxRate;
                $totalAmount = $subtotal + $taxAmount;

                $invoice = new Invoice([
                    'invoice_number' => Invoice::generateInvoiceNumber(),
                    'invoice_date' => now(),
                    'due_date' => now()->addDays(30),
                    'subtotal' => $subtotal,
                    'tax_amount' => $taxAmount,
                    'total_amount' => $totalAmount,
                    'currency' => $quotation->currency,
                    'exchange_rate' => $rateValue,
                    'status' => 'issued',
                    'notes' => "Factura generada automáticamente de cotización #{$quotation->reference_number}",
                    'user_id' => Auth::id(),
                    'customer_nit' => $quotation->customer_nit,
                    'quotation_id' => $quotation->id,
                ]);

                $invoice->save();

                foreach ($quotation->costDetails as $costDetail) {
                    $itemSubtotal = $costDetail->amount;
                    $itemTaxAmount = $itemSubtotal * $taxRate;
                    $itemTotal = $itemSubtotal + $itemTaxAmount;

                    $invoiceItem = new InvoiceItem([
                        'invoice_id' => $invoice->id,
                        'cost_id' => $costDetail->cost_id,
                        'description' => $costDetail->concept,
                        'quantity' => 1,
                        'unit_price' => $itemSubtotal,
                        'tax_rate' => $taxRate * 100,
                        'tax_amount' => $itemTaxAmount,
                        'subtotal' => $itemSubtotal,
                        'total' => $itemTotal,
                        'currency' => $costDetail->currency,
                    ]);

                    $invoiceItem->save();
                }
            }

            $invoice = Invoice::where('quotation_id', $quotation->id)->first();
            DB::commit();

            return $this->generateWordDocument($invoice, $visible);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al crear la factura: ' . $e->getMessage()]);
        }
    }

    private function generateWordDocument($invoice, $visible)
    {
        $invoice->load(['customer', 'items.cost', 'quotation.products']);

        // Calcular totales en ambas monedas
        $totalForeign = $invoice->total_amount;
        $totalBs = $invoice->total_amount * $invoice->exchange_rate;
        $subtotalBs = $invoice->subtotal * $invoice->exchange_rate;
        $taxAmountBs = $invoice->tax_amount * $invoice->exchange_rate;

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
        $clientTable->addCell(3500)->addText($invoice->customer->name);

        $clientTable->addRow();
        $clientTable->addCell(1500)->addText('NIT:', ['bold' => true]);
        $clientTable->addCell(3500)->addText($invoice->customer->NIT);

        $clientTable->addRow();
        $clientTable->addCell(1500)->addText('Email:', ['bold' => true]);
        $clientTable->addCell(3500)->addText($invoice->customer->email);

        if ($invoice->customer->phone) {
            $clientTable->addRow();
            $clientTable->addCell(1500)->addText('Teléfono:', ['bold' => true]);
            $clientTable->addCell(3500)->addText($invoice->customer->phone);
        }

        if ($invoice->customer->address) {
            $clientTable->addRow();
            $clientTable->addCell(1500)->addText('Dirección:', ['bold' => true]);
            $clientTable->addCell(3500)->addText($invoice->customer->address);
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

        $section->addTextBreak(1);

        // Productos de la cotización
        if ($invoice->quotation && count($invoice->quotation->products) > 0) {
            $productsBanner = $section->addTable(['borderSize' => 0, 'cellMargin' => 0, 'width' => 100, 'unit' => 'pct']);
            $productsBanner->addRow(400);
            $productsBannerCell = $productsBanner->addCell(12000, ['bgColor' => 'E8EEF4', 'valign' => 'center', 'borderSize' => 1, 'borderColor' => '1F497D']);
            $productsBannerCell->addText('DETALLE DE PRODUCTOS', $headerStyle, $centerOptions);

            $productsTable = $section->addTable([
                'borderSize' => 6,
                'borderColor' => '1F497D',
                'cellMargin' => 30,
                'width' => 100,
                'unit' => 'pct',
            ]);

            $productsTable->addRow(400, ['bgColor' => '1F497D', 'tblHeader' => true]);
            $productsTable->addCell(2000, $tableHeaderStyle)->addText('Origen', ['color' => 'FFFFFF'], $centerOptions);
            $productsTable->addCell(2000, $tableHeaderStyle)->addText('Destino', ['color' => 'FFFFFF'], $centerOptions);
            $productsTable->addCell(1000, $tableHeaderStyle)->addText('Incoterm', ['color' => 'FFFFFF'], $centerOptions);
            $productsTable->addCell(1500, $tableHeaderStyle)->addText('Cantidad', ['color' => 'FFFFFF'], $centerOptions);
            $productsTable->addCell(1500, $tableHeaderStyle)->addText('Peso', ['color' => 'FFFFFF'], $centerOptions);
            $productsTable->addCell(2000, $tableHeaderStyle)->addText('Volumen', ['color' => 'FFFFFF'], $centerOptions);
            $productsTable->addCell(2000, $tableHeaderStyle)->addText('Nombre', ['color' => 'FFFFFF'], $centerOptions);

            $rowCount = 0;
            foreach ($invoice->quotation->products as $product) {
                $bgColor = ($rowCount % 2 == 0) ? 'F2F6FC' : 'FFFFFF';
                $productsTable->addRow(350, ['bgColor' => $bgColor]);
                $productsTable->addCell(2000)->addText($product->origin->name, null, $centerOptions);
                $productsTable->addCell(2000)->addText($product->destination->name, null, $centerOptions);
                $productsTable->addCell(1200)->addText($product->incoterm->code, null, $centerOptions);
                $productsTable->addCell(1500)->addText($product->quantity . ' ' . $product->quantityDescription->name, null, $centerOptions);
                $productsTable->addCell(1500)->addText($product->weight . ' kg', null, $centerOptions);
                $productsTable->addCell(1800)->addText($product->volume_unit == 'm3' ? $product->volume . ' ' . ' M3' : $product->volume . ' ' . ' KG/VOL', null, $centerOptions);
                $productsTable->addCell(1500)->addText($product->name ? $product->name : 'Sin Nombre', null, $centerOptions);
                $rowCount++;
            }
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

        $counter = 1;
        $rowCount = 0;
        foreach ($invoice->items as $item) {
            $bgColor = ($rowCount % 2 == 0) ? 'F2F6FC' : 'FFFFFF';
            $conceptsTable->addRow(350, ['bgColor' => $bgColor]);
            $conceptsTable->addCell(500)->addText($counter, null, $centerOptions);
            $conceptsTable->addCell(4000)->addText($item->cost->name, null, $paragraphOptions);
            $conceptsTable->addCell(1000)->addText($item->quantity, null, $centerOptions);
            $conceptsTable->addCell(1500)->addText($item->currency . ' ' . number_format($item->unit_price, 2), null, $rightOptions);
            $conceptsTable->addCell(1500)->addText($item->currency . ' ' . number_format($item->total, 2), null, $rightOptions);
            $conceptsTable->addCell(1500)->addText('BS ' . number_format($item->total * $invoice->exchange_rate, 2), null, $rightOptions);
            $counter++;
            $rowCount++;
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
        $totalInnerTable->addCell(2500)->addText($invoice->currency . ' ' . number_format($invoice->subtotal, 2), null, $rightOptions);
        $totalInnerTable->addCell(2500)->addText('BS ' . number_format($subtotalBs, 2), null, $rightOptions);

        $totalInnerTable->addRow();
        $totalInnerTable->addCell(2000)->addText('IVA (' . number_format($invoice->items->first()->tax_rate, 0) . '%):', ['bold' => true], $rightOptions);
        $totalInnerTable->addCell(2500)->addText($invoice->currency . ' ' . number_format($invoice->tax_amount, 2), null, $rightOptions);
        $totalInnerTable->addCell(2500)->addText('BS ' . number_format($taxAmountBs, 2), null, $rightOptions);

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

        // Generar el archivo
        $filename = 'factura-' . $invoice->invoice_number . '.docx';
        $tempFile = tempnam(sys_get_temp_dir(), 'PHPWord');
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }
}
