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
        <div
            class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white rounded-xl shadow-sm p-4 mb-6 border border-gray-200">
            <div class="flex sm:flex-row flex-col items-center gap-6">
                <h2 class="text-xl font-black text-gray-800">
                    <span class="text-[#0B628D]">Reporte de Cotizaciones</span>
                </h2>
            </div>
        </div>

        <div class="bg-white p-2 rounded-lg shadow-sm border border-gray-200 mb-5">
            <div class="flex sm:flex-row flex-col items-center justify-around text-lg">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Total:</span>
                    <span id="totalCount" class="font-semibold">0</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Pendientes:</span>
                    <span id="pendingCount" class="font-semibold text-yellow-600">0</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Aceptadas:</span>
                    <span id="acceptedCount" class="font-semibold text-green-600">0</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Rechazadas:</span>
                    <span id="refusedCount" class="font-semibold text-red-600">0</span>
                </div>
            </div>
        </div>
        <!-- Filtros y estadísticas -->
        <div class="grid grid-cols-1 mb-6">
            <!-- Filtros por fecha -->
            <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                <div class="space-y-2">
                    <div class="flex justify-between gap-2 items-center sm:flex-row flex-col">
                        <div class="flex-1">
                            <label class="block text-sm text-gray-600 mb-1">Fecha inicial</label>
                            <input type="date" id="dateFrom" name="dateFrom"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0B628D] focus:border-[#0B628D]">
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm text-gray-600 mb-1">Fecha final</label>
                            <input type="date" id="dateTo" name="dateTo"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#0B628D] focus:border-[#0B628D]">
                        </div>
                        <div class="flex flex-end gap-2 sm:mt-6">
                            <button id="filterButton"
                                class="w-auto h-10 bg-[#0B628D] text-white py-2 px-4 rounded-md hover:bg-[#0A4D75] transition-colors">Filtrar</button>
                            <button id="resetButton"
                                class="w-auto h-10 bg-gray-200 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-300 transition-colors">Mostrar
                                todo</button>
                        </div>
                    </div>



                    <!-- Formulario para exportar a Excel -->
                    <form id="exportForm" action="{{ route('quotations.create') }}" method="POST" class="pt-2">
                        @csrf
                        <input type="hidden" name="export_date_from" id="exportDateFrom">
                        <input type="hidden" name="export_date_to" id="exportDateTo">
                        <button type="submit"
                            class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 transition-colors flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Exportar a Excel
                        </button>
                    </form>
                </div>
            </div>

        </div>

        <!-- Tabla de cotizaciones simplificada -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
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
                                Fecha de creación
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tiempo transcurrido
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Estado
                            </th>
                        </tr>
                    </thead>
                    <tbody id="quotationsTableBody" class="bg-white divide-y divide-gray-200">
                        @if (count($quotations) === 0)
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                    No hay cotizaciones registradas
                                </td>
                            </tr>
                        @else
                            @foreach ($quotations as $quotation)
                                @php
                                    $createdAt = \Carbon\Carbon::parse($quotation->created_at);
                                    $now = \Carbon\Carbon::now();
                                    $diff = $createdAt->diff($now);

                                    $days = $diff->d;
                                    $hours = $diff->h;
                                    $minutes = $diff->i;

                                    $timeElapsed = '';
                                    if ($days > 0) {
                                        $timeElapsed .= $days . ' día' . ($days > 1 ? 's' : '');
                                    }
                                    if ($hours > 0) {
                                        if (!empty($timeElapsed)) {
                                            $timeElapsed .= ', ';
                                        }
                                        $timeElapsed .= $hours . ' hora' . ($hours > 1 ? 's' : '');
                                    }
                                    if ($days === 0 && $hours === 0) {
                                        $timeElapsed .= $minutes . ' minuto' . ($minutes > 1 ? 's' : '');
                                    }
                                @endphp
                                <tr class="quotation-row hover:bg-gray-50 transition-colors duration-150"
                                    data-date="{{ $quotation->created_at }}"
                                    data-status="{{ strtolower($quotation->status) }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $quotation->customer->NIT }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $quotation->reference_number }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ number_format($quotation->amount, 2) }}
                                            {{ $quotation->currency }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $createdAt->format('d/m/Y H:i') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $timeElapsed }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if (strtolower($quotation->status) == 'pending')
                                            <div
                                                class="text-sm text-white bg-yellow-500 rounded-full px-3 py-1 inline-flex items-center justify-center">
                                                <span class="mr-1 font-bold">•</span> Pendiente
                                            </div>
                                        @elseif (strtolower($quotation->status) == 'accepted')
                                            <div
                                                class="text-sm text-white bg-green-500 rounded-full px-3 py-1 inline-flex items-center justify-center">
                                                <span class="mr-1 font-bold">•</span> Aceptada
                                            </div>
                                        @else
                                            <div
                                                class="text-sm text-white bg-red-500 rounded-full px-3 py-1 inline-flex items-center justify-center">
                                                <span class="mr-1 font-bold">•</span> Rechazada
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dateFromInput = document.getElementById('dateFrom');
            const dateToInput = document.getElementById('dateTo');
            const filterButton = document.getElementById('filterButton');
            const resetButton = document.getElementById('resetButton');
            const exportForm = document.getElementById('exportForm');
            const exportDateFrom = document.getElementById('exportDateFrom');
            const exportDateTo = document.getElementById('exportDateTo');
            const quotationRows = document.querySelectorAll('.quotation-row');

            // Elementos de conteo
            const totalCountElement = document.getElementById('totalCount');
            const pendingCountElement = document.getElementById('pendingCount');
            const acceptedCountElement = document.getElementById('acceptedCount');
            const refusedCountElement = document.getElementById('refusedCount');

            // Función para actualizar los contadores
            function updateCounters() {
                let total = 0;
                let pending = 0;
                let accepted = 0;
                let refused = 0;

                quotationRows.forEach(row => {
                    if (row.style.display !== 'none') {
                        total++;
                        const status = row.getAttribute('data-status');

                        if (status === 'pending') pending++;
                        else if (status === 'accepted') accepted++;
                        else if (status === 'refused') refused++;
                    }
                });

                totalCountElement.textContent = total;
                pendingCountElement.textContent = pending;
                acceptedCountElement.textContent = accepted;
                refusedCountElement.textContent = refused;
            }

            // Función para filtrar por fecha
            function filterByDate() {
                const dateFrom = dateFromInput.value ? new Date(dateFromInput.value) : null;
                const dateTo = dateToInput.value ? new Date(dateToInput.value) : null;

                // Actualizar campos ocultos del formulario de exportación
                exportDateFrom.value = dateFromInput.value;
                exportDateTo.value = dateToInput.value;

                quotationRows.forEach(row => {
                    const rowDateStr = row.getAttribute('data-date');
                    const rowDate = new Date(rowDateStr);
                    let shouldShow = true;

                    if (dateFrom && rowDate < dateFrom) {
                        shouldShow = false;
                    }

                    if (dateTo) {
                        // Ajustar la fecha final para incluir todo el día
                        const endOfDay = new Date(dateTo);
                        endOfDay.setHours(23, 59, 59, 999);

                        if (rowDate > endOfDay) {
                            shouldShow = false;
                        }
                    }

                    row.style.display = shouldShow ? '' : 'none';
                });

                updateCounters();
                checkNoResults();
            }

            // Función para resetear los filtros
            function resetFilters() {
                dateFromInput.value = '';
                dateToInput.value = '';
                exportDateFrom.value = '';
                exportDateTo.value = '';

                quotationRows.forEach(row => {
                    row.style.display = '';
                });

                updateCounters();
            }

            // Event listeners
            filterButton.addEventListener('click', filterByDate);
            resetButton.addEventListener('click', resetFilters);

            // Inicializar contadores al cargar la página
            updateCounters();

            // Configurar fechas de exportación al cargar si hay filtros aplicados
            if (dateFromInput.value || dateToInput.value) {
                exportDateFrom.value = dateFromInput.value;
                exportDateTo.value = dateToInput.value;
            }
        });
    </script>
@endsection
