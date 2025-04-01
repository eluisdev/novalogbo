<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }
    public function store(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|min:6|string',
        ]);

        if (!Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password']])) {
            // Si las credenciales no son válidas, redirigir de vuelta con un mensaje de error

            return back()->withErrors([
                'message' => 'Las credenciales no coinciden con nuestros registros.',
            ]);
        }

        $user = Auth::user();
        if ($user->force_password_change) {
            return redirect()->route('password.change');
        }
        if ($user->role->description == 'admin') {
            return redirect()->intended(route('admin.dashboard'));
        } elseif ($user->role->description == 'operator') {
            return redirect()->intended(route('operator.dashboard'));
        } else {
            // Manejar otros roles o redirigir a una página predeterminada
            return redirect()->intended(route('home'));
        }
    }
    public function logout(Request $request)
    {
        // Cerrar sesión del usuario autenticado
        // Invalidar la sesión y regenerar el token CSRF
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        // Redirigir a la página de inicio de sesión
        return redirect('/login');
    }

    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $user = User::where('email', $request->email)->first();

        if(!$user){
            return back()->withErrors(['message' => 'No encontramos una cuenta con ese correo electrónico.']);
        }

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::ResetLinkSent
        ? back()->with(['status' => __($status)])
        : back()->withErrors(['email' => __($status)]);
    }

    public function showResetPasswordForm($token)
    {
        return view('auth.new-password', ['token' => $token]);
    }

    public function resetPassword(Request $request){
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6',
        ]);
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect('/login')->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

}
