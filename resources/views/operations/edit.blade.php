@php
    $layout = Auth::user()->role_id == '1' ? 'layouts.admin' : 'layouts.operator';

    // Convertir correctamente el exchange_rate (manejar formatos con comas y puntos)
    $defaultExchangeRate = isset($billingNote->quotation['exchange_rate'])
        ? floatval(str_replace(',', '.', str_replace('.', '', $billingNote->quotation['exchange_rate'])))
        : 1.0;

    $hasOldInput = count(old()) > 0;

    // Función optimizada para obtener valores
    $getFieldValue = function ($index, $field, $default = null) use ($hasOldInput, $billingNote, $defaultExchangeRate) {
        // Caso especial para exchange_rate
        if ($field === 'exchange_rate') {
            $value = old(
                "costsDetails.{$index}.{$field}",
                $billingNote->quotation['costDetails'][$index][$field] ?? $defaultExchangeRate,
            );

            // Manejar diferentes formatos numéricos
            if (is_string($value)) {
                $value = str_replace('.', '', $value); // Eliminar separadores de miles
                $value = str_replace(',', '.', $value); // Convertir coma decimal a punto
            }

            return floatval($value);
        }

        // Para otros campos
        if ($hasOldInput) {
            return old(
                "costsDetails.{$index}.{$field}",
                $billingNote->quotation['costDetails'][$index][$field] ?? $default,
            );
        }

        return $billingNote->quotation['costDetails'][$index][$field] ?? $default;
    };

    // Obtener costDetails directamente de la nota de facturación
    $costsDetails = $costsDetails ?? [];

    // Filtrar costos disponibles (no agregados aún)
    $availableCosts = collect($costs)
        ->reject(function ($cost) use ($costsDetails) {
            return collect($costsDetails)->contains(function ($detail) use ($cost) {
                return isset($detail['concept']) &&
                    mb_strtolower($detail['concept']) === mb_strtolower($cost->name) &&
                    ($detail['type'] ?? null) === 'cost';
            });
        })
        ->values();
@endphp

@extends($layout)

@section('dashboard-option')

    <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">

        <div class="w-full">

            <div
                class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white rounded-xl shadow-sm p-3 mb-6 border border-gray-200">
                <h2 class="text-xl font-black text-gray-800">
                    <span class="text-[#0B628D]">Número de servicio interno: {{ $billingNote['op_number'] }}</span>
                </h2>

                <div class="flex sm:flex-row flex-col gap-2">
                    <div class="flex space-x-2">
                        <a href="{{ route('operations.index') }}"
                            class="flex items-center justify-center px-4 py-2 bg-[#0B628D] hover:bg-[#19262c] text-white text-sm font-medium rounded-lg transition-colors duration-200 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Volver a operaciones
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
                            <p class="text-lg font-semibold text-gray-900">{{ $billingNote->quotation['currency'] }}</p>
                        </div>
                        <div class="border-b border-gray-100 pb-2">
                            <p class="text-sm font-medium text-gray-500">Tipo de Cambio</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $billingNote->quotation['exchange_rate'] }}
                            </p>
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
                            <p class="text-lg font-semibold text-gray-900">{{ $billingNote->quotation['customer']['name'] }}
                            </p>
                        </div>
                    </div>

                    <!-- Columna 3: Contacto del Cliente -->
                    <div class="space-y-4">
                        <div class="border-b border-gray-100 pb-2">
                            <p class="text-sm font-medium text-gray-500">Correo Cliente</p>
                            <p class="text-lg font-semibold text-gray-900">
                                {{ $billingNote->quotation['customer']['email'] }}</p>
                        </div>
                        <div class="border-b border-gray-100 pb-2">
                            <p class="text-sm font-medium text-gray-500">Teléfono Cliente</p>
                            <p class="text-lg font-semibold text-gray-900">
                                {{ $billingNote->quotation['customer']['phone'] }}</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        @if ($errors->any())
            <div class="bg-red-100 text-red-700 p-4 rounded-md mb-4">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('operations.update', $billingNote['id']) }}" class="flex flex-col">
            @csrf
            @method('PUT')
            <div class="p-6 border-b-2 border-blue-600 bg-white shadow-sm rounded-lg">
                <div class="flex items-center mb-6 sm:flex-row flex-col">
                    <span class="inline-flex items-center justify-center p-3 rounded-full bg-blue-50 text-blue-600 mr-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    <div class="flex gap-2 items-center sm:flex-row flex-col">
                        <h3 class="text-lg font-semibold text-gray-800">Costos y Cargos</h3>
                        <p class="text-sm text-gray-500">Agregue los costos y cargos aplicables a esta operación.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- COSTOS -->
                    <div>
                        <h2 class="mb-3 text-lg font-semibold">Costos</h2>

                        <!-- Buscar costos existentes -->
                        <div class="relative max-w-md mb-6">
                            <input type="text" id="costSearch"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Buscar costos..." onkeyup="searchCosts(event)"
                                onblur="setTimeout(() => document.getElementById('searchCostResults').classList.add('hidden'), 200)">
                            <div
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-700">
                                <svg class="fill-current h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path
                                        d="M12.9 14.32a8 8 0 1 1 1.41-1.41l5.35 5.33-1.42 1.42-5.33-5.34zM8 14A6 6 0 1 0 8 2a6 6 0 0 0 0 12z" />
                                </svg>
                            </div>
                        </div>

                        <div id="searchCostResults"
                            class="mt-2 hidden border border-gray-200 rounded-lg max-h-60 overflow-y-auto max-w-md"></div>

                        <!-- Costo manual -->
                        <div class="mt-4 max-w-md">
                            <input type="text" id="manualCostName"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Nombre del nuevo costo">
                            <button type="button" class="mt-2 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                                onclick="addManualCost()">Agregar Costo Manual</button>
                        </div>

                        <!-- Lista de costos -->
                        <div id="selectedCostsDetails" class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6">
                            @foreach ($costsDetails as $index => $detail)
                                @if ($detail['type'] === 'cost')
                                    <div class="cost-item bg-white p-4 rounded-lg border border-gray-200 shadow-sm"
                                        data-index="{{ $index }}" data-id="{{ $detail['id'] ?? '' }}">
                                        <div class="flex justify-between items-center mb-3">
                                            <h4 class="font-medium text-gray-800">{{ $detail['concept'] }}</h4>
                                            <button type="button" class="text-red-500 hover:text-red-700 text-lg"
                                                onclick="removeCostDetail('{{ $index }}', '{{ $detail['id'] ?? '' }}', 'cost')">&times;</button>
                                        </div>

                                        <!-- Monto normal -->
                                        <div class="relative rounded-md shadow-sm mb-3">
                                            <p class="text-sm font-medium text-gray-500 mb-1">Costo</p>
                                            <input type="number" step="0.01" min="0"
                                                name="costsDetails[{{ $index }}][amount]"
                                                value="{{ $detail['amount'] }}"
                                                class="block w-full pl-7 pr-12 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 @error("costsDetails.$index.amount") border-red-500 @enderror"
                                                placeholder="0.00" required>
                                            <div
                                                class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none mt-5">
                                                <span class="text-gray-500 sm:text-sm">{{ $billingNote->quotation['currency'] }}</span>
                                            </div>
                                            @error("costsDetails.$index.amount")
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <!-- Campo is_amount_parallel -->
                                        <div class="flex items-center mb-3">
                                            <input type="hidden"
                                                name="costsDetails[{{ $index }}][is_amount_parallel]"
                                                value="0">
                                            <input type="checkbox" id="parallel_{{ $index }}"
                                                name="costsDetails[{{ $index }}][is_amount_parallel]"
                                                value="1"
                                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded parallel-checkbox"
                                                {{ $detail['is_amount_parallel'] ? 'checked' : '' }}>
                                            <label for="parallel_{{ $index }}"
                                                class="ml-2 block text-sm text-gray-700">
                                                Usar costo paralelo
                                            </label>
                                        </div>

                                        <!-- Monto paralelo -->
                                        <div class="relative rounded-md shadow-sm mb-3">
                                            <p class="text-sm font-medium text-gray-500 mb-1">Costo paralelo</p>
                                            <input type="number" step="0.01" min="0"
                                                name="costsDetails[{{ $index }}][amount_parallel]"
                                                value="{{ $detail['amount_parallel'] }}"
                                                id="amount_parallel_{{ $index }}"
                                                class="block w-full pl-7 pr-12 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 parallel-amount @error("costsDetails.$index.amount_parallel") border-red-500 @enderror"
                                                placeholder="0.00" {{ $detail['is_amount_parallel'] ? 'required' : '' }}>
                                            <div
                                                class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none mt-5">
                                                <span class="text-gray-500 sm:text-sm">{{ $billingNote->quotation['currency'] }}</span>
                                            </div>
                                            @error("costsDetails.$index.amount_parallel")
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <!-- Campo exchange_rate -->
                                        <div class="relative rounded-md shadow-sm">
                                            <p class="text-sm font-medium text-gray-500 mb-1">Tasa actual</p>
                                            <input type="number" step="0.01" min="0"
                                                name="costsDetails[{{ $index }}][exchange_rate]"
                                                value="{{ number_format($detail['exchange_rate'], 2, '.', '') }}"
                                                class="block w-full pl-7 pr-12 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 @error("costsDetails.$index.exchange_rate") border-red-500 @enderror"
                                                placeholder="1.00" required>
                                            <div
                                                class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none mt-5">
                                                <span class="text-gray-500 sm:text-sm">Tasa</span>
                                            </div>
                                            @error("costsDetails.$index.exchange_rate")
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <input type="hidden" name="costsDetails[{{ $index }}][enabled]"
                                            value="1">
                                        <input type="hidden" name="costsDetails[{{ $index }}][concept]"
                                            value="{{ $detail['concept'] }}">
                                        <input type="hidden" name="costsDetails[{{ $index }}][type]"
                                            value="cost">
                                        @if (isset($detail['id']))
                                            <input type="hidden" name="costsDetails[{{ $index }}][id]"
                                                value="{{ $detail['id'] }}">
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <!-- CARGOS -->
                    <div>
                        <h2 class="mb-3 text-lg font-semibold">Cargos</h2>

                        <!-- Cargo manual -->
                        <div class="flex gap-2 mb-4 max-w-md">
                            <input type="text" id="manualChargeName"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                placeholder="Nombre del nuevo cargo">
                            <button type="button" onclick="addManualCharge()"
                                class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Agregar</button>
                        </div>

                        <!-- Lista de cargos -->
                        <div id="selectedChargesDetails" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach ($costsDetails as $index => $detail)
                                @if ($detail['type'] === 'charge')
                                    <div class="charge-item bg-white p-4 rounded-lg border border-gray-200 shadow-sm"
                                        data-index="{{ $index }}">
                                        <div class="flex justify-between items-center mb-3">
                                            <h4 class="font-medium text-gray-800">{{ $detail['concept'] }}</h4>
                                            <button type="button" class="text-red-500 hover:text-red-700 text-lg"
                                                onclick="removeCostDetail('{{ $index }}', '{{ $detail['id'] ?? '' }}', 'charge')">&times;</button>
                                        </div>

                                        <!-- Monto normal -->
                                        <div class="relative rounded-md shadow-sm mb-3">
                                            <p class="text-sm font-medium text-gray-500 mb-1">Costo</p>
                                            <input type="number" step="0.01" min="0"
                                                name="costsDetails[{{ $index }}][amount]"
                                                value="{{ $detail['amount'] }}"
                                                class="block w-full py-2 px-4 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500 @error("costsDetails.$index.amount") border-red-500 @enderror"
                                                placeholder="0.00" required>
                                            <div
                                                class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none mt-5">
                                                <span class="text-gray-500 sm:text-sm">{{ $billingNote->quotation['currency'] }}</span>
                                            </div>
                                            @error("costsDetails.$index.amount")
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <!-- Campo is_amount_parallel -->
                                        <div class="flex items-center mb-3">
                                            <input type="hidden"
                                                name="costsDetails[{{ $index }}][is_amount_parallel]"
                                                value="0">
                                            <input type="checkbox" id="parallel_{{ $index }}"
                                                name="costsDetails[{{ $index }}][is_amount_parallel]"
                                                value="1"
                                                class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded parallel-checkbox"
                                                {{ $detail['is_amount_parallel'] ? 'checked' : '' }}>
                                            <label for="parallel_{{ $index }}"
                                                class="ml-2 block text-sm text-gray-700">
                                                Usar costo paralelo
                                            </label>
                                        </div>

                                        <!-- Monto paralelo -->
                                        <div class="relative rounded-md shadow-sm mb-3">
                                            <p class="text-sm font-medium text-gray-500 mb-1">Costo paralelo</p>
                                            <input type="number" step="0.01" min="0"
                                                name="costsDetails[{{ $index }}][amount_parallel]"
                                                value="{{ $detail['amount_parallel'] }}"
                                                id="amount_parallel_{{ $index }}"
                                                class="block w-full py-2 px-4 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500 @error("costsDetails.$index.amount_parallel") border-red-500 @enderror"
                                                placeholder="0.00" {{ $detail['is_amount_parallel'] ? 'required' : '' }}>
                                            <div
                                                class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none mt-5">
                                                <span class="text-gray-500 sm:text-sm">{{ $billingNote->quotation['currency'] }}</span>
                                            </div>
                                            @error("costsDetails.$index.amount_parallel")
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <!-- Campo exchange_rate -->
                                        <div class="relative rounded-md shadow-sm">
                                            <p class="text-sm font-medium text-gray-500 mb-1">Tasa actual</p>
                                            <input type="number" step="0.01" min="0"
                                                name="costsDetails[{{ $index }}][exchange_rate]"
                                                value="{{ number_format($detail['exchange_rate'], 2, '.', '') }}"
                                                class="block w-full py-2 px-4 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500 @error("costsDetails.$index.exchange_rate") border-red-500 @enderror"
                                                placeholder="1.0000" required>
                                            <div
                                                class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none mt-5">
                                                <span class="text-gray-500 sm:text-sm">Tasa</span>
                                            </div>
                                            @error("costsDetails.$index.exchange_rate")
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <input type="hidden" name="costsDetails[{{ $index }}][enabled]"
                                            value="1">
                                        <input type="hidden" name="costsDetails[{{ $index }}][concept]"
                                            value="{{ $detail['concept'] }}">
                                        <input type="hidden" name="costsDetails[{{ $index }}][type]"
                                            value="charge">
                                        @if (isset($detail['id']))
                                            <input type="hidden" name="costsDetails[{{ $index }}][id]"
                                                value="{{ $detail['id'] }}">
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-full bg-white p-5">
                <button type="submit"
                    class="px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Actualizar datos
                </button>
            </div>
        </form>
    </div>

    <script>
        // Costos disponibles para búsqueda (no agregados aún)
        let availableCosts = @json($availableCosts->map(fn($c) => ['id' => $c->id, 'name' => $c->name]));

        // Contador basado en datos antiguos si existen
        let costDetailsCounter = {{ $hasOldInput ? count(old('costsDetails', [])) : count($costsDetails) }};

        function searchCosts(e) {
            const query = e.target.value.toLowerCase();
            const results = document.getElementById('searchCostResults');
            results.innerHTML = '';
            if (!query) return;

            const filtered = availableCosts.filter(c => c.name.toLowerCase().includes(query));

            if (filtered.length) {
                filtered.forEach(c => {
                    const el = document.createElement('div');
                    el.className = 'cursor-pointer hover:bg-gray-100 px-4 py-2';
                    el.innerText = c.name;
                    el.onclick = () => {
                        addCostDetail('', c.name, 'cost');
                        document.getElementById('costSearch').value = '';
                        results.classList.add('hidden');
                        availableCosts = availableCosts.filter(item => item.id !== c.id);
                    };
                    results.appendChild(el);
                });
                results.classList.remove('hidden');
            } else {
                results.classList.add('hidden');
            }
        }

        function addCostDetail(id, concept, type) {
            concept = concept.toUpperCase();
            const quotationCurrency = @json($billingNote->quotation['currency'] ?? 'USD');
            const exchangeRate = @json($billingNote->quotation['exchange_rate'] ?? '6.96');

            const container = type === 'cost' ?
                document.getElementById('selectedCostsDetails') :
                document.getElementById('selectedChargesDetails');

            const index = costDetailsCounter++;
            const colorClass = type === 'cost' ? 'blue' : 'green';

            // Obtener valores antiguos si existen
            const oldValues = @json(old('costsDetails', []));
            const oldData = oldValues[index] || {};
            const isParallelChecked = oldData.is_amount_parallel || false;

            const html = `
        <div class="${type}-item bg-white p-4 rounded-lg border border-gray-200 shadow-sm" 
             data-index="${index}" data-id="${id}">
            <div class="flex justify-between items-center mb-3">
                <h4 class="font-medium text-gray-800 uppercase">${concept}</h4>
                <button type="button" class="text-red-500 hover:text-red-700 text-lg" 
                    onclick="removeCostDetail('${index}', '${id}', '${type}')">&times;</button>
            </div>

            <!-- Monto normal -->
            <div class="relative rounded-md shadow-sm mb-3">
                <p class="text-sm font-medium text-gray-500 mb-1">${type === 'cost' ? 'Costo' : 'Cargo'}</p>
                <input type="number" step="0.01" min="0" name="costsDetails[${index}][amount]"
                    value="${oldData.amount || ''}"
                    class="block w-full pl-7 pr-12 py-2 border border-gray-300 rounded-md focus:ring-${colorClass}-500 focus:border-${colorClass}-500"
                    placeholder="0.00" required>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none mt-5">
                    <span class="text-gray-500 sm:text-sm">${quotationCurrency}</span>
                </div>
            </div>

            <!-- Campo is_amount_parallel -->
            <div class="flex items-center mb-3">
                <input type="hidden" name="costsDetails[${index}][is_amount_parallel]" value="0">
                <input type="checkbox" id="parallel_${index}" 
                    name="costsDetails[${index}][is_amount_parallel]" value="1"
                    class="h-4 w-4 text-${colorClass}-600 focus:ring-${colorClass}-500 border-gray-300 rounded parallel-checkbox"
                    ${isParallelChecked ? 'checked' : ''}>
                <label for="parallel_${index}" class="ml-2 block text-sm text-gray-700">
                    Usar monto paralelo
                </label>
            </div>

            <!-- Monto paralelo -->
            <div class="relative rounded-md shadow-sm mb-3">
                <p class="text-sm font-medium text-gray-500 mb-1">${type === 'cost' ? 'Costo' : 'Cargo'} paralelo</p>
                <input type="number" step="0.01" min="0" name="costsDetails[${index}][amount_parallel]"
                    value="${oldData.amount_parallel || ''}"
                    id="amount_parallel_${index}"
                    class="block w-full pl-7 pr-12 py-2 border border-gray-300 rounded-md focus:ring-${colorClass}-500 focus:border-${colorClass}-500 parallel-amount"
                    placeholder="0.00" ${isParallelChecked ? 'required' : ''}>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none mt-5">
                    <span class="text-gray-500 sm:text-sm">${quotationCurrency}</span>
                </div>
            </div>

            <!-- Campo exchange_rate -->
            <div class="relative rounded-md shadow-sm">
                <p class="text-sm font-medium text-gray-500 mb-1">Tasa actual</p>
                <input type="number" step="0.01" min="0"
                    name="costsDetails[${index}][exchange_rate]" 
                    value="${oldData.exchange_rate || exchangeRate}"
                    class="block w-full pl-7 pr-12 py-2 border border-gray-300 rounded-md focus:ring-${colorClass}-500 focus:border-${colorClass}-500"
                    placeholder="1.00" required>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none mt-5">
                    <span class="text-gray-500 sm:text-sm">Tasa</span>
                </div>
            </div>

            <input type="hidden" name="costsDetails[${index}][enabled]" value="1">
            <input type="hidden" name="costsDetails[${index}][concept]" value="${concept}">
            <input type="hidden" name="costsDetails[${index}][type]" value="${type}">
        </div>
    `;

            container.insertAdjacentHTML('beforeend', html);

            // Configurar el evento change para el checkbox
            const checkbox = document.getElementById(`parallel_${index}`);
            const amountParallel = document.getElementById(`amount_parallel_${index}`);

            checkbox.addEventListener('change', function() {
                if (this.checked) {
                    amountParallel.setAttribute('required', 'required');
                } else {
                    amountParallel.removeAttribute('required');
                }
            });
        }

        function removeCostDetail(index, id, type) {
            const el = document.querySelector(`[data-index="${index}"]`);
            if (el) el.remove();

            if (type === 'cost' && id && id !== '' && !id.startsWith('manual_')) {
                const concept = el.querySelector('h4').innerText;
                availableCosts.push({
                    id,
                    name: concept
                });
            }

            const disabledInput = document.createElement('input');
            disabledInput.type = 'hidden';
            disabledInput.name = `costsDetails[${index}][enabled]`;
            disabledInput.value = '0';
            document.querySelector('form').appendChild(disabledInput);
        }

        function addManualCost() {
            const name = document.getElementById('manualCostName').value.trim();
            if (!name) return;
            addCostDetail(id = '', name, 'cost');
            document.getElementById('manualCostName').value = '';
        }

        function addManualCharge() {
            const name = document.getElementById('manualChargeName').value.trim();
            if (!name) return;
            addCostDetail(id = '', name, 'charge');
            document.getElementById('manualChargeName').value = '';
        }

        // Configurar los checkboxes existentes al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.parallel-checkbox');
            checkboxes.forEach(checkbox => {
                const index = checkbox.id.split('_')[1];
                const amountField = document.getElementById(`amount_parallel_${index}`);

                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        amountField.setAttribute('required', 'required');
                    } else {
                        amountField.removeAttribute('required');
                    }
                });

                if (checkbox.checked) {
                    amountField.setAttribute('required', 'required');
                }
            });
        });
    </script>
@endsection
