<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Continent;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function index()
    {
        $countries = Country::with('continent')->orderBy('name')->get();
        return view('admin.countries.index', compact('countries'));
    }

    public function create()
    {
        $continents = Continent::orderBy('name')->get();
        return view('admin.countries.create', compact('continents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:3|unique:countries',
                'continent_id' => 'required|exists:continents,id',
            ],
            [
                'name.required' => 'El nombre del país es obligatorio.',
                'code.required' => 'El código del país es obligatorio.',
                'code.unique' => 'El código del país ya existe.',
                'code.max' => 'El código del país no puede tener más de 3 caracteres.',
                'continent_id.required' => 'El continente es obligatorio.',
                'continent_id.exists' => 'El continente seleccionado no es válido.',
            ]
        );

        Country::create($validated);

        return redirect()->route('admin.countries.index')
            ->with('success', 'País creado correctamente.');
    }

    public function show($id)
    {
        $country = Country::with('continent')->findOrFail($id);
        if (!$country) {
            return redirect()->route('admin.countries.index')->with('error', 'País no encontrado.');
        }
        return view('admin.countries.show', compact('country'));
    }

    public function edit($id)
    {
        $country = Country::findOrFail($id);
        if (!$country) {
            return redirect()->route('admin.countries.index')->with('error', 'País no encontrado.');
        }
        $continents = Continent::orderBy('name')->get();
        return view('admin.countries.edit', compact('country', 'continents'));
    }

    public function update(Request $request)
    {

        $country = Country::findOrFail($request->id);
        if (!$country) {
            return redirect()->route('admin.countries.index')->with('error', 'País no encontrado.');
        }
        $validated = $request->validate(
            [
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:3|unique:countries,code,' . $country->id,
                'continent_id' => 'required|exists:continents,id',
            ],
            [
                'name.required' => 'El nombre del país es obligatorio.',
                'code.required' => 'El código del país es obligatorio.',
                'code.unique' => 'El código del país ya existe.',
                'code.max' => 'El código del país no puede tener más de 3 caracteres.',
                'continent_id.required' => 'El continente es obligatorio.',
                'continent_id.exists' => 'El continente seleccionado no es válido.',
            ]
        );

        $country->update($validated);

        return redirect()->route('admin.countries.index')
            ->with('success', 'País actualizado correctamente.');
    }

    public function destroy($id)
    {
        $country = Country::findOrFail($id);
        if (!$country) {
            return redirect()->route('admin.countries.index')->with('error', 'País no encontrado.');
        }
        $country->delete();

        return redirect()->route('admin.countries.index')
            ->with('success', 'País eliminado correctamente.');
    }

    public function trashed()
    {
        $countries = Country::onlyTrashed()->with('continent')->get();
        return view('countries.trashed', compact('countries'));
    }

    public function restore($id)
    {

        $country = Country::withTrashed()->findOrFail($id);

        if (!$country) {
            return redirect()->route('admin.countries.trashed')->with('error', 'País no encontrado.');
        }
        $country->restore();
        return redirect()->route('admin.countries.index')
            ->with('success', 'País restaurado correctamente.');
    }
    public function forceDelete($id)
    {
        $country = Country::withTrashed()->findOrFail($id);
        if (!$country) {
            return redirect()->route('admin.countries.trashed')->with('error', 'País no encontrado.');
        }
        $country->forceDelete();
        return redirect()->route('admin.countries.trashed')
            ->with('success', 'País eliminado permanentemente.');
    }
}
