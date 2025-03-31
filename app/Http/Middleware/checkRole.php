<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|array  $roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Verifica que el usuario esté autenticado usando Auth facade
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Convierte el parámetro de roles en un array si es string
        $roles = is_array($roles) ? $roles : [$roles];

        // Verifica si el usuario tiene alguno de los roles permitidos
        if (!$this->userHasAnyRole($roles)) {
            abort(403, 'No tienes permiso para acceder a esta página.');
        }

        return $next($request);
    }

    /**
     * Verifica si el usuario tiene alguno de los roles proporcionados.
     *
     * @param array $roles
     * @return bool
     */
    private function userHasAnyRole(array $roles): bool
    {
        // Usando Auth facade explícitamente
        return Auth::user()->roles->whereIn('description', $roles)->count() > 0;

        // Alternativa si usas una columna 'role' directamente en la tabla users
        // return in_array(Auth::user()->role, $roles);
    }
}

