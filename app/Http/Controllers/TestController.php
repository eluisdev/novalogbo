<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\City;
use App\Models\Cost;
use GuzzleHttp\Client;
use App\Models\Service;
use App\Models\Customer;
use App\Models\Incoterm;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Html;
use App\Models\QuantityDescription;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Style\Language;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\SimpleType\JcTable;
use PhpOffice\PhpWord\SimpleType\TblWidth;

class TestController extends Controller
{

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
                    'name' => 'Product1',
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
        $costsData = $this->getCostsData($validated['logistic_costs']);

        $totalCost = array_reduce($costsData, function ($carry, $item) {
            return $carry + floatval(str_replace(',', '', $item['amount']));
        }, 0);
        $totalCostFormatted = number_format($totalCost, 2, ',', '.');
        $deliveryDate = Carbon::parse($validated['delivery_date'])->locale('es')->isoFormat('D [de] MMMM [de] YYYY');


        $phpWord = new PhpWord();
        // Configurar el idioma español para el documento
        $phpWord->getSettings()->setThemeFontLang(new Language(Language::ES_ES));

        // Establecer propiedades del documento en español
        $properties = $phpWord->getDocInfo();
        $properties->setTitle('Documento en Español');
        $properties->setCreator('NOVALOGISTIC BOLIVIA SRL');
        $properties->setCompany('NOVALOGISTIC BOLIVIA SRL');
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
            'No.007-2025',
            [
                'size' => 11,
                'bold' => true,
            ],
            ['spaceAfter' => 0, 'align' => 'right']
        );
        $section->addText(
            'OP-001-25', // NUMERO DE OPERACION
            [
                'size' => 11,
                'bold' => true,
            ],
            ['spaceAfter' => Converter::pointToTwip(11), 'align' => 'right']
        );

        $section->addText(
            'NOTA DE COBRANZA',
            ['size' => 11, 'underline' => 'single', 'bold' => true, 'allCaps' => true,],
            ['spaceAfter' => Converter::pointToTwip(15), 'align' => 'center']
        );
        // Estilos
        $fontStyle = [
            'name' => 'Montserrat',
            'size' => 11,
            'bold' => true
        ];

        $valueStyle = [
            'name' => 'Montserrat',
            'size' => 11,
            'bold' => false,
        ];

        // Estilo de párrafo sin espaciado
        $paragraphStyle = [
            'spaceAfter' => 0,
            'spaceBefore' => 0,
            'spacing' => 0
        ];
        // Crear una tabla que ocupe todo el ancho de la página
        $tableStyle = [
            'cellMargin' => 50, // Elimina todos los márgenes de las celdas
            'width' => 100, // 100% del ancho disponible
            'unit' => 'pct', // porcentaje
        ];

        $table = $section->addTable($tableStyle);

        // Primera fila: CLIENTE
        $row = $table->addRow(); // Altura exacta para controlar el espacio

        // CLIENTE (celda izquierda)
        $cell = $row->addCell(5000, ['valign' => 'bottom', 'gridSpan' => 2]);
        $textRun = $cell->addTextRun($paragraphStyle); // Aquí el estilo del párrafo
        $textRun->addText("CLIENTE: ", $fontStyle); // Aquí solo estilo de fuente
        $textRun->addText("\tICYS MEDICAL", $valueStyle); // Aquí solo estilo de fuente

        $row = $table->addRow();
        $cell = $row->addCell(5000, ['valign' => 'bottom', 'gridSpan' => 2]);
        $textRun = $cell->addTextRun($paragraphStyle);
        $textRun->addText("FECHA: ", $fontStyle);
        $textRun->addText("\t" . Carbon::now()->format('d/m/Y'), $valueStyle);


        $row = $table->addRow();
        $cell = $row->addCell(5000, ['valign' => 'bottom',]);
        $textRun = $cell->addTextRun([
            'spaceAfter' => Converter::pointToTwip(3),
            'spaceBefore' => 0,
            'spacing' => 0
        ]);
        $textRun->addText("TC: ", $fontStyle);
        $textRun->addText("\t\t6.96", $valueStyle);

        $cell = $row->addCell(5000, [
            'valign' => 'bottom',

        ]);
        $textRun = $cell->addTextRun([
            'spaceAfter' => Converter::pointToTwip(3),
            'spaceBefore' => 0,
            'spacing' => 0,
            'alignment' => 'right'
        ]);
        $textRun->addText("REF: ", $fontStyle);
        $textRun->addText("IM24180", $valueStyle);

        // Crear tabla de datos del envío
        $tableStyle = [
            'borderColor' => '000000',
            'cellMarginLeft' => 50,
            'cellMarginRight' => 50, // Elimina todos los márgenes internos de las celdas
            //'layout' => \PhpOffice\PhpWord\Style\Table::LAYOUT_FIXED,
            'width' => 100,
        ];
        $phpWord->addTableStyle('shipmentTable', $tableStyle);
        $table = $section->addTable('shipmentTable');

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
            'spacing' => 0, // Interlineado 1
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
            'spacing' => 0, // Interlineado 1
            'lineHeight' => 1.0,
            'align' => 'right'
        ]);
        $table->addCell(Converter::cmToTwip(4), [
            'valign' => 'center',
            'borderSize' => 10,
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
                'borderSize' => 10,
            ])->addText($cost['name'], [
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
                'borderSize' => 10,
            ])->addText(number_format($cost['amount'], 2, ',', '.'), [
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
                'borderSize' => 10,
            ])->addText(number_format($cost['amount'], 2, ',', '.'), [
                'size' => 11
            ], [
                'spaceBefore' => 0,
                'spaceAfter' => 0,
                'spacing' => 0,
                'lineHeight' => 1.0,
                'align' => 'right'
            ]);
        }

        $rowsToAdd = max(0, 18 - count($costsData));
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



        $table->addRow();
        $table->addCell(Converter::cmToTwip(10), [
            'valign' => 'center',
            'borderSize' => 10,
        ])->addText('TOTAL', [
            'size' => 11,
            'bold' => true,
            'allCaps' => true
        ], [
            'spaceBefore' => 0,
            'spaceAfter' => 0,
            'spacing' => 0, // Interlineado 1
            'lineHeight' => 1.0,
            'align' => 'right'
        ]);
        $table->addCell(Converter::cmToTwip(4), [
            'valign' => 'center',
            'borderSize' => 10,
        ])->addText($totalCostFormatted, [
            'size' => 11,
            'allCaps' => true,
            'bold' => true
        ], [
            'spaceBefore' => 0,
            'spaceAfter' => 0,
            'spacing' => 0, // Interlineado 1
            'lineHeight' => 1.0,
            'align' => 'right'
        ]);
        $table->addCell(Converter::cmToTwip(4), [
            'valign' => 'center',
            'borderSize' => 10,
        ])->addText($totalCostFormatted, [
            'size' => 11,
            'allCaps' => true,
            'bold' => true
        ], [
            'spaceBefore' => 0,
            'spaceAfter' => 0,
            'spacing' => 0, // Interlineado 1
            'lineHeight' => 1.0,
            'align' => 'right'
        ]);

        // Total con cellspan

        // Segunda fila: FECHA
        $row = $table->addRow();
        // FECHA (celda izquierda)
        $cell = $row->addCell(5000, [
            'valign' => 'center',
            'gridSpan' => 3,
            'valign' => 'center',
            'borderSize' => 10,
        ]);
        $textRun = $cell->addTextRun($paragraphStyle);
        $textRun->addText("Son: ", $fontStyle);
        $textRun->addText("\t" . Carbon::now()->format('d/m/Y'), [
            'size' => 11,
            'allCaps' => true
        ], [
            'spaceBefore' => 0,
            'spaceAfter' => 0,
            'spacing' => 0, // Interlineado 1
            'lineHeight' => 1.0,
            'align' => 'left'
        ]);


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


        // Guardar el documento
        $filename = 'NotaCobranza.docx';
        $tempFile = tempnam(sys_get_temp_dir(), 'PHPWord');
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempFile);
        // Descargar el archivo
        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }

    // public function generarCotizacion0(Request $request)
    // {
    //     $templatePath = storage_path('app/templates/cotizacion_base0.docx');
    //     $templateProcessor = new TemplateProcessor($templatePath);

    //     $text = new TextRun();
    //     $text->addText(
    //         'Aqui debe ir la fecha actual de envío',
    //         array(
    //             'size' => 11,
    //             'name' => 'Montserrat'
    //         ),
    //         array(
    //             'spaceBefore' => Converter::pointToTwip(11),
    //             'spaceAfter' => Converter::pointToTwip(11),
    //             'align' => 'right'
    //         )
    //     );
    //     $templateProcessor->setComplexValue('date', $text);

    //     $text = new TextRun();
    //     $text->addText(
    //         'Señores',
    //         array(
    //             'size' => 11,
    //             'name' => 'Montserrat'
    //         ),
    //         array(
    //             'spaceBefore' => Converter::pointToTwip(11),
    //             'spaceAfter' => Converter::pointToTwip(11)
    //         )
    //     );
    //     $templateProcessor->setComplexValue('se', $text);

    //     $text = new TextRun();
    //     $text->addText(
    //         'NOMBRE DEL CLIENTE O RAZON SOCIAL',
    //         array(
    //             'size' => 11,
    //             'name' => 'Montserrat',
    //             'bold' => true,
    //             'allCaps' => true
    //         ),
    //         array(
    //             'spaceBefore' => Converter::pointToTwip(11),
    //             'spaceAfter' => Converter::pointToTwip(11)
    //         )
    //     );

    //     $templateProcessor->setComplexValue('name', $text);

    //     $text = new TextRun();
    //     $text->addText(
    //         'Presente. -',
    //         array(
    //             'size' => 11,
    //             'name' => 'Montserrat'
    //         ),
    //         array(
    //             'spaceBefore' => Converter::pointToTwip(11),
    //             'spaceAfter' => Converter::pointToTwip(11)
    //         )
    //     );

    //     $templateProcessor->setComplexValue('pr', $text);

    //     $text = new TextRun();
    //     $text->addText(
    //         'REF: COTIZACION 016/25',
    //         array(
    //             'size' => 11,
    //             'name' => 'Montserrat',
    //             'underline' => 'single',
    //             'bold' => true,
    //             'allCaps' => true
    //         ),
    //         array(
    //             'spaceBefore' => Converter::pointToTwip(11),
    //             'spaceAfter' => Converter::pointToTwip(11)
    //         )
    //     );

    //     $templateProcessor->setComplexValue('ref', $text);

    //     $text = new TextRun();
    //     $text->addText(
    //         'Estimado cliente, por medio la presente tenemos el agrado de enviarle nuestra cotización de acuerdo con su requerimiento e información proporcionada.',
    //         array(
    //             'size' => 11,
    //             'name' => 'Montserrat'
    //         ),
    //         array(
    //             'spaceBefore' => Converter::pointToTwip(11),
    //             'spaceAfter' => Converter::pointToTwip(11)
    //         )
    //     );
    //     $templateProcessor->setComplexValue('refText', $text);

    //     // Crear tabla de datos del envío
    //     $tableStyle = [
    //         'borderColor' => '000000',
    //         'cellMarginLeft' => 50,
    //         'cellMarginRight' => 50, // Elimina todos los márgenes internos de las celdas
    //         //'cellMargin' => 50,
    //         //'layout' => \PhpOffice\PhpWord\Style\Table::LAYOUT_FIXED,
    //         'unit' => TblWidth::TWIP
    //     ];

    //     $table = new Table($tableStyle);

    //     // Estilo de párrafo compacto
    //     $compactParagraphStyle = [
    //         'spaceBefore' => 0,
    //         'spaceAfter' => 0,
    //         'spacing' => 0, // Interlineado 1
    //         'lineHeight' => 1.0
    //     ];

    //     // Primera fila de la tabla
    //     $table->addRow(500);
    //     $table->addCell(Converter::cmToTwip(3), [
    //         'valign' => 'center',
    //         'bgColor' => 'bdd6ee',
    //         'borderSize' => 1,
    //     ])->addText('CLIENTE', [
    //         'bold' => true,
    //         'size' => 11,
    //         'allCaps' => true,
    //         'name' => 'Montserrat'
    //     ], $compactParagraphStyle);

    //     $table->addCell(Converter::cmToTwip(7), [
    //         'valign' => 'center',
    //         'borderSize' => 1,
    //     ])->addText('NOMBRE DEL CLIENTE O RAZON SOCIAL', [
    //         'bold' => true,
    //         'allCaps' => true,
    //         'size' => 11,
    //         'name' => 'Montserrat'
    //     ], $compactParagraphStyle);

    //     // Fila vacía (0.5 cm de ancho) - RESTAURADA
    //     $table->addCell(Converter::cmToTwip(0.5), [
    //         'valign' => 'center',
    //     ]);

    //     // Segunda fila
    //     $table->addRow(Converter::cmToTwip(1.7));
    //     $table->addCell(Converter::cmToTwip(3), [
    //         'valign' => 'center',
    //         'bgColor' => 'bdd6ee',
    //         'borderSize' => 1,
    //     ])->addText('ORIGEN', [
    //         'bold' => true,
    //         'size' => 11,
    //         'name' => 'Montserrat'
    //     ], $compactParagraphStyle);

    //     $table->addCell(Converter::cmToTwip(7), [
    //         'valign' => 'center',
    //         'borderSize' => 1,
    //     ])->addText('CHINA , YANTIAN', [
    //         'size' => 11,
    //         'name' => 'Montserrat'
    //     ], $compactParagraphStyle);

    //     // Fila vacía (0.5 cm de ancho) - RESTAURADA
    //     $table->addCell(Converter::cmToTwip(0.5), [
    //         'valign' => 'center',
    //     ]);

    //     $table->addCell(Converter::cmToTwip(2), [
    //         'valign' => 'bottom',
    //         'bgColor' => 'bdd6ee',
    //         'borderSize' => 1,

    //     ])->addText('CANTIDAD', [
    //         'bold' => true,
    //         'size' => 11,
    //         'name' => 'Montserrat'
    //     ], $compactParagraphStyle);

    //     $table->addCell(Converter::cmToTwip(3), [
    //         'valign' => 'bottom',
    //         'borderSize' => 1,
    //     ])->addText('1x40', [
    //         'size' => 11,
    //         'name' => 'Montserrat'
    //     ], $compactParagraphStyle);

    //     // Tercera fila
    //     $table->addRow(Converter::cmToTwip(1.7));
    //     $table->addCell(Converter::cmToTwip(3), [
    //         'valign' => 'center',
    //         'bgColor' => 'bdd6ee',
    //         'borderSize' => 1,
    //     ])->addText('DESTINO', [
    //         'bold' => true,
    //         'size' => 11,
    //         'name' => 'Montserrat'
    //     ], $compactParagraphStyle);

    //     $table->addCell(Converter::cmToTwip(7), [
    //         'valign' => 'center',
    //         'borderSize' => 1,
    //     ])->addText('CHILE - IQUIQUE', [
    //         'size' => 11,
    //         'name' => 'Montserrat'
    //     ], $compactParagraphStyle);

    //     // Fila vacía (0.5 cm de ancho) - RESTAURADA
    //     $table->addCell(Converter::cmToTwip(0.5), [
    //         'valign' => 'center',
    //     ]);

    //     $table->addCell(Converter::cmToTwip(2), [
    //         'valign' => 'bottom',
    //         'bgColor' => 'bdd6ee',
    //         'borderSize' => 1,
    //     ])->addText('PESO', [
    //         'bold' => true,
    //         'size' => 11,
    //         'name' => 'Montserrat'
    //     ], $compactParagraphStyle);

    //     $table->addCell(Converter::cmToTwip(3), [
    //         'valign' => 'bottom',
    //         'borderSize' => 1,
    //     ])->addText('20.000 KG', [
    //         'size' => 11,
    //         'name' => 'Montserrat'
    //     ], $compactParagraphStyle);

    //     // Cuarta fila
    //     $table->addRow();
    //     $table->addCell(Converter::cmToTwip(3), [
    //         'valign' => 'center',
    //         'bgColor' => 'bdd6ee',
    //         'borderSize' => 1,
    //     ])->addText('INCOTERM', [
    //         'bold' => true,
    //         'size' => 11,
    //         'name' => 'Montserrat'
    //     ], $compactParagraphStyle, [
    //         'spaceBefore' => 0,
    //         'spaceAfter' => 0
    //     ]);

    //     $table->addCell(Converter::cmToTwip(7), [
    //         'valign' => 'center',
    //         'borderSize' => 1,
    //     ])->addText('FOB', [
    //         'size' => 11,
    //         'name' => 'Montserrat'
    //     ], $compactParagraphStyle, [
    //         'spaceBefore' => 0,
    //         'spaceAfter' => 0
    //     ]);

    //     // Fila vacía (0.5 cm de ancho) - RESTAURADA
    //     $table->addCell(Converter::cmToTwip(0.5), [
    //         'valign' => 'center',
    //     ])->addText('', [
    //         'spaceBefore' => 0,
    //         'spaceAfter' => 0
    //     ]);

    //     $table->addCell(Converter::cmToTwip(2), [
    //         'valign' => 'center',
    //         'bgColor' => 'bdd6ee',
    //         'borderSize' => 1,
    //     ])->addText('M3', [
    //         'bold' => true,
    //         'size' => 11,
    //         'name' => 'Montserrat'
    //     ], $compactParagraphStyle, [
    //         'spaceBefore' => 0,
    //         'spaceAfter' => 0
    //     ]);

    //     $table->addCell(Converter::cmToTwip(3), [
    //         'valign' => 'center',
    //         'borderSize' => 1,
    //     ])->addText('60 M3', [
    //         'size' => 11,
    //         'name' => 'Montserrat'
    //     ], $compactParagraphStyle, [
    //         'spaceBefore' => 0,
    //         'spaceAfter' => 0
    //     ]);


    //     $templateProcessor->setComplexBlock('table', $table);

    //     $req = new TextRun();
    //     $req->addText(
    //         'Para el requerimiento de transporte y logística los costos se encuentran líneas abajo',
    //         array(
    //             'size' => 11,
    //             'name' => 'Montserrat'
    //         ),
    //         array(
    //             'spaceBefore' => Converter::pointToTwip(11),
    //             'spaceAfter' => Converter::pointToTwip(11),
    //         )
    //     );
    //     $templateProcessor->setComplexValue('req', $req);

    //     $text = new TextRun();
    //     $text->addText(
    //         'OPCION 1) PAGO EN EFECTIVO EN BS DE EN BOLIVIA',
    //         [
    //             'size' => 11,
    //             'name' => 'Montserrat',
    //             'bold' => true
    //         ],
    //         [
    //             'spaceBefore' => Converter::pointToTwip(11)
    //         ]
    //     );
    //     $templateProcessor->setComplexValue('pag', $text);

    //     // Texto después de la tabla

    //     $table = new Table([
    //         'width' => 400,
    //         'unit' => 'pct',
    //         'alignment' => JcTable::CENTER,
    //         'cellMargin' => 50,
    //     ]);
    //     // Primera fila de la tabla
    //     $table->addRow();
    //     $table->addCell(Converter::cmToTwip(10), [
    //         'valign' => 'center',
    //         'bgColor' => 'bdd6ee',
    //         'borderSize' => 1,
    //     ])->addText('CONCEPTO', [
    //         'bold' => true,
    //         'size' => 11,
    //         'allCaps' => true,
    //         'name' => 'Montserrat'
    //     ], [
    //         'spaceBefore' => 0,
    //         'spaceAfter' => 0,
    //         'spacing' => 0, // Interlineado 1
    //         'lineHeight' => 1.0,
    //         'align' => 'center'
    //     ]);
    //     $table->addCell(Converter::cmToTwip(3), [
    //         'valign' => 'center',
    //         'bgColor' => 'bdd6ee',
    //         'borderSize' => 1,
    //     ])->addText('MONTO USD', [
    //         'bold' => true,
    //         'size' => 11,
    //         'allCaps' => true,
    //         'name' => 'Montserrat'
    //     ], [
    //         'spaceBefore' => 0,
    //         'spaceAfter' => 0,
    //         'spacing' => 0, // Interlineado 1
    //         'lineHeight' => 1.0,
    //         'align' => 'right'
    //     ]);


    //     // Datos de la tabla de costos
    //     $costos = [
    //         'FLETE MARITIMO YANTIAN -- IQUIQUE' => '2.400,00',
    //         'THC EN PUERTO DE ORIGEN' => '0,00',
    //         'THC EN PUERTO DE DESTINO' => '0,00',
    //         'EMISION DE BL' => '0,00',
    //         'SEGUIMIENTO E INFORMACION CONSTANTE' => '0,00',
    //         'CARGOS EN PUERTO DE DESTINO' => '0,00',
    //     ];

    //     foreach ($costos as $concepto => $monto) {
    //         $table->addRow();
    //         $table->addCell(Converter::cmToTwip(10), [
    //             'valign' => 'center',
    //             'borderSize' => 1,
    //         ])->addText($concepto, [
    //             'size' => 11,
    //             'name' => 'Montserrat'
    //         ], [
    //             'spaceBefore' => 0,
    //             'spaceAfter' => 0,
    //             'spacing' => 0, // Interlineado 1
    //             'lineHeight' => 1.0,
    //             'align' => 'left'
    //         ]);
    //         $table->addCell(Converter::cmToTwip(3), [
    //             'valign' => 'center',
    //             'borderSize' => 1,
    //         ])->addText($monto, [
    //             'size' => 11,
    //             'name' => 'Montserrat'
    //         ], [
    //             'spaceBefore' => 0,
    //             'spaceAfter' => 0,
    //             'spacing' => 0, // Interlineado 1
    //             'lineHeight' => 1.0,
    //             'align' => 'right'
    //         ]);
    //     }



    //     $table->addRow();
    //     $table->addCell(Converter::cmToTwip(10), [
    //         'valign' => 'center',
    //         'borderSize' => 1,
    //     ])->addText('TOTAL', [
    //         'size' => 11,
    //         'allCaps' => true,
    //         'name' => 'Montserrat'
    //     ], [
    //         'spaceBefore' => 0,
    //         'spaceAfter' => 0,
    //         'spacing' => 0, // Interlineado 1
    //         'lineHeight' => 1.0,
    //         'align' => 'left'
    //     ]);
    //     $table->addCell(Converter::cmToTwip(3), [
    //         'valign' => 'center',
    //         'borderSize' => 1,
    //     ])->addText('2.400,00', [
    //         'size' => 11,
    //         'allCaps' => true,
    //         'name' => 'Montserrat'
    //     ], [
    //         'spaceBefore' => 0,
    //         'spaceAfter' => 0,
    //         'spacing' => 0, // Interlineado 1
    //         'lineHeight' => 1.0,
    //         'align' => 'right'
    //     ]);

    //     $templateProcessor->setComplexBlock('tableC', $table);



    //     // Crear el TextRun
    //     $textRun = new TextRun();

    //     $textRun->addText(
    //         '** De acuerdo con el TC paralelo vigente.',
    //         [
    //             'size' => 11,
    //             'bold' => true,
    //             'name' => 'Montserrat',
    //         ]
    //     );
    //     $templateProcessor->setComplexValue('TC', $textRun);

    //     // Agregar el TextRun al TemplateProcessor
    //     $text = new TextRun();

    //     $text->addText(
    //         'El servicio incluye:',
    //         array(
    //             'size' => 11,
    //             'name' => 'Montserrat',
    //             'bold' => true
    //         ),
    //     );
    //     $templateProcessor->setComplexValue('inc', $text);















    //     // 6. Insertar en el template usando setComplexBlock


    //     $html = '<p><span style="background-color: #ff0000;">BugTracker X</span> is ${facing1} an issue.</p>';
    //     $section = new Section(0);
    //     Html::addHtml($section, $html, false, false);
    //     $templateProcessor->setComplexBlock('sinc', $section);























    //     $text = new TextRun();
    //     $text->addText(
    //         'El servicio no incluye:',
    //         array(
    //             'size' => 11,
    //             'name' => 'Montserrat',
    //             'bold' => true

    //         ),
    //     );

    //     $templateProcessor->setComplexValue('ninc', $text);


    //     $paragraphStyle = array(
    //         'spaceAfter' => Converter::pointToTwip(11),
    //         'spaceBefore' => Converter::pointToTwip(11),
    //     );

    //     $text = new TextRun($paragraphStyle);
    //     $text->addText(
    //         'Seguro: ',
    //         [
    //             'bold' => true,
    //             'size' => 11,
    //             'name' => 'Montserrat'
    //         ]
    //     );
    //     $text->addText(
    //         'Se recomienda tener una póliza de seguro para el embarque, ofrecemos la misma de manera adicional considerando el 0.35% sobre el valor declarado, con un min de 30 usd, previa autorización por la compañía de seguros.',
    //         [
    //             'size' => 11,
    //             'name' => 'Montserrat'
    //         ]
    //     );

    //     $templateProcessor->setComplexValue('seg', $text);

    //     $text = new TextRun();
    //     $text->addText(
    //         'Forma de pago: ',
    //         [
    //             'bold' => true,
    //             'size' => 11,
    //             'name' => 'Montserrat'
    //         ]
    //     );
    //     $text->addText(
    //         'Una vez se confirme el arribo del embarque a puerto de destino.',
    //         [
    //             'size' => 11,
    //             'name' => 'Montserrat'
    //         ]
    //     );

    //     $templateProcessor->setComplexValue('FP', $text);

    //     $text = new TextRun();
    //     $text->addText(
    //         'Validez: ',
    //         [
    //             'bold' => true,
    //             'size' => 11,
    //             'name' => 'Montserrat'
    //         ]
    //     );
    //     $text->addText(
    //         'Los fletes son válidos hasta 10 días, posterior a ese tiempo, validar si los costos aún están vigentes.',
    //         [
    //             'size' => 11,
    //             'name' => 'Montserrat'
    //         ]
    //     );
    //     $templateProcessor->setComplexValue('V', $text);

    //     $text = new TextRun();
    //     $text->addText(
    //         'Observaciones: ',
    //         [
    //             'bold' => true,
    //             'size' => 11,
    //             'name' => 'Montserrat'
    //         ]
    //     );
    //     $text->addText(
    //         'Se debe considerar como un tiempo de tránsito 48 a 50 días hasta puerto de Iquique.',
    //         [
    //             'size' => 11,
    //             'name' => 'Montserrat'
    //         ]
    //     );

    //     $templateProcessor->setComplexValue('O', $text);

    //     $text = new TextRun();
    //     $text->addText(
    //         'Atentamente:',
    //         array(
    //             'size' => 11,
    //             'name' => 'Montserrat'
    //         ),
    //         array(
    //             'spaceBefore' => Converter::pointToTwip(11),
    //             'spaceAfter' => Converter::pointToTwip(11)
    //         )
    //     );

    //     $templateProcessor->setComplexValue('A', $text);

    //     $text = new TextRun();
    //     $text->addText(
    //         'NOMBRE DEL REMITENTE',
    //         array(
    //             'size' => 11,
    //             'name' => 'Montserrat',
    //             'bold' => true
    //         ),
    //         array(
    //             'spaceBefore' => Converter::pointToTwip(11),
    //             'spaceAfter' => Converter::pointToTwip(11)
    //         )
    //     );

    //     $templateProcessor->setComplexValue('rep', $text);

    //     $text = new TextRun();
    //     $text->addText(
    //         'Responsable Comercial',
    //         array(
    //             'size' => 11,
    //             'name' => 'Montserrat',
    //             'bold' => true
    //         ),
    //         array(
    //             'spaceBefore' => Converter::pointToTwip(11),
    //             'spaceAfter' => Converter::pointToTwip(11)
    //         )
    //     );
    //     $templateProcessor->setComplexValue('c', $text);


    //     // 5. Guardar y descargar documento

    //     $filename = 'cotizacion_final.docx';
    //     $tempFile = tempnam(sys_get_temp_dir(), 'PHPWord');
    //     $templateProcessor->saveAs($tempFile);

    //     return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    // }

    /**
     * Obtiene datos del cliente por NIT
     */
}
