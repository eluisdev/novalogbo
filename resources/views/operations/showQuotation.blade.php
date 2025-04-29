@php
    $layout = Auth::user()->role_id == '1' ? 'layouts.admin' : 'layouts.operator';
@endphp

@extends($layout)

@section('dashboard-option')
    <div class="w-full mx-auto px-4 sm:px-6 lg:px-8 pb-5">
        <div
            class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white rounded-xl shadow-sm p-3 mb-6 border border-gray-200">
            <h2 class="text-xl font-black text-gray-800">
                <span class="text-[#0B628D]">Número de cotización: {{ $quotation_data['reference_number'] }}</span>
            </h2>

            <div class="flex sm:flex-row flex-col gap-2">
                <div class="flex space-x-2">
                    <a href="{{ route('operations.create') }}"
                        class="flex items-center justify-center px-4 py-2 bg-[#0B628D] hover:bg-[#19262c] text-white text-sm font-medium rounded-lg transition-colors duration-200 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Volver a crear operacion
                    </a>
                </div>
            </div>
        </div>

        <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200 mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Información General</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-6">
                <!-- Columna 1: Moneda y Tipo de Cambio -->
                <div class="space-y-4">
                    <div class="border-b border-gray-100 pb-2">
                        <p class="text-sm font-medium text-gray-500">Moneda</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $quotation_data['currency'] }}</p>
                    </div>
                    <div class="border-b border-gray-100 pb-2">
                        <p class="text-sm font-medium text-gray-500">Tipo de Cambio</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $quotation_data['exchange_rate'] }}</p>
                    </div>
                </div>

                <!-- Columna 2: Datos del Cliente -->
                <div class="space-y-4">
                    <div class="border-b border-gray-100 pb-2">
                        <p class="text-sm font-medium text-gray-500">NIT Cliente</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $quotation_data['NIT'] }}</p>
                    </div>
                    <div class="border-b border-gray-100 pb-2">
                        <p class="text-sm font-medium text-gray-500">Nombre Cliente</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $quotation_data['customer_info']['name'] }}</p>
                    </div>
                </div>

                <!-- Columna 3: Contacto del Cliente -->
                <div class="space-y-4">
                    <div class="border-b border-gray-100 pb-2">
                        <p class="text-sm font-medium text-gray-500">Correo Cliente</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $quotation_data['customer_info']['email'] }}</p>
                    </div>
                    <div class="border-b border-gray-100 pb-2">
                        <p class="text-sm font-medium text-gray-500">Teléfono Cliente</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $quotation_data['customer_info']['phone'] }}</p>
                    </div>
                </div>
                <div class="border-b border-gray-100 pb-2">
                    <p class="text-sm font-medium text-gray-500">Estado cotizacion</p>
                    <p class="text-lg font-semibold text-gray-900">
                        @switch($quotation_data['status'])
                            @case('accepted')
                                Confirmada
                            @break

                            @case('pending')
                                Pendiente de respuesta
                            @break

                            @case('refused')
                                Rechazada
                            @break

                            @case('in_progress')
                                En Progreso
                            @break

                            @case('canceled')
                                Cancelado
                            @break

                            @default
                                Estado desconocido
                        @endswitch
                    </p>
                </div>
                <div class="border-b border-gray-100 pb-2">
                    <p class="text-sm font-medium text-gray-500">Referencia cliente</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $quotation_data['reference_customer'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200 mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Productos</h3>
            </div>

            <div class="p-6">
                @foreach ($quotation_data['products'] as $product)
                    <div class="mb-8 p-4 border border-gray-200 rounded-lg">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
                            <!-- Detalles del Producto -->
                            @if (isset($product['name']))
                                <div class="space-y-2">
                                    <p class="text-sm font-medium text-gray-500">Nombre</p>
                                    <p class="text-gray-700">{{ $product['name'] }}</p>
                                </div>
                            @endif
                            <div>
                                <p class="text-sm font-medium text-gray-500">Peso</p>
                                <p class="text-gray-700">{{ $product['weight'] }} kg</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Volumen</p>
                                <p class="text-gray-700">{{ $product['volume'] }} {{ $product['volume_unit'] }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Cantidad</p>
                                <p class="text-gray-700">{{ $product['quantity'] }}
                                    {{ $product['quantity_description_name'] }}
                                </p>
                            </div>
                            <div class="space-y-2">
                                <p class="text-sm font-medium text-gray-500">Origen</p>
                                <p class="text-gray-700">{{ $product['origin_name'] }}</p>
                            </div>
                            <div class="space-y-2">
                                <p class="text-sm font-medium text-gray-500">Destino</p>
                                <p class="text-gray-700">{{ $product['destination_name'] }}</p>
                            </div>
                            <div class="space-y-2">
                                <p class="text-sm font-medium text-gray-500">Incoterm</p>
                                <p class="text-gray-700">{{ $product['incoterm_name'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Servicios -->
            <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Servicios</h3>
                </div>

                <div class="p-6">
                    <!-- Servicios incluidos -->
                    <div class="mb-6 bg-green-50 p-4 rounded-lg border border-green-200">
                        <h3 class="font-bold text-green-800 mb-2">✅ Servicios incluidos</h3>
                        <ul>
                            @foreach ($quotation_data['services'] as $id => $status)
                                @if ($status == 'include')
                                    <li>{{ $quotation_data['service_names'][$id] }}</li>
                                @endif
                            @endforeach
                        </ul>
                    </div>

                    <!-- Servicios excluidos -->
                    <div class="bg-gray-100 p-4 rounded-lg border border-gray-200">
                        <h3 class="font-bold text-gray-700 mb-2">❌ Servicios excluidos</h3>
                        <ul>
                            @foreach ($quotation_data['services'] as $id => $status)
                                @if ($status == 'exclude')
                                    <li>{{ $quotation_data['service_names'][$id] }}</li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Costos -->
            <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Costos</h3>
                </div>

                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Concepto
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Monto
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($quotation_data['costs'] as $cost)
                                    @if ($cost['enabled'] == '1')
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $cost['cost_name'] }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $quotation_data['currency'] }} {{ $cost['amount'] }}
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Resumen Total</h3>
            </div>

            <div class="p-6">
                <div class="flex gap-3 justify-end space-x-8 flex-wrap"> <!-- Añadido space-x-8 para separar los dos resúmenes -->
                    <!-- Resumen 1: Solo costos originales -->
                    <div class="w-full md:w-1/3 bg-gray-50 p-4 rounded-lg mr-0">
                        <h3 class="font-bold text-gray-700 mb-3">Resumen Original</h3>
                        @php
                            $subtotal_original = array_reduce(
                                $quotation_data['costs'],
                                function ($carry, $item) {
                                    $amount = is_numeric($item['amount']) ? (float)$item['amount'] : 0;
                                    return $carry + ($item['enabled'] == '1' ? $amount : 0);
                                },
                                0
                            );
                        @endphp
                        <div class="flex justify-between py-2 border-b border-gray-200">
                            <span class="text-gray-600">Subtotal:</span>
                            <span class="font-medium">
                                {{ $quotation_data['currency'] }} {{ number_format($subtotal_original, 2) }}
                            </span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-200">
                            <span class="text-gray-600">Total:</span>
                            <span class="font-bold text-lg text-[#0B628D]">
                                {{ $quotation_data['currency'] }} {{ number_format($subtotal_original, 2) }}
                            </span>
                        </div>
                    </div>
            
                    <!-- Resumen 2: Usa costos paralelos (si existen) -->
                    <div class="w-full md:w-1/3 bg-blue-50 p-4 rounded-lg">
                        <h3 class="font-bold text-gray-700 mb-3">Resumen con Costos Paralelos</h3>
                        @php
                            $subtotal_parallel = array_reduce(
                                $quotation_data['costs'],
                                function ($carry, $item) {
                                    $amount = isset($item['amount_parallel']) && is_numeric($item['amount_parallel']) 
                                        ? (float)$item['amount_parallel'] 
                                        : (is_numeric($item['amount']) ? (float)$item['amount'] : 0);
                                    return $carry + ($item['enabled'] == '1' ? $amount : 0);
                                },
                                0
                            );
                        @endphp
                        <div class="flex justify-between py-2 border-b border-gray-200">
                            <span class="text-gray-600">Subtotal:</span>
                            <span class="font-medium">
                                {{ $quotation_data['currency'] }} {{ number_format($subtotal_parallel, 2) }}
                            </span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-200">
                            <span class="text-gray-600">Total:</span>
                            <span class="font-bold text-lg text-[#0B628D]">
                                {{ $quotation_data['currency'] }} {{ number_format($subtotal_parallel, 2) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
