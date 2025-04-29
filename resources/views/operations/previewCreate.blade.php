@if (Auth::user()->role_id == '1')
    @php $layout = 'layouts.admin'; @endphp
@else
    @php $layout = 'layouts.operator'; @endphp
@endif

@extends($layout)

@section('dashboard-option')
    <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
        <div
            class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white rounded-xl shadow-sm p-3 mb-6 border border-gray-200">
            <h2 class="text-xl font-black text-gray-800">
                <span class="text-[#0B628D]">Crear operacion</span>
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('operations.index') }}"
                    class="flex items-center justify-center px-4 py-2 bg-[#0B628D] hover:bg-[#19262c] text-white text-sm font-medium rounded-lg transition-colors duration-200 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Volver a la lista
                </a>
            </div>
        </div>

        <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Cliente
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                CI / NIT
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                N° Cotización
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Costo total
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Fecha de creacion
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Estado
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody id="quotationsTableBody" class="bg-white divide-y divide-gray-200">
                        @if (count($quotations) === 0)
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                    No hay cotizaciones registradas
                                </td>
                            </tr>
                        @else
                            @foreach ($quotations as $quotation)
                                <tr class="quotation-row hover:bg-gray-50 transition-colors duration-150"
                                    data-customer="{{ strtolower($quotation->customer->name) }}"
                                    data-ci="{{ strtolower($quotation->customer->NIT) }}"
                                    data-reference="{{ strtolower($quotation->reference_number) }}"
                                    data-currency="{{ strtolower($quotation->currency) }}"
                                    data-amount="{{ strtolower($quotation->amount) }}"
                                    data-status="{{ strtolower($quotation->status) }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $quotation->customer->name }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $quotation->customer->NIT }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $quotation->reference_number }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $quotation->amount }}
                                            {{ $quotation->currency }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $quotation->delivery_date }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if (strtolower($quotation->status) == 'pending')
                                            <div
                                                class="text-sm text-white bg-red-500 rounded-full px-3 py-1 inline-flex items-center justify-center">
                                                <span class="mr-1 font-bold">•</span> Pendiente de respuesta
                                            </div>
                                        @elseif (strtolower($quotation->status) == 'accepted')
                                            <div
                                                class="text-sm text-white bg-green-500 rounded-full px-3 py-1 inline-flex items-center justify-center">
                                                <span class="mr-1 font-bold">•</span> Confirmada
                                            </div>
                                        @else
                                            <div
                                                class="text-sm text-white bg-green-500 rounded-full px-3 py-1 inline-flex items-center justify-center">
                                                <span class="mr-1 font-bold">•</span> Finalizado
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-center space-x-2">
                                            <a href="{{ route('operations.showQuotation', $quotation->id) }}"
                                                class="text-blue-600 hover:text-blue-900 p-1 rounded-full hover:bg-blue-50 transition-colors duration-200 my-auto"
                                                title="Ver detalle">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>
                                            <a href="{{ route('quotations.show', $quotation->id) }}"
                                                class="text-white bg-blue-400 hover:bg-blue-500 p-1 rounded-full transition-colors duration-200"
                                                title="Ver detalle">
                                                <p>Crear operacion</p>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>


@endsection
