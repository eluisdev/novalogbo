@props(['incoterms', 'index' => 0, 'product' => null, 'isClone' => false])

@php
    $useOld = count(old('products', [])) > 0;
    $defaultProductName = $useOld ? old('products.' . $index . '.name') : $product->name ?? '';
    $defaultWeight = $useOld ? old('products.' . $index . '.weight') : $product->weight ?? '';
    $defaultIncotermId = $useOld ? old('products.' . $index . '.incoterm_id') : $product->incoterm_id ?? '';
    $defaultQuantity = $useOld ? old('products.' . $index . '.quantity') : $product->quantity ?? '1 x 40';
    $defaultQuantityDescriptionId = $useOld
        ? old('products.' . $index . '.quantity_description_id')
        : $product->quantity_description_id ?? '1';
    $defaultVolume = $useOld ? old('products.' . $index . '.volume') : $product->volume ?? '';
    $defaultVolumeUnit = $useOld ? old('products.' . $index . '.volume_unit') : $product->volume_unit ?? 'kg_vol';

    // Manejo simplificado de is_container
    $defaultIsContainer = $useOld
        ? old('products.' . $index . '.is_container', '1') == '1'
        : (isset($product)
            ? (bool) isset($product->is_container) ? $product->is_container : '1'
            : true);

    // Para cantidad por defecto
    if ($useOld && str_contains(old('products.' . $index . '.quantity', '1 x 40'), ' x ')) {
        $quantityParts = explode(' x ', old('products.' . $index . '.quantity'));
    } else {
        $quantityParts = explode(' x ', $defaultQuantity);
    }

    $incoterms = isset($quotation_data) ? $quotation_data['formSelects']['incoterms'] : $incoterms;
    $cities = isset($quotation_data['formSelects']['cities']) ? $quotation_data['formSelects']['cities'] : $cities;
    $quantity_descriptions = isset($quotation_data['formSelects']['QuantityDescriptions'])
        ? $quotation_data['formSelects']['QuantityDescriptions']
        : $QuantityDescriptions;
@endphp

<div class="rounded-lg shadow-[0_0_15px_rgba(0,0,0,0.30)] p-6 mb-6 bg-white relative overflow-visible product-block"
    data-index="{{ $index }}">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="name_{{ $index }}">Nombre
                (Opcional)</label>
            <input type="text" id="name_{{ $index }}" name="products[{{ $index }}][name]"
                value="{{ $defaultProductName }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
            <label for="origin_id_{{ $index }}" class="block text-sm font-medium text-gray-700 mb-1">Origen
                *</label>
            <select id="origin_id_{{ $index }}" name="products[{{ $index }}][origin_id]"
                class="origin-select w-full border border-gray-300 rounded px-3 py-2">
                <option value="">Seleccionar</option>
                @if ($useOld)
                    @php
                        $oldOriginId = old("products.{$index}.origin_id", '');
                        $selectedCity = $oldOriginId ? $cities->firstWhere('id', $oldOriginId) : null;
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
            <label for="destination_id_{{ $index }}"
                class="block text-sm font-medium text-gray-700 mb-1">Destino *</label>
            <select id="destination_id_{{ $index }}" name="products[{{ $index }}][destination_id]"
                class="destiny-select w-full border border-gray-300 rounded px-3 py-2">
                <option value="">Seleccionar</option>
                @if ($useOld)
                    @php
                        $oldDestinationId = old("products.{$index}.destination_id", '');
                        $selectedCity = $oldDestinationId ? $cities->firstWhere('id', $oldDestinationId) : null;
                    @endphp
                    @if ($selectedCity)
                        <option value="{{ $selectedCity->id }}" selected>{{ $selectedCity->name }} ,
                            {{ $selectedCity->country->name }}</option>
                    @endif
                @elseif(isset($product) && $product->destination_id)
                    @php
                        $selectedCity = $cities->firstWhere('id', $product->destination_id);
                    @endphp
                    @if ($selectedCity)
                        <option value="{{ $selectedCity->id }}" selected>{{ $selectedCity->name }} ,
                            {{ $selectedCity->country->name }}</option>
                    @endif
                @endif
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="weight_{{ $index }}">Peso (kg)
                *</label>
            <input type="number" step="0.01" id="weight_{{ $index }}"
                name="products[{{ $index }}][weight]" value="{{ $defaultWeight }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        </div>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-2 mt-3 gap-6">
        <div class="flex gap-3 md:flex-row flex-col">
            <div class="w-full">
                <label class="block text-sm font-medium text-gray-700 mb-1"
                    for="incoterm_id_{{ $index }}">Incoterm *</label>
                <select id="incoterm_id_{{ $index }}" name="products[{{ $index }}][incoterm_id]"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 incoterm-select">
                    <option value="">Seleccionar</option>

                    @foreach ($incoterms as $incoterm)
                        <option value="{{ $incoterm->id }}"
                            {{ $defaultIncotermId == $incoterm->id ? 'selected' : '' }}>
                            {{ $incoterm->code }}
                        </option>
                    @endforeach

                </select>
            </div>
            <div class="">
                <label class="block text-sm font-medium text-gray-700"
                    for="volume_container_{{ $index }}">Volumen *</label>
                <div class="flex gap-2" id="volume_container_{{ $index }}">
                    <!-- Input para el valor numérico -->
                    <input type="number" step="0.01" id="volume_value_{{ $index }}"
                        name="products[{{ $index }}][volume]" value="{{ $defaultVolume }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">

                    <!-- Select para la unidad de medida -->
                    <select id="volume_unit_{{ $index }}" name="products[{{ $index }}][volume_unit]"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="kg_vol" {{ $defaultVolumeUnit == 'kg_vol' ? 'selected' : '' }}>
                            vol/kg</option>
                        <option value="m3" {{ $defaultVolumeUnit == 'm3' ? 'selected' : '' }}>
                            M³</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="flex gap-2 md:flex-row flex-col max-sm:mx-auto">
            <!-- Tipo de carga -->
            <div class="mr-4">
                <label class="block text-sm font-medium text-gray-700">Tipo de carga - cantidad *</label>
                <div class="flex items-center gap-2">
                    <label class="inline-flex items-center mt-2.5">
                        <input type="radio" name="products[{{ $index }}][is_container]" value="1"
                            class="product-type-radio" data-index="{{ $index }}"
                            {{ $defaultIsContainer ? 'checked' : '' }}>
                        <span class="ml-1">Contenedor</span>
                    </label>
                    <label class="inline-flex items-center mt-2.5">
                        <input type="radio" name="products[{{ $index }}][is_container]" value="0"
                            class="product-type-radio" data-index="{{ $index }}"
                            {{ !$defaultIsContainer ? 'checked' : '' }}>
                        <span class="ml-1">Carga suelta</span>
                    </label>
                </div>
            </div>

            <!-- Campos para Contenedor -->
            <div id="container_fields_{{ $index }}" class="{{ $defaultIsContainer ? '' : 'hidden' }}">
                <div class="flex items-center gap-2 md:mt-5.5 justify-center">
                    <input type="number" id="quantity_part1_{{ $index }}" value="{{ $quantityParts[0] ?? 1 }}"
                        class="w-16 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 quantity-input"
                        data-index="{{ $index }}" min="1">

                    <span class="text-gray-600">X</span>

                    <input type="number" id="quantity_part2_{{ $index }}"
                        value="{{ $quantityParts[1] ?? 40 }}"
                        class="w-16 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 quantity-input"
                        data-index="{{ $index }}" min="1">
                </div>
            </div>

            <!-- Campos para Carga suelta -->
            <div id="loose_fields_{{ $index }}" class="{{ !$defaultIsContainer ? '' : 'hidden' }}">
                <div class="flex gap-2 items-center md:mt-5.5 sm:flex-row flex-col">
                    <input type="number" id="loose_quantity_{{ $index }}"
                        value="{{ $useOld ? old('products.' . $index . '.loose_quantity', $quantityParts[0] ?? 1) : $quantityParts[0] ?? 1 }}"
                        class="w-16 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 quantity-input"
                        min="1" data-index="{{ $index }}">

                    <select id="quantity_description_id_{{ $index }}"
                        name="products[{{ $index }}][quantity_description_id]"
                        class="quantity-description-select w-full border border-gray-300 rounded py-2 quantity-input"
                        data-index="{{ $index }}">
                        <option value="">Seleccionar</option>
                        @if ($useOld)
                            @php
                                $oldQuantityDescriptionId = old("products.{$index}.quantity_description_id", '');
                                $selectedDescription = $oldQuantityDescriptionId
                                    ? $quantity_descriptions->firstWhere('id', $oldQuantityDescriptionId)
                                    : null;
                            @endphp
                            @if ($selectedDescription)
                                <option value="{{ $selectedDescription->id }}" selected>
                                    {{ $selectedDescription->name }}
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
                                <option value="{{ $selectedDescription->id }}" selected>
                                    {{ $selectedDescription->name }}
                                </option>
                            @endif
                        @endif
                    </select>
                </div>
            </div>

            <input type="hidden" name="products[{{ $index }}][quantity]"
                id="real_quantity_{{ $index }}" value="{{ $defaultQuantity }}">
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Función para actualizar la cantidad real (oculta)
        function updateRealQuantity(index) {
            const isContainer = document.querySelector(`input[name="products[${index}][is_container]"]:checked`)
                .value;
            console.log(isContainer, "CONTENEDOR");
            if (isContainer === '1') {
                // Para contenedores (part1 x part2)
                const part1Input = document.querySelector(`[id^="quantity_part1_${index}"]`) || document
                    .querySelector(`[id^="quantity_part1_clone_${index}"]`);
                const part2Input = document.querySelector(`[id^="quantity_part2_${index}"]`) || document
                    .querySelector(`[id^="quantity_part2_clone_${index}"]`);
                const realQuantityInput = document.querySelector(`[id^="real_quantity_${index}"]`);
                console.log(part1Input, part2Input, realQuantityInput);

                if (part1Input && part2Input && realQuantityInput) {
                    realQuantityInput.value = `${part1Input.value} x ${part2Input.value}`;
                } else {
                    console.error('No se encontraron los campos de cantidad para contenedor');
                }
            } else {
                // Para carga suelta
                const looseQuantityInput = document.querySelector(`[id^="loose_quantity_${index}"]`);
                const realQuantityInput = document.querySelector(`[id^="real_quantity_${index}"]`);

                if (looseQuantityInput && realQuantityInput) {
                    realQuantityInput.value = looseQuantityInput.value;
                } else {
                    console.error('No se encontraron los campos de cantidad para carga suelta');
                }
            }
        }

        // Event delegation para TODOS los inputs de cantidad (incluyendo clones)
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('quantity-input')) {
                const index = e.target.dataset.index;
                updateRealQuantity(index);
            }
        });

        // Event delegation para TODOS los radio buttons (incluyendo clones)
        document.addEventListener('change', function(e) {
            const number = e.target.name
            const match = number.match(/\[(\d{1,2})\]/)
            const index = match ? parseInt(match[1], 10) : null;
            if (e.target.classList.contains('product-type-radio')) {
                toggleFields(index, e.target.value);
                updateRealQuantity(index);
            }
        });
    });

    // Función para mostrar/ocultar campos según tipo de carga
    function toggleFields(index, isContainer) {
        const container = document.querySelector(`[id^="container_fields_${index}"]`);
        const loose = document.querySelector(`[id^="loose_fields_${index}"]`);

        if (!container || !loose) {
            console.error(`No se encontraron campos para el índice ${index}`);
            return;
        }

        if (isContainer === '1') {
            container.classList.remove('hidden');
            loose.classList.add('hidden');
        } else {
            loose.classList.remove('hidden');
            container.classList.add('hidden');
        }
    }

    // Función para eliminar bloques (sin cambios)
    function removeProductBlock(button) {
        const block = button.closest('.product-block');
        if (block) {
            block.remove();
        }
    }
</script>
