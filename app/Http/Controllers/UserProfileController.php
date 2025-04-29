<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserProfileController extends Controller
{
    /**
     * Mostrar el formulario de edición del perfil
     */
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }
    
    public function show()
    {
        $user = Auth::user();
        return view('profile.show', compact('user'));
    }

    /**
     * Actualizar los datos del perfil
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:20',
            'current_password' => 'nullable|required_with:new_password|string|current_password',
            'new_password' => 'nullable|string|min:8|different:current_password',
        ],
        [
            'name.required' => 'El nombre es obligatorio.',
            'surname.required' => 'El apellido es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe ser una dirección válida.',
            'email.unique' => 'Este correo electrónico ya está en uso.',
            'phone.max' => 'El teléfono no puede tener más de 20 caracteres.',
            'current_password.current_password' => 'La contraseña actual es incorrecta.',
            'new_password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'new_password.different' => 'La nueva contraseña debe ser diferente a la actual.',
        ]);

        if ($request->filled('new_password')) {
            $user->password = Hash::make($validated['new_password']);
        }


        $user2 = User::where('id', $user->id);
        if (!$user2) {
            return redirect()->route('users.index')->with('error', 'Usuario no encontrado.');
        }

        $user2->update([
            'name' => $validated['name'],
            'surname' => $validated['surname'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? $user->phone,
            'password' => $user->password,
        ]);

        if ($user->role->description === "admin") {
            return redirect()->route('users.index')
            ->with('success', 'Perfil actualizado correctamente');
        } else {
            return redirect()->route('customers.index')
            ->with('success', 'Perfil actualizado correctamente');
        }
    }
}
