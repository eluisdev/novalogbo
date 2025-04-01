<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User; // Asegúrate de importar el modelo User

class PasswordChangeController extends Controller
{
    public function showChangeForm()
    {
        return view('auth.passwords-change');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Verificar contraseña actual
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'La contraseña actual no es correcta']);
        }

        // Verificar que la nueva contraseña sea diferente
        if ($request->current_password === $request->new_password) {
            return back()->withErrors(['new_password' => 'La nueva contraseña debe ser diferente a la actual']);
        }

        // Actualizar usando el modelo directamente (más robusto)
        try {
            User::where('id', $user->id)->update([
                'password' => Hash::make($request->new_password),
                'force_password_change' => false,
            ]);

            // Cerrar sesión y regenerar token
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')->with('success', 'Contraseña cambiada exitosamente. Por favor, inicie sesión con su nueva contraseña.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Ocurrió un error al actualizar la contraseña: '.$e->getMessage()]);
        }
    }
}
