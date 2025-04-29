@extends('layouts.auth')

@section('auth-action')
    @php
        $token = request()->route('token');
        $email = request()->query('email');
    @endphp
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

        <form method="POST" action="{{ route('password.change.store') }}" class="space-y-4">
            @csrf
            <p class="text-yellow-700 font-semibold text-center">Por razones de seguridad, debe cambiar su contraseña al iniciar sesión por primera vez.</p>
            <div>
                <label for="current_password" class="block font-medium text-gray-900">Contraseña actual</label>
                <input type="password" id="current_password" name="current_password" placeholder="****************"
                    value="{{ old('current_password') }}"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label for="new_password" class="block font-medium text-gray-900">Nueva contraseña</label>
                <input type="password" id="new_password" name="new_password" placeholder="****************"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label for="new_password_confirmation" class="block font-medium text-gray-900">Confirmar contraseña</label>
                <input type="password" id="new_password_confirmation" name="new_password_confirmation" placeholder="****************"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">
            <button type="submit"
                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gradient-to-r from-[#0e71a2] to-[#074665] hover:from-[#084665] hover:to-[#06364e] transition-colors duration-200 hover:cursor-pointer mt-6">
                Cambiar contraseña
            </button>
        </form>
    </div>
@endsection
