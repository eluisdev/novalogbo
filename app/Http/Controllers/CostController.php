<?php

namespace App\Http\Controllers;

use App\Models\Cost;
use Illuminate\Http\Request;

class CostController extends Controller
{
    public function index()
    {
        $costs = Cost::all();
        return view('admin.costs.index' , compact('costs'));
    }

    public function create()
    {
        return view('admin.costs.create');
    }

    public function store(Request $request)
    {
        $request->validate(
            [
                'name' => 'required|string|max:255',
                'is_active' => 'required|boolean',
            ],
            [
                'name.required' => 'El nombre es obligatorio.',
                'name.string' => 'El nombre debe ser una cadena de texto.',
                'name.max' => 'El nombre no puede tener más de 255 caracteres.',
                'is_active.required' => 'El estado activo es obligatorio.',
                'is_active.boolean' => 'El estado activo debe ser verdadero o falso.',
            ]
        );

        Cost::create($request->all());

        return redirect()->route('costs.index')->with('success', 'Costo creado correctamente.');
    }

    public function edit($id)
    {
        $cost = Cost::findOrFail($id);
        if (!$cost) {
            return redirect()->route('costs.index')->with('error', 'Costo no encontrado.');
        }

        return view('admin.costs.edit', compact('cost'));
    }

    public function show($id)
    {
        $cost = Cost::findOrFail($id);
        if (!$cost) {
            return redirect()->route('costs.index')->with('error', 'Costo no encontrado..');
        }

        return view('admin.costs.show', compact('cost'));
    }
    public function update(Request $request, $id)
    {
        $cost = Cost::findOrFail($id);
        if (!$cost) {
            return redirect()->route('costs.index')->with('error', 'Costo no encontrado..');
        }

        $request->validate(
            [
                'name' => 'required|string|max:255',
                'is_active' => 'required|boolean',
            ],
            [
                'name.required' => 'El nombre es obligatorio.',
                'name.string' => 'El nombre debe ser una cadena de texto.',
                'name.max' => 'El nombre no puede tener más de 255 caracteres.',
                'is_active.required' => 'El estado activo es obligatorio.',
                'is_active.boolean' => 'El estado activo debe ser verdadero o falso.',
            ]
        );

        $cost->update($request->all());

        return redirect()->route('costs.index')->with('success', 'Costo actualizado correctamente.');
    }
    public function destroy($id)
    {
        $cost = Cost::findOrFail($id);
        if (!$cost) {
            return redirect()->route('costs.index')->with('error', 'Costo no encontrado.');
        }

        $cost->delete();

        return redirect()->route('costs.index')->with('success', 'Costo actualizado correctamente.');
    }
    public function toggleStatus($id){
        $cost = Cost::findOrFail($id);
        if (!$cost) {
            return redirect()->route('costs.index')->with('error', 'Costo no encontrado..');
        }

        $cost->is_active = !$cost->is_active;
        $cost->save();

        return redirect()->route('costs.index')->with('success', 'Costo estado actualizado correctamente.');
    }
}
