@extends('layouts.admin')

@section('dashboard-option')

<div class="w-full mx-auto px-4 sm:px-6 lg:px-8">

    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white rounded-xl shadow-sm p-4 mb-6 border border-gray-200">
        <h2 class="text-xl font-black text-gray-800">
            <span class="text-[#0B628D]">Detalles del Usuario</span>
        </h2>
        <div class="flex space-x-2">
            <a href="{{ route('users.index') }}" 
               class="flex items-center justify-center px-4 py-2 bg-[#0B628D] hover:bg-[#19262c] text-white text-sm font-medium rounded-lg transition-colors duration-200 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver a usuarios
            </a>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">

            <div class="space-y-4">
                <div class="border-b border-gray-100 pb-3">
                    <p class="text-sm font-medium text-gray-500">ID de Usuario</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $user->id }}</p>
                </div>
                
                <div class="border-b border-gray-100 pb-3">
                    <p class="text-sm font-medium text-gray-500">Nombre de Usuario</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $user->username }}</p>
                </div>
                
                <div class="border-b border-gray-100 pb-3">
                    <p class="text-sm font-medium text-gray-500">Nombre Completo</p>
                    <p class="text-gray-700">{{ $user->name }} {{ $user->surname }}</p>
                </div>
            </div>

            <div class="space-y-4">
                <div class="border-b border-gray-100 pb-3">
                    <p class="text-sm font-medium text-gray-500">Correo Electrónico</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $user->email }}</p>
                </div>
                
                <div class="border-b border-gray-100 pb-3">
                    <p class="text-sm font-medium text-gray-500">Teléfono</p>
                    <p class="text-gray-700">{{ $user->phone ?? 'No disponible' }}</p>
                </div>
                
                <div class="border-b border-gray-100 pb-3">
                    <p class="text-sm font-medium text-gray-500">Rol</p>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-medium bg-blue-100 text-blue-800 mt-1">
                        {{ $user->role_id === 2 ? 'Operador' : 'Administrador' }}
                    </span>
                </div>
            </div>

            <div class="space-y-4">
                
                <div class="border-b border-gray-100 pb-3">
                    <p class="text-sm font-medium text-gray-500">Fecha de Registro</p>
                    <p class="text-gray-700">{{ $user->created_at->translatedFormat('l, j F Y - H:i') }}</p>
                </div>
                
                <div class="border-b border-gray-100 pb-3">
                    <p class="text-sm font-medium text-gray-500">Última Actualización</p>
                    <p class="text-gray-700">{{ $user->updated_at->translatedFormat('l, j F Y - H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection