@extends('layouts.admin')

@section('dashboard-option')

    <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
        <div
            class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white rounded-xl shadow-sm p-3 mb-6 border border-gray-200">
            <div class="flex items-center gap-6 sm:flex-row flex-col">
                <h2 class="text-xl font-black text-gray-800">
                    <span class="text-[#0B628D]">Tipos de cambio</span>
                </h2>

                <!-- Campo de búsqueda -->
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" id="searchInput"
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-[#0B628D] focus:border-[#0B628D] sm:text-sm"
                        placeholder="Buscar tipo de cambio...">
                </div>
            </div>

            <div class="flex space-x-2">
                <a href="{{ route('exchange-rates.create') }}"
                    class="flex items-center justify-center px-4 py-2 bg-[#0B628D] hover:bg-[#19262c] text-white text-sm font-medium rounded-lg transition-colors duration-200 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Crear tasa
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
                                Moneda origen
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Moneda destino
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tasa
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Fecha
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Estado
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody id="exchange-ratesTableBody" class="bg-white divide-y divide-gray-200">
                        @if (count($exchangeRates) === 0)
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                    No hay tipos de cambio registrados
                                </td>
                            </tr>
                        @else
                            @foreach ($exchangeRates as $exchangeRate)
                                <tr class="exchange-rate-row hover:bg-gray-50 transition-colors duration-150"
                                    data-source="{{ strtolower($exchangeRate->source_currency) }}"
                                    data-target="{{ strtolower($exchangeRate->target_currency) }}"
                                    data-rate="{{ $exchangeRate->rate }}"
                                    data-status="{{ $exchangeRate->active ? 'activo' : 'inactivo' }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $exchangeRate->source_currency }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $exchangeRate->target_currency }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ number_format($exchangeRate->rate, 3) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500">
                                            {{ \Carbon\Carbon::parse($exchangeRate->date)->format('d/m/Y') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if ($exchangeRate->active)
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Activo
                                            </span>
                                        @else
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Inactivo
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-center space-x-2">
                                            <a href="{{ route('exchange-rates.edit', $exchangeRate->id) }}"
                                                class="text-yellow-600 hover:text-yellow-900 p-1 rounded-full hover:bg-yellow-50 transition-colors duration-200"
                                                title="Editar">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>
                                            <x-delete-button route="exchange-rates.destroy" :id="$exchangeRate->id" />
                                            <x-active-button route="exchange-rates.toggleStatus" :id="$exchangeRate->id" />
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const exchangeRateRows = document.querySelectorAll('.exchange-rate-row');
            const noResultsRow = document.createElement('tr');
            noResultsRow.innerHTML =
                '<td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No se encontraron resultados</td>';

            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                let hasResults = false;

                exchangeRateRows.forEach(row => {
                    const source = row.getAttribute('data-source');
                    const target = row.getAttribute('data-target');
                    const rate = row.getAttribute('data-rate');
                    const status = row.getAttribute('data-status');

                    if (source.includes(searchTerm) ||
                        target.includes(searchTerm) ||
                        rate.includes(searchTerm) ||
                        status.includes(searchTerm)) {
                        row.style.display = '';
                        hasResults = true;
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Mostrar mensaje si no hay resultados
                const tableBody = document.getElementById('exchange-ratesTableBody');
                const existingNoResults = tableBody.querySelector('.no-results');

                if (!hasResults && exchangeRateRows.length > 0) {
                    if (!existingNoResults) {
                        noResultsRow.classList.add('no-results');
                        tableBody.appendChild(noResultsRow);
                    }
                } else {
                    if (existingNoResults) {
                        tableBody.removeChild(existingNoResults);
                    }
                }
            });
        });
    </script>
@endsection
