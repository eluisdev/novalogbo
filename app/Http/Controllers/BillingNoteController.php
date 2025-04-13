<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Quotation;
use App\Models\BillingNote;
use App\Models\ExchangeRate;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\PhpWord;
use App\Models\BillingNoteItem;
use PhpOffice\PhpWord\IOFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpWord\Style\Language;
use PhpOffice\PhpWord\Shared\Converter;
use App\Helpers\NumberToWordsConverter;

class BillingNoteController extends Controller
{
    public function download(Request $request)
    {
        $request->validate(
            [
                'quotation_id' => 'required|integer',
                'visible' => 'required|boolean'
            ],
        );
        $quotationId = $request->quotation_id;
        $visible = $request->visible ?? true;
        $quotation = Quotation::with(['customer', 'costDetails.cost'])->findOrFail($quotationId);

        DB::beginTransaction();

        try {
            // Verificar si ya existe una nota de cobranza para esta cotización
            $billingNote = BillingNote::where('quotation_id', $quotationId)->first();

            if (!$billingNote) {
                $year = Carbon::now()->format('y');
                $sequence = BillingNote::whereYear('created_at', Carbon::now()->year)->count() + 1;
                $sequenceFormatted = str_pad($sequence, 3, '0', STR_PAD_LEFT);

                $numbers = [
                    'op_number' => "OP-{$sequenceFormatted}-{$year}",
                    'note_number' => "No-{$sequenceFormatted}-{$year}"
                ];

                // Función para verificar la unicidad de un número
                $checkUnique = function ($number, $field) use ($year) {
                    return !BillingNote::where($field, $number)
                        ->whereYear('created_at', Carbon::now()->year)
                        ->exists();
                };

                // Verificar y generar nuevos números si no son únicos
                $maxAttempts = 100; // Para evitar bucles infinitos en casos extremos
                $attempts = 0;

                while (!$checkUnique($numbers['op_number'], 'op_number') || !$checkUnique($numbers['note_number'], 'note_number')) {
                    $sequence++;
                    $sequenceFormatted = str_pad($sequence, 3, '0', STR_PAD_LEFT);
                    $numbers['op_number'] = "OP-{$sequenceFormatted}-{$year}";
                    $numbers['note_number'] = "No-{$sequenceFormatted}-{$year}";

                    $attempts++;
                    if ($attempts > $maxAttempts) {
                        // Log de un error o lanzar una excepción si se excede el número de intentos
                        error_log("Error al generar números de nota únicos después de {$maxAttempts} intentos.");
                        // Puedes devolver un valor por defecto o lanzar una excepción aquí
                        $numbers = [
                            'op_number' => null,
                            'note_number' => null
                        ];
                        break;
                    }
                }

                // Crear nueva nota de cobranza
                $billingNote = BillingNote::create([
                    'op_number' => $numbers['op_number'],
                    'note_number' => $numbers['note_number'],
                    'emission_date' => Carbon::now(),
                    'total_amount' => $quotation->costDetails->sum('amount'),
                    'currency' => $quotation->currency,
                    'exchange_rate' => $quotation->exchange_rate,
                    'user_id' => Auth::id(),
                    'quotation_id' => $quotation->id,
                    'customer_nit' => $quotation->customer_nit,
                ]);

                // Crear items de la nota
                foreach ($quotation->costDetails as $costDetail) {
                    BillingNoteItem::create([
                        'billing_note_id' => $billingNote->id,
                        'cost_id' => $costDetail->cost_id,
                        'description' => $costDetail->cost->name,
                        'amount' => $costDetail->amount,
                        'currency' => $costDetail->currency
                    ]);
                }
                $billingNote = BillingNote::where('quotation_id', $quotationId)->first();
            }

            // Actualizar estado de la cotización
            $quotation->update(['status' => 'completed']);

            DB::commit();

            // Generar y descargar el documento Word
            return $this->generateWordDocument($billingNote, $visible);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al generar la nota de cobranza: ' . $e->getMessage());
        }
    }

    private function generateWordDocument(BillingNote $billingNote, $visible)
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

        // Encabezados de la tabla
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

        // Conceptos
        foreach ($billingNote->items as $item) {
            $table->addRow();
            $table->addCell(Converter::cmToTwip(10), [
                'valign' => 'center',
                'borderSize' => 10,
            ])->addText(
                $item->description,
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
                number_format($item->amount * $billingNote->exchange_rate, 2, ',', '.'),
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
                number_format($item->amount, 2, ',', '.'),
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

        // Rellenar filas vacías si es necesario
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

        // Total
        $totalBs = $billingNote->total_amount * $billingNote->exchange_rate;
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
            number_format($billingNote->total_amount, 2, ',', '.'),
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
        $totalInWordsForeign = NumberToWordsConverter::convertToWords(
            $billingNote->total_amount,
            strtoupper($billingNote->currency == 'USD' ? 'DÓLARES AMERICANOS' : 'EUROS')
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
        $filename = "Nota_Cobranza_{$billingNote->note_number}.docx";
        $tempFile = tempnam(sys_get_temp_dir(), 'PHPWord');
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }
}
