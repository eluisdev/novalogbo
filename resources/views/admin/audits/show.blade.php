@extends('layouts.admin')

@section('dashboard-option')
    @php
        // Traducciones
        $modelTranslations = [
            'Cost' => 'Costo',
            'QuantityDescription' => 'Descripción de cantidad',
            'Customer' => 'Cliente',
            'User' => 'Usuario',
            'City' => 'Ciudad',
            'Quotation' => 'Cotización',
            'ExchangeRate' => 'Tasa de cambio',
            'Product' => 'Producto',

            //Atributos
            'name' => 'nombre',
            'surname' => 'apellido',
            'username' => 'nombre de usuario',
            'email' => 'correo electrónico',
            'password' => 'contraseña',
            'phone' => 'teléfono',
            'updated_at' => 'fecha de actualización',
            'created_at' => 'fecha de creación',
            'reference_number' => 'número de referencia',
            'reference_customer' => 'cliente de referencia',
            'currency' => 'moneda',
            'exchange_rate' => 'tipo de cambio',
            'customer_nit' => 'NIT del cliente',
            'status' => 'estado',
            'amount' => 'monto/cantidad',
        ];

        $actionTranslations = [
            'created' => 'Creación',
            'updated' => 'Actualización',
            'deleted' => 'Eliminación',
            'restored' => 'Restauración',
        ];

        $modelName = class_basename($audit->auditable_type);
        $oldValues = is_string($audit->old_values) ? json_decode($audit->old_values, true) : $audit->old_values;
        $newValues = is_string($audit->new_values) ? json_decode($audit->new_values, true) : $audit->new_values;
    @endphp

    <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">

        <div
            class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white rounded-xl shadow-sm p-4 mb-6 border border-gray-200">
            <h2 class="text-xl font-black text-gray-800">
                <span class="text-[#0B628D]">Detalles de historial</span>
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('audits.index') }}"
                    class="flex items-center justify-center px-4 py-2 bg-[#0B628D] hover:bg-[#19262c] text-white text-sm font-medium rounded-lg transition-colors duration-200 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Volver a inicio
                </a>
            </div>
        </div>

        {{-- Caja principal de auditoría --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-2xl font-bold mb-4 text-gray-800">
                Detalles de Auditoría
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <p class="text-sm text-gray-600">Usuario:</p>
                    <p class="text-lg text-gray-900 font-medium">{{ $audit->user->username }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-600">Nombre completo:</p>
                    <p class="text-lg text-gray-900 font-medium">{{ $audit->user->name }} {{ $audit->user->lastname }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-600">Rol:</p>
                    <p class="text-lg text-gray-900 font-medium">{{ $audit->user->role->description }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-600">Acción realizada:</p>
                    <p class="text-lg text-gray-900 font-medium">
                        {{ $actionTranslations[$audit->action] ?? ucfirst($audit->action) }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-600">Entidad afectada:</p>
                    <p class="text-lg text-gray-900 font-medium">
                        {{ $modelTranslations[$modelName] ?? $modelName }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-600">Fecha:</p>
                    <p class="text-lg text-gray-900 font-medium">
                        {{ $audit->created_at->format('d/m/Y H:i') }}
                    </p>
                </div>
            </div>

            <hr class="my-6">

            <h3 class="text-2xl font-bold text-[#0B628D] mb-6 border-b border-gray-300 pb-2">Cambios realizados</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                {{-- OLD VALUES --}}
                <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-md">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">Valores anteriores</h4>
                    @if (is_array($oldValues) && count($oldValues))
                        <div class="space-y-4">
                            @foreach ($oldValues as $key => $value)
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-100 shadow-sm">
                                    <span class="block text-sm font-medium text-gray-500 mb-1 uppercase tracking-wide">
                                        {{ $modelTranslations[$key] ?? ucfirst(str_replace('_', ' ', $key)) }} : <span
                                            class="text-black break-words">
                                            {{ is_array($value) || is_object($value) ? json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $value }}
                                        </span>
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500">Sin datos anteriores</p>
                    @endif
                </div>

                <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-md">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">Valores nuevos</h4>
                    @if (is_array($newValues) && count($newValues))
                        <div class="space-y-4">
                            @foreach ($newValues as $key => $value)
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-100 shadow-sm">
                                    <span class="block text-sm font-medium text-gray-500 mb-1 uppercase tracking-wide">
                                        {{ $modelTranslations[$key] ?? ucfirst(str_replace('_', ' ', $key)) }} : <span
                                            class="text-black break-words">
                                            {{ is_array($value) || is_object($value) ? json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $value }}
                                        </span>
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500">Sin nuevos datos</p>
                    @endif
                </div>
            </div>

        </div>
    </div>
@endsection
