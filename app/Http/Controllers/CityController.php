<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Country;
use Illuminate\Http\Request;

class CityController extends Controller
{
    //
    public function index()
    {
        $cities = City::with('country')->orderBy('name')->get();
        return view('admin.cities.index', compact('cities'));
    }
    public function create()
    {
        return view('admin.cities.create');
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'country_id' => 'required|exists:countries,id',
        ], [
            'name.required' => 'El nombre de la ciudad es obligatorio.',
            'country_id.required' => 'El país es obligatorio.',
            'country_id.exists' => 'El país seleccionado no es válido.',
        ]);

        City::create($validated);

        return redirect()->route('cities.index')
            ->with('success', 'Ciudad creada correctamente.');
    }
    public function show($id)
    {
        $city = City::with('country')->findOrFail($id);
        if (!$city) {
            return redirect()->route('cities.index')->with('error', 'Ciudad no encontrada.');
        }
        return view('admin.cities.show', compact('city'));
    }
    public function edit($id)
    {
        $city = City::findOrFail($id);
        if (!$city) {
            return redirect()->route('cities.index')->with('error', 'Ciudad no encontrada.');
        }
        $countries = Country::orderBy('name')->get();
        return view('admin.cities.edit', compact('city', 'countries'));
    }
    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'country_id' => 'required|exists:countries,id',
        ], [
            'name.required' => 'El nombre de la ciudad es obligatorio.',
            'country_id.required' => 'El país es obligatorio.',
            'country_id.exists' => 'El país seleccionado no es válido.',
        ]);
        $city = City::findOrFail($request->id);
        if (!$city) {
            return redirect()->route('cities.index')->with('error', 'Ciudad no encontrada.');
        }
        $city->update($validated);

        return redirect()->route('cities.index')
            ->with('success', 'Ciudad actualizada correctamente.');
    }
    public function destroy($id)
    {
        $city = City::findOrFail($id);
        if (!$city) {
            return redirect()->route('cities.index')->with('error', 'Ciudad no encontrada.');
        }
        $city->delete();
        return redirect()->route('cities.index')
            ->with('success', 'Ciudad eliminada correctamente.');
    }
    public function restore($id)
    {
        $city = City::withTrashed()->findOrFail($id);
        if (!$city) {
            return redirect()->route('cities.index')->with('error', 'Ciudad no encontrada.');
        }
        $city->restore();

        return redirect()->route('cities.index')
            ->with('success', 'Ciudad restaurada correctamente.');
    }
    public function trashed()
    {
        $cities = City::onlyTrashed()->with('country')->get();
        return view('admin.cities.trashed', compact('cities'));
    }
    public function forceDelete($id)
    {
        $city = City::withTrashed()->findOrFail($id);
        if (!$city) {
            return redirect()->route('cities.index')->with('error', 'Ciudad no encontrada.');
        }
        $city->forceDelete();

        return redirect()->route('cities.index')
            ->with('success', 'Ciudad eliminada permanentemente.');
    }
    public function search(Request $request)
    {
        $query = $request->input('query');
        $cities = City::with('country')
            ->where('name', 'LIKE', "%{$query}%")
            ->orWhereHas('country', function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%");
            })
            ->orderBy('name')
            ->get();

        return view('admin.cities.index', compact('cities'));
    }
}
