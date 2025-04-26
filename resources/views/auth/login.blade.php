@extends('layouts.auth')

@section('auth-action')

    <div class="w-full max-w-md mx-auto p-7 bg-gray-50 rounded-lg shadow-2xl space-y-3">
        <img src="{{ asset('images/logoNova.png') }}" alt="Logo de la app" class="max-w-32 mx-auto">

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 text-red-700 rounded">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf
            <div>
                <label for="username" class="block font-medium text-gray-900">Nombre de usuario</label>
                <input type="text" id="username" name="username" placeholder="Usuario" value="{{ old('username') }}"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div class="relative">
                <label for="password" class="block font-medium text-gray-900">Contraseña</label>
                <div class="relative">
                    <input type="password" id="password" name="password" placeholder="**********"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 pr-10">
                    <button type="button" onclick="togglePassword()"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center mt-1 hover:text-blue-600 transition-colors">
                        <!-- Icono de ojo (mostrar) -->
                        <svg id="show-icon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                        </svg>
                        <!-- Icono de ojo tachado (ocultar) - inicialmente hidden -->
                        <svg id="hide-icon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd" />
                            <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z" />
                        </svg>
                    </button>
                </div>
            </div>
            
            <button type="submit"
                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gradient-to-r from-[#0e71a2] to-[#074665] hover:from-[#084665] hover:to-[#06364e] transition-colors duration-200 hover:cursor-pointer mt-6">
                Iniciar sesión
            </button>
        </form>
    </div>
    <div class="text-center text-sm text-gray-700 mt-5">
        <a href="{{ route('password.request') }} " class="font-semibold">¿Olvidaste tu contraseña?</a>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const showIcon = document.getElementById('show-icon');
            const hideIcon = document.getElementById('hide-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                showIcon.classList.add('hidden');
                hideIcon.classList.remove('hidden');
            } else {
                passwordInput.type = 'password';
                showIcon.classList.remove('hidden');
                hideIcon.classList.add('hidden');
            }
        }
    </script>
@endsection
