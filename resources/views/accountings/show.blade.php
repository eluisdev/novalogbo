@if (Auth::user()->role_id == '1')
    @php $layout = 'layouts.admin'; @endphp
@elseif (Auth::user()->role_id == '2')
    @php $layout = 'layouts.commercial'; @endphp
@else
    @php $layout = 'layouts.operator'; @endphp
@endif

@extends($layout)

{{-- {{dd($quotation_data)}} --}}
@section('dashboard-option')
    <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
        <div
            class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white rounded-xl shadow-sm p-3 mb-6 border border-gray-200">
            <h2 class="text-xl font-black text-gray-800">
                <span class="text-[#0B628D]">Número de cotización: {{ $quotation_data['reference_number'] }}</span>
            </h2>

            <div class="flex sm:flex-row flex-col gap-2">
                @if ($quotation_data['status'] === 'approved')
                    <form action="{{ route('quotations.updateStatus', $quotation_data['id']) }}" method="POST"
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
                    <form action="{{ route('quotations.updateStatus', $quotation_data['id']) }}" method="POST"
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
                            Crear operación
                        </button>
                    </form>
                @endif

                <div class="flex space-x-2">
                    <a href="{{ route('quotations.index') }}"
                        class="flex items-center justify-center px-4 py-2 bg-[#0B628D] hover:bg-[#19262c] text-white text-sm font-medium rounded-lg transition-colors duration-200 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Volver a cotizaciones
                    </a>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-3 mb-6 border border-gray-200">
            <div class="flex flex-col lg:flex-row gap-2 w-full justify-between items-center">
                @if ($errors->any())
                    <div class="bg-red-100 text-red-700 p-4 rounded-md">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('quotations.generate.download') }}" method="POST"
                    class="flex flex-col sm:flex-row gap-2 items-center w-full sm:w-auto">
                    @csrf
                    <input type="hidden" name="quotation_id" value="{{ $quotation_data['id'] }}" />
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
                        class="flex items-center justify-center p-1.5 bg-gradient-to-r from-[#0B628D] to-[#0d7db5] hover:from-[#0d455e] hover:to-[#0B628D] text-white font-medium rounded-lg transition-all duration-300 shadow-md transform text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Generar Documento
                    </button>
                </form>

                <form action="{{ route('quotations.generate.excel.download') }}" method="POST"
                    class="flex flex-col sm:flex-row gap-2 items-center w-full sm:w-auto">
                    @csrf
                    <input type="hidden" name="quotation_id" value="{{ $quotation_data['id'] }}" />
                    <button type="submit"
                        class="flex-1 sm:flex-none p-1.5 bg-gradient-to-r from-yellow-500 to-yellow-600 text-white rounded-lg text-sm font-semibold hover:from-yellow-600 hover:to-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-2 transition-all duration-200 shadow-md hover:shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-2" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Generar Cotización Interna (Excel)
                    </button>
                </form>

                @if ($quotation_data['status'] !== 'accepted')
                    <a href="{{ route('quotations.edit', $quotation_data['id']) }}"
                        class="flex items-center justify-center px-4 py-2 bg-[#FF9800] hover:bg-[#e68a00] text-white text-sm font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md w-full sm:w-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Editar
                    </a>
                @endif
            </div>
        </div>

        {{-- @if ($quotation_data['status'] !== 'pending')
            <div
                class="bg-gray-200 rounded-xl shadow-sm p-3 mb-6 border border-gray-200 flex justify-between sm:flex-row flex-col max-sm:gap-4">
                <form action="{{ route('quotations.invoice.download') }}" method="POST" class="w-full sm:w-auto">
                    @csrf
                    <input type="hidden" name="quotation_id" value="{{ $quotation_data['id'] }}" />
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
                    <input type="hidden" name="quotation_id" value="{{ $quotation_data['id'] }}" />
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
                        {{ $quotation_data['status'] === 'approved' ? 'Finalizado' : 'Pendiente' }}</p>
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
                                    ({{ $product['quantity_description_name'] }})
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
                <div class="flex justify-end">
                    <div class="w-full md:w-1/3">
                        @php
                            $subtotal = array_reduce(
                                $quotation_data['costs'],
                                function ($carry, $item) {
                                    return $carry + ($item['enabled'] == '1' ? $item['amount'] : 0);
                                },
                                0,
                            );
                        @endphp

                        <div class="flex justify-between py-2 border-b border-gray-200">
                            <span class="text-gray-600">Subtotal:</span>
                            <span class="font-medium">
                                {{ $quotation_data['currency'] }} {{ number_format($subtotal, 2) }}
                            </span>
                        </div>

                        <div class="flex justify-between py-2 border-b border-gray-200">
                            <span class="text-gray-600">Total:</span>
                            <span class="font-bold text-lg text-[#0B628D]">
                                {{ $quotation_data['currency'] }} {{ number_format($subtotal, 2) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
