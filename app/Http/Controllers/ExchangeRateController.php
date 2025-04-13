<?php


namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ExchangeRate;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ExchangeRateController extends Controller
{
    public function index()
    {
        $exchangeRates = ExchangeRate::orderBy('date', 'desc')->get();
        return view('exchange-rates.index', compact('exchangeRates'));
    }

    public function create()
    {
        return view('exchange-rates.create');
    }
    public function store(Request $request)
    {

        $request->validate(
            [
                'source_currency' => 'required|string|max:10',
                'target_currency' => 'required|string|max:10',
                'rate' => 'required|numeric|min:0.00000001',
                'active' => 'boolean',
            ],
            [
                'source_currency.required' => 'El campo moneda origen es obligatorio',
                'source_currency.max' => 'La moneda origen debe tener más de 10 caracteres',
                'target_currency.required' => 'El campo moneda destino es obligatorio',
                'target_currency.size' => 'La moneda destino debe tener  más de 10 caracteres',
                'rate.required' => 'El campo tasa es obligatorio',
                'rate.numeric' => 'La tasa debe ser un número',
                'rate.min' => 'La tasa debe ser mayor que cero',
            ]
        );

        try {
            ExchangeRate::create([
                'source_currency' => strtoupper($request->source_currency),
                'target_currency' => strtoupper($request->target_currency),
                'rate' => $request->rate,
                'date' => Carbon::now(),
                'active' => $request->has('active'),
            ]);

            return redirect()->route('admin.exchange-rates.index')
                ->with('success', 'Tipo de cambio creado correctamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al crear el tipo de cambio: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $exchangeRate = ExchangeRate::findOrFail($id);
        if (!$exchangeRate) {
            return redirect()->route('exchange-rates.index')->with('error', 'Tipo de cambio no encontrada.');
        }
        return view('exchange-rates.show', compact('exchangeRate'));
    }

    public function edit($id)
    {
        $exchangeRate = ExchangeRate::findOrFail($id);
        if (!$exchangeRate) {
            return redirect()->route('exchange-rates.index')->with('error', 'Tipo de cambio no encontrada.');
        }
        return view('exchange-rates.edit', compact('exchangeRate'));
    }

    public function update(Request $request,  $id)
    {
        $exchangeRate = ExchangeRate::findOrFail($id);
        if (!$exchangeRate) {
            return redirect()->route('exchange-rates.index')->with('error', 'Tipo de cambio no encontrada.');
        }

        $request->validate(
            [
                'source_currency' => 'required|string|max:10',
                'target_currency' => 'required|string|max:10',
                'rate' => 'required|numeric|min:0.00000001',
                'active' => 'boolean',
            ],
            [
                'source_currency.required' => 'El campo moneda origen es obligatorio',
                'source_currency.max' => 'La moneda origen debe tener más de 10 caracteres',
                'target_currency.required' => 'El campo moneda destino es obligatorio',
                'target_currency.size' => 'La moneda destino debe tener  más de 10 caracteres',
                'rate.required' => 'El campo tasa es obligatorio',
                'rate.numeric' => 'La tasa debe ser un número',
                'rate.min' => 'La tasa debe ser mayor que cero',
            ]
        );
        try {
            $exchangeRate->update([
                'source_currency' => strtoupper($request->source_currency),
                'target_currency' => strtoupper($request->target_currency),
                'rate' => $request->rate,
                'date' => Carbon::now(),
                'active' => $request->has('active') ?? true,
            ]);

            return redirect()->route('exchange-rates.index')
                ->with('success', 'Tipo de cambio actualizado correctamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al actualizar el tipo de cambio: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        $exchangeRate = ExchangeRate::findOrFail($id);
        if (!$exchangeRate) {
            return redirect()->route('exchange-rates.index')->with('error', 'Tipo de cambio no encontrada.');
        }
        try {
            $exchangeRate->delete();
            return redirect()->route('exchange-rates.index')
                ->with('success', 'Tipo de cambio eliminado correctamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al eliminar el tipo de cambio: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        $exchangeRate = ExchangeRate::findOrFail($id);
        if (!$exchangeRate) {
            return redirect()->route('exchange-rates.index')->with('error', 'Tipo de cambio no encontrada.');
        }
        try {
            $exchangeRate->update([
                'active' => !$exchangeRate->active
            ]);

            $status = $exchangeRate->active ? 'activado' : 'desactivado';
            return redirect()->route('exchange-rates.index')
                ->with('success', "Tipo de cambio {$status} correctamente.");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al cambiar el estado: ' . $e->getMessage());
        }
    }
}
