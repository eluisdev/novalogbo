<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;


class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::with('role')->latest()->paginate(10);

        // Retornar la vista con los clientes
        return view('customers.index', compact('customers'));
    }


    public function create()
    {
        // Retornar la vista para crear un nuevo cliente
        return view('customers.create');
    }



    public function store(Request $request)
    {
        // Validar los datos de entrada
        $request->validate([
            'NIT' => 'required|integer|unique:customers',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'phone' => 'nullable|string|max:20',
            'cellphone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:100',
            'role_id' => 'required|exists:roles,id',
        ], [
            'NIT.required' => 'El NIT\CI es obligatorio.',
            'NIT.integer' => 'El NIT\CI debe ser un número entero.',
            'NIT.unique' => 'Este NIT\CI ya está en uso.',
            'name.required' => 'La razón social es obligatoria.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe ser una dirección válida.',
            'email.unique' => 'Este correo electrónico ya está en uso.',
            'phone.nullable' => 'El teléfono es opcional.',
            'cellphone.nullable' => 'El celular es opcional.',
            'address.nullable' => 'La dirección es opcional.',
            'department.nullable' => 'El departamento o lugar de residencia es opcional.',
            'role_id.required' => 'El rol es obligatorio.',
            'role_id.exists' => 'El rol seleccionado no es válido.',
        ]);

        // Crear un nuevo cliente
        Customer::create($request->all());

        // Redirigir a la lista de clientes con un mensaje de éxito
        return redirect()->route('customers.index')->with('success', 'Cliente creado exitosamente.');
    }

    public function show($NIT)
    {

        $customer = Customer::with(
            [
                'role',
                'quotations',
                'quotations.services',
                'quotations.products',
                'quotations.products.origin',
                'quotations.products.destination',
                'quotations.products.incoterm',
                'quotations.costDetails',
                'quotations.costDetails.cost',
            ]
        )->findOrFail($NIT);
        if (!$customer) {
            return redirect()->route('customers.index')->with('error', 'Cliente no encontrado.');
        }
        return view('customers.show', compact('customer'));
    }

    public function edit($NIT)
    {
        // Obtener el cliente por ID
        $customer = Customer::findOrFail($NIT);
        // Verificar si el cliente existe
        if (!$customer) {
            return redirect()->route('customers.index')->with('error', 'Cliente no encontrado.');
        }
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, $NIT)
    {
        // Validar los datos de entrada
        $request->validate([
            'NIT' => 'required|unique:customers,NIT,' . $NIT . ',NIT',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email,' . $NIT . ',NIT',
            'phone' => 'nullable|string|max:20',
            'cellphone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:100',
            'active' => 'required|boolean',
            'role_id' => 'required|exists:roles,id',
        ], [
            'NIT.required' => 'El NIT\CI es obligatorio.',
            'NIT.unique' => 'Este NIT\CI ya está en uso.',
            'name.required' => 'La razón social es obligatoria.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe ser una dirección válida.',
            'email.unique' => 'Este correo electrónico ya está en uso.',
            'phone.nullable' => 'El teléfono es opcional.',
            'cellphone.nullable' => 'El celular es opcional.',
            'address.nullable' => 'La dirección es opcional.',
            'department.nullable' => 'El departamento o lugar de residencia es opcional.',
            'active.required' => 'El estado activo es obligatorio.',
            'active.boolean' => 'El estado activo debe ser verdadero o falso.',
            'role_id.required' => 'El rol es obligatorio.',
            'role_id.exists' => 'El rol seleccionado no es válido.'
        ]);

        // Obtener el cliente por ID
        $customer = Customer::find($NIT);
        // Verificar si el cliente existe
        if (!$customer) {
            return redirect()->route('customers.index')->with('error', 'Cliente no encontrado.');
        }

        // Actualizar el cliente
        $customer->update($request->all());

        // Redirigir a la lista de clientes con un mensaje de éxito
        return redirect()->route('customers.index')->with('success', 'Cliente actualizado exitosamente.');
    }

    public function destroy($NIT)
    {
        // Buscar el cliente (retorna null si no existe)
        $customer = Customer::find($NIT);

        // Si no existe, redirigir con un mensaje de error
        if (!$customer) {
            return redirect()
                ->route('customers.index')
                ->with('error', 'El cliente no existe o ya fue eliminado.');
        }

        // Si existe, eliminarlo
        $customer->delete();

        // Redirigir con mensaje de éxito
        return redirect()
            ->route('customers.index')
            ->with('success', 'Cliente eliminado exitosamente.');
    }
}
