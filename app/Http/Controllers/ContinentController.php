<?php

namespace App\Http\Controllers;

use App\Models\Continent;
use Illuminate\Http\Request;

class ContinentController extends Controller
{
    public function index()
    {
        $continents = Continent::orderBy('name')->get();
        return view('admin.continents.index', compact('continents'));
    }

    public function create()
    {
        return view('admin.continents.create');
    }
    public function store(Request $request)
    {
        $request->validate(
            [
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:3|unique:continents,code'
            ],
            [
                'name.required' => 'El nombre del continente es obligatorio.',
                'name.string' => 'El nombre del continente debe ser una cadena de texto.',
                'name.max' => 'El nombre del continente no puede tener más de 255 caracteres.',
                'code.required' => 'El código del continente es obligatorio.',
                'code.string' => 'El código del continente debe ser una cadena de texto.',
                'code.max' => 'El código del continente no puede tener más de 3 caracteres.',
                'code.unique' => 'El código del continente ya existe.'
            ]
        );

        Continent::create($request->all());
        return redirect()->route('continents.index')->with('success', 'Continente creado correctamente.');
    }
    public function show($id)
    {
        $continent = Continent::findOrFail($id);
        if (!$continent) {
            return redirect()->route('continents.index')->with('error', 'Continente no encontrado.');
        }
        return view('admin.continents.show', compact('continent'));
    }
    public function edit($id)
    {
        $continent = Continent::findOrFail($id);
        if (!$continent) {
            return redirect()->route('continents.index')->with('error', 'Continente no encontrado.');
        }
        return view('admin.continents.edit', compact('continent'));
    }
    public function update(Request $request)
    {
        $continent = Continent::findOrFail($request->id);
        if (!$continent) {
            return redirect()->route('continents.index')->with('error', 'Continente no encontrado.');
        }
        $request->validate(
            [
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:3|unique:continents,code,' . $continent->id
            ],
            [
                'name.required' => 'El nombre del continente es obligatorio.',
                'name.string' => 'El nombre del continente debe ser una cadena de texto.',
                'name.max' => 'El nombre del continente no puede tener más de 255 caracteres.',
                'code.required' => 'El código del continente es obligatorio.',
                'code.string' => 'El código del continente debe ser una cadena de texto.',
                'code.max' => 'El código del continente no puede tener más de 3 caracteres.',
                'code.unique' => 'El código del continente ya existe.'
            ]
        );

        $continent->update($request->all());
        return redirect()->route('continents.index')->with('success', 'Continente actualizado correctamente.');
    }

    public function destroy($id)
    {
        $continent = Continent::findOrFail($id);
        if (!$continent) {
            return redirect()->route('continents.index')->with('error', 'Continente no encontrado.');
        }
        $continent->delete();
        return redirect()->route('continents.index')->with('success', 'Continente eliminado correctamente.');
    }

    public function trashed()
    {
        $continents = Continent::onlyTrashed()->get();
        return view('admin.continents.trashed', compact('continents'));
    }

    public function restore($id)
    {
        $continent = Continent::withTrashed()->findOrFail($id);
        if (!$continent) {
            return redirect()->route('continents.trashed')->with('error', 'Continente no encontrado.');
        }
        $continent->restore();
        return redirect()->route('continents.index')
            ->with('success', 'Continente restaurado correctamente.');
    }

    public function forceDelete($id)
    {
        $continent = Continent::withTrashed()->findOrFail($id);
        if (!$continent) {
            return redirect()->route('continents.trashed')->with('error', 'Continente no encontrado.');
        }
        $continent->forceDelete();
        return redirect()->route('continents.trashed')
            ->with('success', 'Continente eliminado permanentemente.');
    }
}
