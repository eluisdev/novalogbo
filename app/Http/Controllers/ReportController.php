<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\BillingNote;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Carbon\Carbon;

class ReportController extends Controller
{
    // Método para mostrar el reporte de cotizaciones
    public function quotationsReport(Request $request)
    {
        // Obtener parámetros de filtrado del request
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $userId = $request->input('user_id');
        $search = $request->input('search');
        $status = $request->input('status');

        // dd($request->all());
        // Construir consulta base
        $query = Quotation::with(['customer', 'user'])
            ->orderBy('created_at', 'desc');


        // Aplicar filtros de fechas
        if ($dateFrom) {
            $query->where('created_at', '>=', Carbon::parse($dateFrom)->startOfDay());
        }
        if ($dateTo) {
            $query->where('created_at', '<=', Carbon::parse($dateTo)->endOfDay());
        }

        // Filtro por usuario
        if ($userId) {
            $query->where('users_id', $userId);
        }

        // Filtro por estado
        if ($status) {
            $query->where('status', $status);
        }

        // Búsqueda por referencia o CI/NIT
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('NIT', 'like', "%{$search}%");
                    });
            });
        }

        $quotations = $query->get();
        $users = User::whereIn('role_id', [1, 2, 3])->get(); // Solo comerciales y operadores

        // Totales para estadísticas
        $total = $quotations->count();
        $pending = $quotations->where('status', 'pending')->count();
        $accepted = $quotations->where('status', 'accepted')->count();
        $refused = $quotations->where('status', 'refused')->count();

        return view('admin.reports.quotations', compact(
            'quotations',
            'users',
            'total',
            'pending',
            'accepted',
            'refused',
            'dateFrom',
            'dateTo',
            'userId',
            'search',
            'status'
        ));
    }

    // Método para mostrar el reporte de operaciones
    public function operationsReport(Request $request)
    {
        // Obtener parámetros de filtrado del request
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $userId = $request->input('user_id');
        $search = $request->input('search');
        $status = $request->input('status');

        // Construir consulta base
        $query = BillingNote::with(['quotation.customer', 'user'])
            ->orderBy('created_at', 'desc');



        // Aplicar filtros de fechas
        if ($dateFrom) {
            $query->where('created_at', '>=', Carbon::parse($dateFrom)->startOfDay());
        }
        if ($dateTo) {
            $query->where('created_at', '<=', Carbon::parse($dateTo)->endOfDay());
        }

        // Filtro por usuario
        if ($userId) {
            $query->where('user_id', $userId);
        }

        // Filtro por estado
        if ($status) {
            $query->where('status', $status);
        }

        // Búsqueda por referencia o CI/NIT
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('op_number', 'like', "%{$search}%")
                    ->orWhereHas('quotation.customer', function ($q) use ($search) {
                        $q->where('NIT', 'like', "%{$search}%");
                    });
            });
        }

        $billingNotes = $query->get();
        $users = User::whereIn('role_id', [1, 2, 3])->get(); // Solo comerciales y operadores

         // Totales para estadísticas
         $total = $billingNotes->count();
         $pending = $billingNotes->where('status', 'pending')->count();
         $completed = $billingNotes->where('status', 'completed')->count();

        return view('admin.reports.operations', compact(
            'billingNotes',
            'users',
            'dateFrom',
            'dateTo',
            'userId',
            'search',
            'status',
            'total',
            'pending',
            'completed'
        ));
    }


    // Método para exportar cotizaciones a Excel
    public function exportQuotationsExcel(Request $request)
    {
        // Obtener los mismos filtros que en la vista
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $userId = $request->input('user_id');
        $search = $request->input('search');
        $status = $request->input('status');

        // Construir la misma consulta que en la vista
        $query = Quotation::with(['customer', 'user'])
            ->orderBy('created_at', 'desc');


        if ($dateFrom) {
            $query->where('created_at', '>=', Carbon::parse($dateFrom)->startOfDay());
        }
        if ($dateTo) {
            $query->where('created_at', '<=', Carbon::parse($dateTo)->endOfDay());
        }
        if ($userId) {
            $query->where('users_id', $userId);
        }
        if ($status) {
            $query->where('status', $status);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('NIT', 'like', "%{$search}%");
                    });
            });
        }

        $quotations = $query->get();

        // Crear el archivo Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Encabezados
        $sheet->setCellValue('A1', 'Usuario');
        $sheet->setCellValue('B1', 'Cliente');
        $sheet->setCellValue('C1', 'CI/NIT');
        $sheet->setCellValue('D1', 'N° Cotización');
        $sheet->setCellValue('E1', 'Costo Total');
        $sheet->setCellValue('F1', 'Moneda');
        $sheet->setCellValue('G1', 'Fecha de Creación');
        $sheet->setCellValue('H1', 'Estado');

        // Datos
        $row = 2;
        foreach ($quotations as $quotation) {
            $sheet->setCellValue('A' . $row, $quotation->user->name ?? 'N/A');
            $sheet->setCellValue('B' . $row, $quotation->customer->name);
            $sheet->setCellValue('C' . $row, $quotation->customer->NIT);
            $sheet->setCellValue('D' . $row, $quotation->reference_number);
            $sheet->setCellValue('E' . $row, $quotation->amount);
            $sheet->setCellValue('F' . $row, $quotation->currency);
            $sheet->setCellValue('G' . $row, $quotation->created_at->format('d/m/Y H:i'));
            $sheet->setCellValue('H' . $row, $this->getStatusText($quotation->status));
            $row++;
        }

        // Estilos
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet->getStyle('A1:H1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD9E1F2');

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Generar y descargar el archivo
        $writer = new Xlsx($spreadsheet);
        $filename = 'reporte_cotizaciones_' . now()->format('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }


    // Método para exportar operaciones a Excel
    public function exportOperationsExcel(Request $request)
    {
        // Obtener los mismos filtros que en la vista
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $userId = $request->input('user_id');
        $search = $request->input('search');
        $status = $request->input('status');

        // Construir la misma consulta que en la vista
        $query = BillingNote::with(['quotation.customer', 'user'])
            ->orderBy('created_at', 'desc');



        if ($dateFrom) {
            $query->where('created_at', '>=', Carbon::parse($dateFrom)->startOfDay());
        }
        if ($dateTo) {
            $query->where('created_at', '<=', Carbon::parse($dateTo)->endOfDay());
        }
        if ($userId) {
            $query->where('user_id', $userId);
        }
        if ($status) {
            $query->where('status', $status);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('op_number', 'like', "%{$search}%")
                    ->orWhereHas('quotation.customer', function ($q) use ($search) {
                        $q->where('NIT', 'like', "%{$search}%");
                    });
            });
        }

        $billingNotes = $query->get();

        // Crear el archivo Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Encabezados
        $sheet->setCellValue('A1', 'Usuario');
        $sheet->setCellValue('B1', 'Cliente');
        $sheet->setCellValue('C1', 'CI/NIT');
        $sheet->setCellValue('D1', 'N° Operación');
        $sheet->setCellValue('E1', 'N° Cotización');
        $sheet->setCellValue('F1', 'Monto');
        $sheet->setCellValue('G1', 'Moneda');
        $sheet->setCellValue('H1', 'Estado');
        $sheet->setCellValue('I1', 'Fecha');

        // Datos
        $row = 2;
        foreach ($billingNotes as $note) {
            $sheet->setCellValue('A' . $row, $note->user->name ?? 'N/A');
            $sheet->setCellValue('B' . $row, $note->quotation->customer->name);
            $sheet->setCellValue('C' . $row, $note->quotation->customer->NIT);
            $sheet->setCellValue('D' . $row, $note->op_number);
            $sheet->setCellValue('E' . $row, $note->quotation->reference_number);
            $sheet->setCellValue('F' . $row, $note->total_amount);
            $sheet->setCellValue('G' . $row, $note->currency);
            $sheet->setCellValue('H' . $row, $note->status == 'completed' ? 'Completado' : 'Pendiente');
            $sheet->setCellValue('I' . $row, $note->created_at->format('d/m/Y H:i'));
            $row++;
        }

        // Estilos
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);
        $sheet->getStyle('A1:I1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD9E1F2');

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Generar y descargar el archivo
        $writer = new Xlsx($spreadsheet);
        $filename = 'reporte_operaciones_' . now()->format('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    // Función auxiliar para convertir estados a texto
    private function getStatusText($status)
    {
        switch (strtolower($status)) {
            case 'pending':
                return 'Pendiente';
            case 'accepted':
                return 'Aceptada';
            case 'refused':
                return 'Rechazada';
            case 'completed':
                return 'Completado';
            default:
                return $status;
        }
    }
}
