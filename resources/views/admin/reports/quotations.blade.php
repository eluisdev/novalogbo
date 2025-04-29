@if (Auth::user()->role_id == '1')
    @php $layout = 'layouts.admin'; @endphp
@elseif (Auth::user()->role_id == '2')
    @php $layout = 'layouts.commercial'; @endphp
@else
    @php $layout = 'layouts.operator'; @endphp
@endif

@extends($layout)

@section('dashboard-option')
    <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Encabezado -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white rounded-xl shadow-sm p-4 mb-6 border border-gray-200">
            <div class="flex sm:flex-row flex-col items-center gap-6">
                <h2 class="text-xl font-black text-gray-800">
                    <span class="text-[#0B628D]">Reporte de Cotizaciones</span>
                </h2>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="bg-white p-2 rounded-lg shadow-sm border border-gray-200 mb-5">
            <div class="flex sm:flex-row flex-col items-center justify-around text-lg">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Total:</span>
                    <span id="totalCount" class="font-semibold">{{ $total }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Pendientes:</span>
                    <span id="pendingCount" class="font-semibold text-yellow-600">{{ $pending }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Aceptadas:</span>
                    <span id="acceptedCount" class="font-semibold text-green-600">{{ $accepted }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Rechazadas:</span>
                    <span id="refusedCount" class="font-semibold text-red-600">{{ $refused }}</span>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="grid grid-cols-1 mb-6">
            <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                <form id="filterForm" method="GET" action="{{ route('reports.quotations') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
                        <!-- Filtro por fechas -->
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Fecha inicial</label>
                            <input type="date" name="date_from" value="{{ $dateFrom }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0B628D] focus:border-[#0B628D]">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Fecha final</label>
                            <input type="date" name="date_to" value="{{ $dateTo }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0B628D] focus:border-[#0B628D]">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Usuario</label>
                            <select name="user_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0B628D] focus:border-[#0B628D]">
                                <option value="">Todos los usuarios</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ $userId == $user->id ? 'selected' : '' }}>{{ $user->name . ' ' . $user->surname }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Estado</label>
                            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0B628D] focus:border-[#0B628D]">
                                <option value="">Todos los estados</option>
                                <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Pendiente</option>
                                <option value="accepted" {{ $status == 'accepted' ? 'selected' : '' }}>Aceptada</option>
                                <option value="refused" {{ $status == 'refused' ? 'selected' : '' }}>Rechazada</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Buscar (Ref. o CI/NIT)</label>
                            <input type="text" name="search" value="{{ $search }}" placeholder="N° referencia o CI/NIT" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0B628D] focus:border-[#0B628D]">
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-end gap-2">
                        <button type="submit" class="h-10 bg-[#0B628D] text-white py-2 px-4 rounded-md hover:bg-[#0A4D75] transition-colors">Filtrar</button>
                        <a href="{{ route('reports.quotations') }}" class="h-10 bg-gray-200 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-300 transition-colors">Mostrar todo</a>

                        <!-- Botón de exportación a Excel (integrado visualmente, pero separado en funcionamiento) -->
                        <button type="button" id="exportExcelBtn" class="h-10 bg-green-600 text-white py-2 px-3 rounded-md hover:bg-green-700 transition-colors flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Excel
                        </button>
                    </div>
                </form>

                <!-- Formulario oculto para exportación a Excel -->
                <form id="exportForm" action="{{ route('reports.export.quotations.excel') }}" method="POST" class="hidden">
                    @csrf
                    <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                    <input type="hidden" name="date_to" value="{{ $dateTo }}">
                    <input type="hidden" name="user_id" value="{{ $userId }}">
                    <input type="hidden" name="status" value="{{ $status ?? '' }}">
                    <input type="hidden" name="search" value="{{ $search }}">
                </form>
            </div>
        </div>

        <!-- Tabla de cotizaciones -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Usuario
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Cliente (CI/NIT)
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                N° Cotización
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Costo total
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Fecha de creación
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Estado
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($quotations as $quotation)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $quotation->user->name ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $quotation->customer->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $quotation->customer->NIT }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $quotation->reference_number }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ number_format($quotation->amount, 2) }} {{ $quotation->currency }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $quotation->created_at->format('d/m/Y H:i') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if (strtolower($quotation->status) == 'pending')
                                        <div class="text-sm text-white bg-yellow-500 rounded-full px-3 py-1 inline-flex items-center justify-center">
                                            <span class="mr-1 font-bold">•</span> Pendiente
                                        </div>
                                    @elseif (strtolower($quotation->status) == 'accepted')
                                        <div class="text-sm text-white bg-green-500 rounded-full px-3 py-1 inline-flex items-center justify-center">
                                            <span class="mr-1 font-bold">•</span> Aceptada
                                        </div>
                                    @else
                                        <div class="text-sm text-white bg-red-500 rounded-full px-3 py-1 inline-flex items-center justify-center">
                                            <span class="mr-1 font-bold">•</span> Rechazada
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                    No se encontraron cotizaciones con los filtros aplicados
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Script para actualizar el formulario de exportación cuando cambia el de filtrado
        const filterForm = document.getElementById('filterForm');
        const exportForm = document.getElementById('exportForm');
        const exportButton = document.getElementById('exportExcelBtn');

        // Función para actualizar los inputs ocultos del formulario de exportación
        function updateExportForm() {
            const dateFrom = filterForm.querySelector('input[name="date_from"]').value;
            const dateTo = filterForm.querySelector('input[name="date_to"]').value;
            const userId = filterForm.querySelector('select[name="user_id"]').value;
            const status = filterForm.querySelector('select[name="status"]').value;
            const search = filterForm.querySelector('input[name="search"]').value;

            exportForm.querySelector('input[name="date_from"]').value = dateFrom;
            exportForm.querySelector('input[name="date_to"]').value = dateTo;
            exportForm.querySelector('input[name="user_id"]').value = userId;
            exportForm.querySelector('input[name="status"]').value = status;
            exportForm.querySelector('input[name="search"]').value = search;
        }

        // Actualizar y enviar el formulario de exportación cuando se hace clic en el botón de Excel
        exportButton.addEventListener('click', function(e) {
            e.preventDefault();
            updateExportForm();
            exportForm.submit();
        });

        // También mantener actualizados los valores cuando cambie cualquier campo
        filterForm.addEventListener('change', updateExportForm);
    });
    </script>
@endsection
