@props(['incoterms', 'index' => 0, 'product' => null, 'isClone' => false])

@php
    $useOld = count(old('products', [])) > 0;
    $uniqueSuffix = $isClone ? '_clone_' . uniqid() : '';

    $defaultProductName = $product->name ?? '';
    $defaultWeight = $product->weight ?? '';
    $defaultIncotermId = $product->incoterm_id ?? '';
    $defaultQuantity = $product->quantity ?? '1 x 40';
    $defaultQuantityDescriptionId = $product->quantity_description_id ?? '1';
    $defaultVolume = $product->volume ?? '';
    $defaultVolumeUnit = $product->volume_unit ?? 'kg_vol';

    $incoterms = isset($quotation_data) ? $quotation_data['formSelects']['incoterms'] : $incoterms;

    $cities = isset($quotation_data['formSelects']['cities']) ? $quotation_data['formSelects']['cities'] : $cities;
    $quantity_descriptions = isset($quotation_data['formSelects']['QuantityDescriptions'])  ? $quotation_data['formSelects']['QuantityDescriptions'] : $QuantityDescriptions;
@endphp

<div class="rounded-lg shadow-[0_0_15px_rgba(0,0,0,0.30)] p-6 mb-6 bg-white relative overflow-visible product-block"
    data-index="{{ $index }}">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
                for="name_{{ $index }}{{ $uniqueSuffix }}">Nombre (Opcional)</label>
            <input type="text" id="name_{{ $index }}{{ $uniqueSuffix }}"
                name="products[{{ $index }}][name]"
                value="{{ $useOld ? old('products.' . $index . '.name') : $defaultProductName }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
            <label for="origin_id_{{ $index }}{{ $uniqueSuffix }}"
                class="block text-sm font-medium text-gray-700 mb-1">Origen *</label>
            <select id="origin_id_{{ $index }}{{ $uniqueSuffix }}"
                name="products[{{ $index }}][origin_id]"
                class="origin-select w-full border border-gray-300 rounded px-3 py-2">
                <option value="">Seleccionar</option>
                @if ($useOld)
                    @php
                        $oldOriginId = old("products.{$index}.origin_id", '');
                        $selectedCity = $oldOriginId
                            ? $cities->firstWhere('id', $oldOriginId)
                            : null;
                    @endphp
                    @if ($selectedCity)
                        <option value="{{ $selectedCity->id }}" selected>{{ $selectedCity->name }} ,
                            {{ $selectedCity->country->name }}</option>
                    @endif
                @elseif(isset($product) && $product->origin_id)
                    @php
                        $selectedCity = $cities->firstWhere('id', $product->origin_id);
                    @endphp
                    @if ($selectedCity)
                        <option value="{{ $selectedCity->id }}" selected>{{ $selectedCity->name }} ,
                            {{ $selectedCity->country->name }}</option>
                    @endif
                @endif
            </select>
        </div>

        <div>
            <label for="destination_id_{{ $index }}{{ $uniqueSuffix }}"
                class="block text-sm font-medium text-gray-700 mb-1">Destino *</label>
            <select id="destination_id_{{ $index }}{{ $uniqueSuffix }}"
                name="products[{{ $index }}][destination_id]"
                class="destiny-select w-full border border-gray-300 rounded px-3 py-2">
                <option value="">Seleccionar</option>
                @if ($useOld)
                    @php
                        $oldDestinationId = old("products.{$index}.destination_id", '');
                        $selectedCity = $oldDestinationId
                            ? $cities->firstWhere('id', $oldDestinationId)
                            : null;
                    @endphp
                    @if ($selectedCity)
                        <option value="{{ $selectedCity->id }}" selected>{{ $selectedCity->name }} ,
                            {{ $selectedCity->country->name }}</option>
                    @endif
                @elseif(isset($product) && $product->destination_id)
                    @php
                        $selectedCity = $cities->firstWhere(
                            'id',
                            $product->destination_id,
                        );
                    @endphp
                    @if ($selectedCity)
                        <option value="{{ $selectedCity->id }}" selected>{{ $selectedCity->name }} ,
                            {{ $selectedCity->country->name }}</option>
                    @endif
                @endif
            </select>
        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mt-3">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
                for="weight_{{ $index }}{{ $uniqueSuffix }}">Peso (kg) *</label>
            <input type="number" step="0.01" id="weight_{{ $index }}{{ $uniqueSuffix }}"
                name="products[{{ $index }}][weight]"
                value="{{ $useOld ? old('products.' . $index . '.weight') : $defaultWeight }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
                for="incoterm_id_{{ $index }}{{ $uniqueSuffix }}">Incoterm *</label>
            <select id="incoterm_id_{{ $index }}{{ $uniqueSuffix }}"
                name="products[{{ $index }}][incoterm_id]"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 incoterm-select">
                <option value="">Seleccionar</option>

                @foreach ($incoterms as $incoterm)
                    <option value="{{ $incoterm->id }}"
                        @if ($useOld) {{ old('products.' . $index . '.incoterm_id') == $incoterm->id ? 'selected' : '' }}
                        @else
                            {{ $defaultIncotermId == $incoterm->id ? 'selected' : '' }} @endif>
                        {{ $incoterm->code }}
                    </option>
                @endforeach

            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
                for="quantity_container_{{ $index }}{{ $uniqueSuffix }}">Cantidad *</label>
            <div class="flex items-center gap-2" id="quantity_container_{{ $index }}{{ $uniqueSuffix }}">
                @php
                    if ($useOld) {
                        $quantityParts = explode(' x ', old('products.' . $index . '.quantity', '1 x 40'));
                    } else {
                        $quantityParts = explode(' x ', $defaultQuantity);
                    }
                @endphp
                <!-- Primer número (1) -->
                <input type="number" id="quantity_part1_{{ $index }}{{ $uniqueSuffix }}"
                    value="{{ $quantityParts[0] ?? 1 }}"
                    class="w-16 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 quantity-input"
                    data-index="{{ $index }}" min="1">

                <span class="text-gray-600">X</span>

                <input type="number" id="quantity_part2_{{ $index }}{{ $uniqueSuffix }}"
                    value="{{ $quantityParts[1] ?? 40 }}"
                    class="w-16 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 quantity-input"
                    data-index="{{ $index }}" min="1">

                <input type="hidden" name="products[{{ $index }}][quantity]"
                    id="real_quantity_{{ $index }}{{ $uniqueSuffix }}"
                    value="{{ $useOld ? old('products.' . $index . '.quantity') : $defaultQuantity }}">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
                for="quantity_description_id_{{ $index }}{{ $uniqueSuffix }}">Cantidad desplegable *</label>
            <div class="flex gap-2">
                <select id="quantity_description_id_{{ $index }}{{ $uniqueSuffix }}"
                    name="products[{{ $index }}][quantity_description_id]"
                    class="quantity-description-select w-full border border-gray-300 rounded px-3 py-2">
                    <option value="">Seleccionar</option>
                    @if ($useOld)
                        @php
                            $oldQuantityDescriptionId = old("products.{$index}.quantity_description_id", '');
                            $selectedDescription = $oldQuantityDescriptionId
                                ? $quantity_descriptions->firstWhere(
                                    'id',
                                    $oldQuantityDescriptionId,
                                )
                                : null;
                        @endphp
                        @if ($selectedDescription)
                            <option value="{{ $selectedDescription->id }}" selected>{{ $selectedDescription->name }}
                            </option>
                        @endif
                    @elseif(isset($product) && isset($product->quantity_description_id))
                        @php
                            $selectedDescription = $quantity_descriptions->firstWhere(
                                'id',
                                $product->quantity_description_id,
                            );
                        @endphp
                        @if ($selectedDescription)
                            <option value="{{ $selectedDescription->id }}" selected>{{ $selectedDescription->name }}
                            </option>
                        @endif
                    @endif
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
                for="volume_container_{{ $index }}{{ $uniqueSuffix }}">Volumen *</label>
            <div class="flex gap-2" id="volume_container_{{ $index }}{{ $uniqueSuffix }}">
                <!-- Input para el valor numérico -->
                <input type="number" step="0.01" id="volume_value_{{ $index }}{{ $uniqueSuffix }}"
                    name="products[{{ $index }}][volume]"
                    value="{{ $useOld ? old('products.' . $index . '.volume') : $defaultVolume }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">

                <!-- Select para la unidad de medida -->
                <select id="volume_unit_{{ $index }}{{ $uniqueSuffix }}"
                    name="products[{{ $index }}][volume_unit]"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    <option value="kg_vol"
                        @if ($useOld) {{ old('products.' . $index . '.volume_unit') == 'kg_vol' ? 'selected' : '' }}
                        @else {{ $defaultVolumeUnit == 'kg_vol' ? 'selected' : '' }} @endif>
                        vol/kg</option>
                    <option value="m3"
                        @if ($useOld) {{ old('products.' . $index . '.volume_unit') == 'm3' ? 'selected' : '' }}
                        @else {{ $defaultVolumeUnit == 'm3' ? 'selected' : '' }} @endif>
                        M³</option>
                </select>
            </div>
        </div>

    </div>
    <input type="hidden" name="products[{{ $index }}][description]" value="sin descripcion">

    <div class="absolute -top-3 -right-3">
        <button type="button" onclick="removeProductBlock(this)" aria-label="Eliminar producto"
            class="flex items-center justify-center w-8 h-8 rounded-full bg-red-500 text-white shadow-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors duration-200 transform hover:scale-105 active:scale-95">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                    clip-rule="evenodd" />
            </svg>
        </button>
    </div>

</div>
