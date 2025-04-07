<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\City;
use App\Models\Cost;
use GuzzleHttp\Client;
use App\Models\Service;
use App\Models\Customer;
use App\Models\Incoterm;
use App\Models\QuantityDescription;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Html;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\SimpleType\JcTable;
use PhpOffice\PhpWord\SimpleType\TblWidth;

class CotizacionController extends Controller
{

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
