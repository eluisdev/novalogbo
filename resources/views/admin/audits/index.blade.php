@extends('layouts.admin')

@section('dashboard-option')

    @php
        // Mapeo de modelos a traducciones (puede extenderse fácilmente)
        $modelTranslations = [
            'Cost' => 'Costo',
            'QuantityDescription' => 'Descripción de cantidad',
            'Customer' => 'Cliente',
            'User' => 'Usuario',
            'City' => 'Ciudad',
            'Quotation' => 'Cotizacion',
            'ExchangeRate' => 'Tasa de cambio',
            'Product' => 'Producto',
            // Agregar más traducciones aquí según sea necesario
        ];

        // Traducción de acciones
        $actionTranslations = [
            'created' => 'Creación',
            'updated' => 'Actualización',
            'deleted' => 'Eliminación',
            'restored' => 'Restauración',
        ];
    @endphp

    <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
        <div
            class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white rounded-xl shadow-sm p-3 mb-6 border border-gray-200">
            <div class="flex items-center gap-6 sm:flex-row flex-col">
                <h2 class="text-xl font-black text-gray-800">
                    <span class="text-[#0B628D]">Historial</span>
                </h2>

                <!-- Campo de búsqueda -->
                <div class="relative flex sm:flex-row flex-col">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" id="searchInput"
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-[#0B628D] focus:border-[#0B628D] sm:text-sm"
                        placeholder="Buscar en historial...">
                </div>
            </div>
        </div>

        <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Usuario
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nombre completo
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Rol
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Entidad
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acción
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Fecha
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody id="auditsTableBody" class="bg-white divide-y divide-gray-200">
                        @if (count($audits) === 0)
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                                    No hay registros de auditoría
                                </td>
                            </tr>
                        @else
                            @foreach ($audits as $audit)
                                @php
                                    $modelName = class_basename($audit->auditable_type);
                                    $translatedModel = $modelTranslations[$modelName] ?? $modelName;
                                    $translatedAction = $actionTranslations[$audit->action] ?? $audit->action;
                                @endphp
                                <tr class="audit-row hover:bg-gray-50 transition-colors duration-150"
                                    data-username="{{ strtolower($audit->user->username) }}"
                                    data-name="{{ strtolower($audit->user->name) }}"
                                    data-role="{{ strtolower($audit->user->role->description) }}"
                                    data-entity="{{ strtolower($translatedModel) }}"
                                    data-action="{{ strtolower($translatedAction) }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $audit->user->username }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $audit->user->name }}
                                            {{ $audit->user->lastname }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $audit->user->role->description }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $translatedModel }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $translatedAction }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $audit->created_at->format('d/m/Y H:i') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('audits.show', $audit->id) }}"
                                            class="text-[#0B628D] hover:text-[#19262c]">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
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
            const auditRows = document.querySelectorAll('.audit-row');
            const noResultsRow = document.createElement('tr');
            noResultsRow.innerHTML =
                '<td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">No se encontraron resultados</td>';

            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                let hasResults = false;

                auditRows.forEach(row => {
                    const username = row.getAttribute('data-username');
                    const name = row.getAttribute('data-name');
                    const role = row.getAttribute('data-role');
                    const entity = row.getAttribute('data-entity');
                    const action = row.getAttribute('data-action');

                    if (username.includes(searchTerm) ||
                        name.includes(searchTerm) ||
                        role.includes(searchTerm) ||
                        entity.includes(searchTerm) ||
                        action.includes(searchTerm)) {
                        row.style.display = '';
                        hasResults = true;
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Mostrar mensaje si no hay resultados
                const tableBody = document.getElementById('auditsTableBody');
                const existingNoResults = tableBody.querySelector('.no-results');

                if (!hasResults && auditRows.length > 0) {
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
