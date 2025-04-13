<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Incoterm;
use Illuminate\Http\Request;

class IncotermController extends Controller
{
    public function index()
    {
        $incoterms = Incoterm::all();
        return view('admin.incoterms.index', compact('incoterms'));
    }

    public function create()
    {
        return view('admin.incoterms.create');
    }

    public function store(Request $request)
    {
        $request->validate(
            [
                'code' => 'required|string|max:3|unique:incoterms',
                'name' => 'required|string|max:255',
                'is_active' => 'boolean',
            ],
            [
                'code.required' => 'El código es requerido.',
                'code.string' => 'El código debe ser una cadena de texto.',
                'code.max' => 'El código no puede tener más de 3 caracteres.',
                'name.required' => 'El nombre es requerido.',
                'name.string' => 'El nombre debe ser una cadena de texto.',
                'name.max' => 'El nombre no puede tener más de 255 caracteres.',
                'is_active.boolean' => 'El estado debe ser verdadero o falso.',
            ]
        );
        Incoterm::create($request->all());
        return redirect()->route('incoterms.index')->with('success', 'Incoterm creado exitosamente.');
    }

    public function show($id)
    {
        $incoterm = Incoterm::findOrFail($id);
        if(!$incoterm) {
            return redirect()->route('incoterms.index')->with('error', 'Incoterm no encontrado.');
        }
        return view('admin.incoterms.show', compact('incoterm'));
    }

    public function edit($id)
    {
        $incoterm = Incoterm::findOrFail($id);
        if(!$incoterm) {
            return redirect()->route('incoterms.index')->with('error', 'Incoterm no encontrado.');
        }
        return view('admin.incoterms.edit', compact('incoterm'));
    }
    public function update(Request $request)
    {
        $request->validate(
            [
                'code' => 'required|string|max:3|unique:incoterms,code,' . $request->id,
                'name' => 'required|string|max:255',
                'is_active' => 'boolean',
            ],
            [
                'code.required' => 'El código es requerido.',
                'code.string' => 'El código debe ser una cadena de texto.',
                'code.max' => 'El código no puede tener más de 3 caracteres.',
                'name.required' => 'El nombre es requerido.',
                'name.string' => 'El nombre debe ser una cadena de texto.',
                'name.max' => 'El nombre no puede tener más de 255 caracteres.',
                'is_active.boolean' => 'El estado debe ser verdadero o falso.',
            ]
        );
        Incoterm::find($request->id)->update($request->all());
        return redirect()->route('incoterms.index')->with('success', 'Incoterm actualizado exitosamente.');
    }

    public function destroy($id)
    {
        $incoterm = Incoterm::findOrFail($id);
        if(!$incoterm) {
            return redirect()->route('incoterms.index')->with('error', 'Incoterm no encontrado.');
        }
        $product = Product::with('incoterm')->where('incoterm_id', $id)->first();
        if($product){
            return redirect()->route('incoterms.index')->with('error', 'Existe un producto asociado a este Incoterm');
        }
        $incoterm->delete();
        return redirect()->route('incoterms.index')->with('success', 'Incoterm eliminado exitosamente.');
    }
    public function toggleStatus($id)
    {
        $incoterm = Incoterm::findOrFail($id);
        $incoterm->is_active = !$incoterm->is_active;
        $incoterm->save();

        $status = $incoterm->is_active ? 'activado' : 'desactivado';

        return redirect()->route('incoterms.index')
            ->with('success', "Incoterm {$incoterm->code} {$status} correctamente");
    }
}
