<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportController extends Controller
{

    public function index()
    {
        return view('reports.index');
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
