@php
    $layout = Auth::user()->role_id == '1' ? 'layouts.admin' : 'layouts.operator';
    $costs = isset($quotation_data) ? $quotation_data['formSelects']['costs'] : $costs;
    $chargesData = $quotation_data['formData']['charges'] ?? [];
@endphp

{{-- {{dd($quotation_data)}} --}}
@extends($layout)
{{-- TODO: Cambiar informacion por operacion --}}
@section('dashboard-option')
    <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
        <div
            class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white rounded-xl shadow-sm p-3 mb-6 border border-gray-200">
            <h2 class="text-xl font-black text-gray-800">
                <span class="text-[#0B628D]">Número de operacion:
                    {{ $quotation_data['formData']['reference_number'] }}</span>
            </h2>

            <div class="flex sm:flex-row flex-col gap-2">


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

        <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200 mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Información General</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-6">
                <!-- Columna 1: Moneda y Tipo de Cambio -->
                <div class="space-y-4">
                    <div class="border-b border-gray-100 pb-2">
                        <p class="text-sm font-medium text-gray-500">Moneda</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $quotation_data['formData']['currency'] }}</p>
                    </div>
                    <div class="border-b border-gray-100 pb-2">
                        <p class="text-sm font-medium text-gray-500">Tipo de Cambio</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $quotation_data['formData']['exchange_rate'] }}
                        </p>
                    </div>
                </div>

                <!-- Columna 2: Datos del Cliente -->
                <div class="space-y-4">
                    <div class="border-b border-gray-100 pb-2">
                        <p class="text-sm font-medium text-gray-500">NIT Cliente</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $quotation_data['formData']['NIT'] }}</p>
                    </div>
                    <div class="border-b border-gray-100 pb-2">
                        <p class="text-sm font-medium text-gray-500">Nombre Cliente</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $quotation_data['formData']['customer']['name'] }}
                        </p>
                    </div>
                </div>

                <!-- Columna 3: Contacto del Cliente -->
                <div class="space-y-4">
                    <div class="border-b border-gray-100 pb-2">
                        <p class="text-sm font-medium text-gray-500">Correo Cliente</p>
                        <p class="text-lg font-semibold text-gray-900">
                            {{ $quotation_data['formData']['customer']['email'] }}</p>
                    </div>
                    <div class="border-b border-gray-100 pb-2">
                        <p class="text-sm font-medium text-gray-500">Teléfono Cliente</p>
                        <p class="text-lg font-semibold text-gray-900">
                            {{ $quotation_data['formData']['customer']['phone'] }}</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="border-b border-gray-100 pb-2">
                        <p class="text-sm font-medium text-gray-500">Estado de operacion</p>
                        <p class="text-lg font-semibold text-gray-900">
                            {{ $quotation_data['formData']['customer']['email'] }}</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
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
                    <p class="text-sm text-gray-500">Agregue los costos y cargos aplicables a esta cotización</p>
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
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-700">
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
                    <div id="selectedCosts" class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6">
                        @foreach ($costs as $cost)
                            @php
                                $oldEnabled = old("costs.{$cost->id}.enabled", null);
                                $costEnabled = isset($quotation_data['formData']['costs'][$cost->id]);
                                $amount = old(
                                    "costs.{$cost->id}.amount",
                                    $quotation_data['formData']['costs'][$cost->id]['amount'] ?? '',
                                );
                                $isEnabled = $oldEnabled !== null ? (bool) $oldEnabled : $costEnabled;
                            @endphp

                            @if ($isEnabled)
                                <div class="cost-item bg-white p-4 rounded-lg border border-gray-200 shadow-sm"
                                    data-cost-id="{{ $cost->id }}">
                                    <div class="flex justify-between items-center mb-3">
                                        <h4 class="font-medium text-gray-800">{{ $cost->name }}</h4>
                                        <button type="button" class="text-red-500 hover:text-red-700 text-lg"
                                            onclick="removeCost('{{ $cost->id }}')">&times;</button>
                                    </div>

                                    <div class="relative rounded-md shadow-sm">
                                        <input type="number" step="0.01" min="0"
                                            name="costs[{{ $cost->id }}][amount]" value="{{ $amount }}"
                                            class="block w-full pl-7 pr-12 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="0.00" required>
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">USD</span>
                                        </div>
                                    </div>

                                    <input type="hidden" name="costs[{{ $cost->id }}][enabled]" value="1">
                                    <input type="hidden" name="costs[{{ $cost->id }}][cost_id]"
                                        value="{{ $cost->id }}">
                                    <input type="hidden" name="costs[{{ $cost->id }}][concept]"
                                        value="{{ $cost->name }}">
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
                    <div id="selectedCharges" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach ($chargesData as $id => $charge)
                            <div class="charge-item bg-white p-4 rounded-lg border border-gray-200 shadow-sm"
                                data-charge-id="{{ $id }}">
                                <div class="flex justify-between items-center mb-3">
                                    <h4 class="font-medium text-gray-800">{{ $charge['concept'] }}</h4>
                                    <button type="button" class="text-red-500 hover:text-red-700 text-lg"
                                        onclick="removeCharge('{{ $id }}')">&times;</button>
                                </div>

                                <div class="relative rounded-md shadow-sm">
                                    <input type="number" step="0.01" min="0"
                                        name="charges[{{ $id }}][amount]" value="{{ $charge['amount'] }}"
                                        class="block w-full py-2 px-4 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500"
                                        placeholder="0.00" required>
                                </div>

                                <input type="hidden" name="charges[{{ $id }}][enabled]" value="1">
                                <input type="hidden" name="charges[{{ $id }}][concept]"
                                    value="{{ $charge['concept'] }}">
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>

        <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200 my-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Area contable</h3>
            </div>

            <div class="p-3 mb-6 flex justify-between sm:flex-row flex-col max-sm:gap-4">
                <form action="{{ route('quotations.invoice.download') }}" method="POST" class="w-full sm:w-auto">
                    @csrf
                    <input type="hidden" name="quotation_id" value="{{ $quotation_data['formData']['id'] }}" />
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
                    <input type="hidden" name="quotation_id" value="{{ $quotation_data['formData']['id'] }}" />
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

        </div>
    </div>


    <script>
        const allCosts = @json($costs->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->values());

        function searchCosts(e) {
            const query = e.target.value.toLowerCase();
            const results = document.getElementById('searchCostResults');
            results.innerHTML = '';
            if (!query) return;

            const filtered = allCosts.filter(c => c.name.toLowerCase().includes(query));
            if (filtered.length) {
                filtered.forEach(c => {
                    const el = document.createElement('div');
                    el.className = 'cursor-pointer hover:bg-gray-100 px-4 py-2';
                    el.innerText = c.name;
                    el.onclick = () => {
                        addSelectedCost(c);
                        results.classList.add('hidden');
                    };
                    results.appendChild(el);
                });
                results.classList.remove('hidden');
            } else {
                results.classList.add('hidden');
            }
        }

        function addSelectedCost(cost) {
            const container = document.getElementById('selectedCosts');
            const id = cost.id;
            if (container.querySelector(`[data-cost-id="${id}"]`)) return;

            const html = `
            <div class="cost-item bg-white p-4 rounded-lg border border-gray-200 shadow-sm" data-cost-id="${id}">
                <div class="flex justify-between items-center mb-3">
                    <h4 class="font-medium text-gray-800">${cost.name}</h4>
                    <button type="button" class="text-red-500 hover:text-red-700 text-lg" onclick="removeCost('${id}')">&times;</button>
                </div>
                <div class="relative rounded-md shadow-sm">
                    <input type="number" step="0.01" min="0" name="costs[${id}][amount]"
                        class="block w-full pl-7 pr-12 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                        placeholder="0.00" required>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <span class="currency-code text-gray-500 sm:text-sm">USD</span>
                    </div>
                </div>
                <input type="hidden" name="costs[${id}][enabled]" value="1">
                <input type="hidden" name="costs[${id}][cost_id]" value="${id}">
                <input type="hidden" name="costs[${id}][concept]" value="${cost.name}">
            </div>
        `;
            container.insertAdjacentHTML('beforeend', html);
        }

        function addManualCost() {
            const name = document.getElementById('manualCostName').value.trim();
            if (!name) return;
            const id = 'manual_' + Date.now();
            addSelectedCost({
                id,
                name
            });
            allCosts.push({
                id,
                name
            });
            document.getElementById('manualCostName').value = '';
        }

        function removeCost(id) {
            const el = document.querySelector(`[data-cost-id="${id}"]`);
            if (el) el.remove();
        }

        function addManualCharge() {
            const name = document.getElementById('manualChargeName').value.trim();
            if (!name) return;
            const id = 'manual_' + Date.now();
            const container = document.getElementById('selectedCharges');
            const html = `
            <div class="charge-item bg-white p-4 rounded-lg border border-gray-200 shadow-sm" data-charge-id="${id}">
                <div class="flex justify-between items-center mb-3">
                    <h4 class="font-medium text-gray-800">${name}</h4>
                    <button type="button" class="text-red-500 hover:text-red-700 text-lg" onclick="removeCharge('${id}')">&times;</button>
                </div>
                <div class="relative rounded-md shadow-sm">
                    <input type="number" step="0.01" min="0" name="charges[${id}][amount]"
                         class="block w-full pl-7 pr-12 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                        placeholder="0.00" required>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <span class="currency-code text-gray-500 sm:text-sm">USD</span>
                    </div>
                </div>
                
                <input type="hidden" name="charges[${id}][enabled]" value="1">
                <input type="hidden" name="charges[${id}][concept]" value="${name}">
            </div>
        `;
        
            container.insertAdjacentHTML('beforeend', html);
            document.getElementById('manualChargeName').value = '';
        }

        function removeCharge(id) {
            const el = document.querySelector(`[data-charge-id="${id}"]`);
            if (el) el.remove();
        }
    </script>
@endsection
