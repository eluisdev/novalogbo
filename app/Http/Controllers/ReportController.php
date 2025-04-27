<?php

namespace App\Http\Controllers;

use App\Models\BillingNote;
use App\Models\Quotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{

    public function index()
    {
        return view('reports.index');
    }

    //METODO CREADO KIKE
    public function getQuotations()
    {
        if (Auth::user()->role_id === 1 || Auth::user()->role_id === 3) {
            $quotations = Quotation::with(['customer', 'user'])
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $quotations = Quotation::with(['customer'])
                ->where('users_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('admin.reports.quotations', compact('quotations'));
    }

    //METODO CREADO KIKE
    public function getOperations() {
        $billingNotes = BillingNote::with(['quotation.customer', 'user'])
        ->orderBy('created_at', 'desc')
        ->get();
        return view('admin.reports.operations', compact('billingNotes'));
    }

    public function create()
    {
        return view('reports.create');
    }

    public function store(Request $request)
    {
        // Aquí puedes manejar la lógica para almacenar el reporte
        // Por ejemplo, validar y guardar en la base de datos

        return redirect()->route('reports.index')->with('success', 'Reporte creado exitosamente.');
    }

    public function show($id)
    {
        // Aquí puedes manejar la lógica para mostrar un reporte específico
        return view('reports.show', compact('id'));
    }
}
