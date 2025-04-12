<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\QuantityDescription;

class QuantityDescriptionController extends Controller
{
    public function index()
    {
        $quantityDescriptions = QuantityDescription::all();
        return view('quantity_descriptions.index', compact('quantityDescriptions'));
    }

    public function create()
    {
        return view('quantity_descriptions.create');
    }

    public function store(Request $request)
    {
        $request->validate(
            [
                'name' => 'required|string|max:255|unique:quantity_descriptions',
                'is_active' => 'boolean',
            ],
            [
                'name.required' => 'El nombre es requerido.',
                'name.string' => 'El nombre debe ser una cadena de texto.',
                'name.max' => 'El nombre no puede tener más de 255 caracteres.',
                'is_active.boolean' => 'El estado debe ser verdadero o falso.',
            ]
        );
        QuantityDescription::create($request->all());
        return redirect()->route('quantity_descriptions.index')->with('success', 'Descripción de cantidad creada exitosamente.');
    }
    public function show($id)
    {
        $quantityDescription = QuantityDescription::findOrFail($id);
        if (!$quantityDescription) {
            return redirect()->route('quantity_descriptions.index')->with('error', 'Descripción de cantidad no encontrada.');
        }
        return view('quantity_descriptions.show', compact('quantityDescription'));
    }
    public function edit($id)
    {
        $quantityDescription = QuantityDescription::findOrFail($id);
        if (!$quantityDescription) {
            return redirect()->route('quantity_descriptions.index')->with('error', 'Descripción de cantidad no encontrada.');
        }
        return view('quantity_descriptions.edit', compact('quantityDescription'));
    }
    public function update(Request $request, $id)
    {
        $quantityDescription = QuantityDescription::findOrFail($id);
        if (!$quantityDescription) {
            return redirect()->route('quantity_descriptions.index')->with('error', 'Descripción de cantidad no encontrada.');
        }
        $request->validate(
            [
                'name' => 'required|string|max:255|unique:quantity_descriptions,name,' . $id,
                'is_active' => 'boolean',
            ],
            [
                'name.required' => 'El nombre es requerido.',
                'name.string' => 'El nombre debe ser una cadena de texto.',
                'name.max' => 'El nombre no puede tener más de 255 caracteres.',
                'is_active.boolean' => 'El estado debe ser verdadero o falso.',
            ]
        );
        $quantityDescription->update($request->all());
        return redirect()->route('quantity_descriptions.index')->with('success', 'Descripción de cantidad actualizada exitosamente.');
    }
    public function destroy($id)
    {
        $quantityDescription = QuantityDescription::findOrFail($id);
        if (!$quantityDescription) {
            return redirect()->route('quantity_descriptions.index')->with('error', 'Descripción de cantidad no encontrada.');
        }
        $quantityDescription->delete();
        return redirect()->route('quantity_descriptions.index')->with('success', 'Descripción de cantidad eliminada exitosamente.');
    }
    public function toggleStatus($id)
    {
        $quantityDescription = QuantityDescription::findOrFail($id);
        if (!$quantityDescription) {
            return redirect()->route('quantity_descriptions.index')->with('error', 'Descripción de cantidad no encontrada.');
        }
        $quantityDescription->is_active = !$quantityDescription->is_active;
        $quantityDescription->save();
        return redirect()->route('quantity_descriptions.index')->with('success', 'Estado de la descripción de cantidad actualizado exitosamente.');
    }
}
