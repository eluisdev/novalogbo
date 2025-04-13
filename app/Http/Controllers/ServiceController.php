<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::all();
        return view('admin.services.index', compact('services'));
    }

    public function create(){
        return view('admin.services.create');
    }

    public function store(Request $request)
    {
        $request->validate(
            [
                'name' => 'required|string|max:255',
                'is_active' => 'boolean',
            ],
            [
                'name.required' => 'El nombre es requerido.',
                'name.string' => 'El nombre debe ser una cadena de texto.',
                'name.max' => 'El nombre no puede tener más de 255 caracteres.',
                'is_active.boolean' => 'El estado debe ser verdadero o falso.',
            ]
        );
        Service::create($request->all());
        return redirect()->route('services.index')->with('success', 'Servicio creado exitosamente.');
    }
    public function show($id)
    {
        $service = Service::findOrFail($id);
        if(!$service) {
            return redirect()->route('services.index')->with('error', 'Servicio no encontrado.');
        }
        return view('admin.services.show', compact('service'));
    }
    public function edit($id)
    {
        $service = Service::findOrFail($id);
        if(!$service) {
            return redirect()->route('services.index')->with('error', 'Servicio no encontrado.');
        }
        return view('admin.services.edit', compact('service'));
    }
    public function update(Request $request)
    {

        $request->validate(
            [
                'name' => 'required|string|max:255',
                'is_active' => 'boolean',
            ],
            [
                'name.required' => 'El nombre es requerido.',
                'name.string' => 'El nombre debe ser una cadena de texto.',
                'name.max' => 'El nombre no puede tener más de 255 caracteres.',
                'is_active.boolean' => 'El estado debe ser verdadero o falso.',
            ]
        );

        $service = Service::findOrFail($request->id);
        if (!$service) {
            return redirect()->route('services.index')->with('error', 'Servicio no encontrado.');
        }
        $service->update($request->all());
        return redirect()->route('services.index')->with('success', 'Servicio actualizado exitosamente.');
    }
    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        if(!$service) {
            return redirect()->route('services.index')->with('error', 'Servicio no encontrado.');
        }
        $service->delete();
        return redirect()->route('services.index')->with('success', 'Servicio eliminado exitosamente.');
    }
    public function toggleStatus($id)
    {
        $service = Service::findOrFail($id);
        if(!$service) {
            return redirect()->route('services.index')->with('error', 'Servicio no encontrado.');
        }
        $service->is_active = !$service->is_active;
        $service->save();
        return redirect()->route('services.index')->with('success', 'Estado del servicio actualizado exitosamente.');
    }


}
