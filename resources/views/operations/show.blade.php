@php
    $layout = Auth::user()->role_id == '1' ? 'layouts.admin' : 'layouts.operator';
@endphp

@extends($layout)

@section('dashboard-option')
    <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
        <div
            class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white rounded-xl shadow-sm p-3 mb-6 border border-gray-200">
            <h2 class="text-xl font-black text-gray-800">
                <span class="text-[#0B628D]">Número de servicio interno: {{ $billingNote['op_number'] }}</span>
            </h2>
            <div class="flex sm:flex-row flex-col gap-2">
                @if ($billingNote->quotation['status'] === 'approved')
                    <form action="{{ route('quotations.updateStatus', $billingNote->quotation['id']) }}" method="POST"
                        class="w-full sm:w-auto">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="pending" />
                        <button type="submit"
                            class="flex items-center justify-center px-4 py-2 bg-[#0b8d41] hover:bg-[#588498] text-white text-sm font-medium rounded-lg transition-colors duration-200 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                            Cancelar finalizacion
                        </button>
                    </form>
                @else
                    <form action="{{ route('quotations.updateStatus', $billingNote->quotation['id']) }}" method="POST"
                        class="w-full sm:w-auto">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="approved" />
                        <button type="submit"
                            class="flex items-center justify-center px-4 py-2 bg-[#0b8d41] hover:bg-[#588498] text-white text-sm font-medium rounded-lg transition-colors duration-200 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                            Finalizar operacion
                        </button>
                    </form>
                @endif
            </div>
        </div>
        <div
            class="flex flex-col justify-end sm:flex-row items-center gap-4 bg-white rounded-xl shadow-sm p-3 mb-6 border border-gray-200">
            {{-- @if ($quotation_data['status'] !== 'accepted') --}}
            <a href="{{ route('operations.edit', $billingNote['id']) }}"
                class="flex px-4 py-2 bg-[#FF9800] hover:bg-[#e68a00] text-white text-sm font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md w-full sm:w-auto">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Editar
            </a>
            {{-- @endif --}}
        </div>
        {{-- @if ($quotation['status'] !== 'pending')
            <div
                class="bg-gray-200 rounded-xl shadow-sm p-3 mb-6 border border-gray-200 flex justify-between sm:flex-row flex-col max-sm:gap-4">
                <form action="{{ route('quotations.invoice.download') }}" method="POST" class="w-full sm:w-auto">
                    @csrf
                    <input type="hidden" name="quotation_id" value="{{ $quotation['id'] }}" />
                    <div class="flex gap-2 sm:flex-row flex-col max-sm:justify-center items-center">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="hidden" name="visible" value="0">
                            <input type="checkbox" name="visible"
                                class="form-checkbox h-6 w-6 text-[#4CAF50] rounded border-gray-300 focus:ring-[#4CAF50] mr-3 ml-2"
                                value="1" checked>
                            <span class="text-gray-700 font-medium flex items-center">
                                Fondo + Logo
                            </span>
                        </label>
                        <button type="submit"
                            class="flex items-center justify-center px-4 py-2 bg-yellow-600 hover:bg-yellow-800 hover:cursor-pointer text-white text-sm font-medium rounded-lg transition-all duration-200 shadow-sm opacity-70 w-full sm:w-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                            </svg>
                            Crear factura
                        </button>
                    </div>
                </form>

                <form action="{{ route('quotations.billing-note.download') }}" method="POST"
                    class="w-full sm:w-auto flex gap-2 max-sm:justify-center">
                    @csrf
                    <input type="hidden" name="quotation_id" value="{{ $quotation['id'] }}" />
                    <div class="flex flex-col sm:flex-row-reverse gap-2 items-center">
                        <div class="flex items-center bg-white rounded-lg border border-gray-200 p-1 shadow-sm">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="hidden" name="visible" value="0">
                                <input type="checkbox" name="visible"
                                    class="form-checkbox h-6 w-6 text-[#4CAF50] rounded border-gray-300 focus:ring-[#4CAF50] mr-3 ml-2"
                                    value="1" checked>
                                <span class="text-gray-700 font-medium flex items-center">
                                    Fondo + Logo
                                </span>
                            </label>
                        </div>
                        <button type="submit"
                            class="flex items-center justify-center px-4 py-2 bg-[#4CAF50] hover:bg-[#3d8b40] text-white text-sm font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md w-full sm:w-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Crear nota de cobranza
                        </button>
                    </div>
                </form>
            </div>
        @endif --}}

        <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200 mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Información General</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-6">
                <!-- Columna 1: Moneda y Tipo de Cambio -->
                <div class="space-y-4">
                    <div class="border-b border-gray-100 pb-2">
                        <p class="text-sm font-medium text-gray-500">Moneda</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $billingNote->quotation['currency'] }}</p>
                    </div>
                    <div class="border-b border-gray-100 pb-2">
                        <p class="text-sm font-medium text-gray-500">Tipo de Cambio</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $billingNote->quotation['exchange_rate'] }}</p>
                    </div>
                </div>

                <!-- Columna 2: Datos del Cliente -->
                <div class="space-y-4">
                    <div class="border-b border-gray-100 pb-2">
                        <p class="text-sm font-medium text-gray-500">NIT Cliente</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $billingNote->quotation['customer_nit'] }}</p>
                    </div>
                    <div class="border-b border-gray-100 pb-2">
                        <p class="text-sm font-medium text-gray-500">Nombre Cliente</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $billingNote->customer['name'] }}</p>
                    </div>
                </div>

                <!-- Columna 3: Contacto del Cliente -->
                <div class="space-y-4">
                    <div class="border-b border-gray-100 pb-2">
                        <p class="text-sm font-medium text-gray-500">Correo Cliente</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $billingNote->customer['email'] }}</p>
                    </div>
                    <div class="border-b border-gray-100 pb-2">
                        <p class="text-sm font-medium text-gray-500">Teléfono Cliente</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $billingNote->customer['cellphone'] }}</p>
                    </div>
                </div>

                <div class="border-b border-gray-100 pb-2">
                    <p class="text-sm font-medium text-gray-500">Estado cotizacion</p>
                    <p class="text-lg font-semibold text-gray-900">
                        {{ $billingNote->quotation['status'] === 'accepted' ? 'Confirmado' : 'Pendiente de respuesta' }}</p>
                </div>

            </div>
        </div>

        <div class="space-y-6">
            <!-- Tabla de Costos -->
            <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200 bg-red-100">
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
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tasa de cambio
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($costsDetails as $item)
                                    @if ($item['enabled'] == '1' && $item['type'] == 'cost')
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $item['concept'] }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ number_format($item['amount'], 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $item['exchange_rate'] }}
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tabla de Cargos -->
            <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200 bg-green-100">
                    <h3 class="text-lg font-medium text-gray-900">Cargos</h3>
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
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tasa de cambio
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($costsDetails as $item)
                                    @if ($item['enabled'] == '1' && $item['type'] == 'charge')
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $item['concept'] }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ number_format($item['amount'], 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $item['exchange_rate'] }}
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
        <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200 mt-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Resumen Total</h3>
            </div>

            <div class="p-6">
                <div class="flex justify-end">
                    <div class="w-full md:w-1/3 space-y-2">
                        @php
                            // Calcular subtotal de costos
                            $subtotalCosts = array_reduce(
                                $costsDetails,
                                function ($carry, $item) {
                                    return $carry +
                                        ($item['enabled'] == '1' && $item['type'] == 'cost' ? $item['amount'] : 0);
                                },
                                0,
                            );

                            // Calcular subtotal de cargos
                            $subtotalCharges = array_reduce(
                                $costsDetails,
                                function ($carry, $item) {
                                    return $carry +
                                        ($item['enabled'] == '1' && $item['type'] == 'charge' ? $item['amount'] : 0);
                                },
                                0,
                            );

                            // Calcular total general
                            $total = $subtotalCosts + $subtotalCharges;
                        @endphp

                        <!-- Subtotal de Costos -->
                        <div class="flex justify-between py-2 border-b border-gray-200">
                            <span class="text-gray-600">Subtotal Costos:</span>
                            <span class="font-medium">
                                 {{ number_format($subtotalCosts, 2) }}
                            </span>
                        </div>

                        <!-- Subtotal de Cargos -->
                        <div class="flex justify-between py-2 border-b border-gray-200">
                            <span class="text-gray-600">Subtotal Cargos:</span>
                            <span class="font-medium">
                                 {{ number_format($subtotalCharges, 2) }}
                            </span>
                        </div>

                        <!-- Total General -->
                        <div class="flex justify-between py-3 border-t-2 border-gray-300 mt-2">
                            <span class="text-gray-800 font-semibold">Total General:</span>
                            <span class="font-bold text-lg text-[#0B628D]">
                                 {{ number_format($total, 2) }}
                            </span>
                        </div>

                        <!-- Opcional: Mostrar total en moneda paralela si aplica -->
                        @php
                            $hasParallel = array_reduce(
                                $costsDetails,
                                function ($carry, $item) {
                                    return $carry || ($item['is_amount_parallel'] == '1' && $item['enabled'] == '1');
                                },
                                false,
                            );
                        @endphp

                        @if ($hasParallel)
                            @php
                                $totalParallel = array_reduce(
                                    $costsDetails,
                                    function ($carry, $item) {
                                        $amount =
                                            $item['is_amount_parallel'] == '1'
                                                ? $item['amount_parallel']
                                                : $item['amount'] / $item['exchange_rate'];
                                        return $carry + ($item['enabled'] == '1' ? $amount : 0);
                                    },
                                    0,
                                );
                            @endphp
                            {{-- <div class="flex justify-between py-2 border-t border-gray-200 mt-3 pt-3">
                                <span class="text-gray-600">Total (Moneda Paralela):</span>
                                <span class="font-medium text-[#0B628D]">
                                    USD {{ number_format($totalParallel, 2) }}
                                </span>
                            </div> --}}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
