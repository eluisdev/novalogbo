@props(['incoterms', 'index' => 0, 'product' => null, 'isTemplate' => false])

@php
    $classes = $isTemplate ? 'hidden product-template' : 'product-block';
@endphp

<div
    class="{{ $classes }} rounded-lg shadow-[0_0_15px_rgba(0,0,0,0.30)] p-6 mb-6 bg-white relative overflow-visible">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre (Opcional)</label>
            <input type="text" name="products[{{ $index }}][product_name]"
                value="{{ $product->product_name ?? '' }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Origen *</label>
            <select name="products[{{ $index ?? '' }}][origin_id]"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 origin-select">
                <option value="">Seleccionar</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Destino *</label>
            <select name="products[{{ $index ?? '' }}][destination_id]"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 destiny-select">
                <option value="">Seleccionar</option>
            </select>
        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mt-3">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Peso (kg) *</label>
            <input type="number" step="0.01" name="products[{{ $index ?? '' }}][weight]"
                value="{{ $product->weight ?? '' }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Incoterm *</label>
            <select name="products[{{ $index ?? '' }}][incoterm_id]"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                <option value="">Seleccionar</option>
                @foreach ($incoterms as $incoterm)
                    <option value="{{ $incoterm->id }}" @if (isset($product) && $product->incoterm_id == $incoterm->id) selected @endif>
                        {{ $incoterm->code }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Cantidad *</label>
            <div class="flex items-center gap-2">
                <!-- Primer número (1) -->
                <input type="number" id="quantity_part1_{{ $index ?? '' }}"
                    value="{{ isset($product->quantity) ? explode(' x ', $product->quantity)[0] : 1 }}"
                    class="w-16 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 quantity-input"
                    data-index="{{ $index ?? '' }}" min="1">

                <span class="text-gray-600">X</span>

                <!-- Segundo número (40) -->
                <input type="number" id="quantity_part2_{{ $index ?? '' }}"
                    value="{{ isset($product->quantity) ? explode(' x ', $product->quantity)[1] : 40 }}"
                    class="w-16 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 quantity-input"
                    data-index="{{ $index ?? '' }}" min="1">

                <!-- Campo REAL que se enviará (hidden) -->
                <input type="hidden" name="products[{{ $index ?? '' }}][quantity]"
                    id="real_quantity_{{ $index ?? '' }}" value="{{ $product->quantity ?? '1 x 40' }}">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Cantidad desplegable *</label>
            <div class="flex gap-2">
                <select name="products[{{ $index ?? '' }}][quantity_description_id]"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    <option value="1" @if (isset($product) && $product->quantity_type == '1') selected @endif>Caja</option>
                    <option value="2" @if (isset($product) && $product->quantity_type == '2') selected @endif>Entero</option>
                    <option value="3" @if (isset($product) && $product->quantity_type == '3') selected @endif>nose</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Volumen *</label>
            <div class="flex gap-2">
                <!-- Input para el valor numérico -->
                <input type="number" step="0.01" name="products[{{ $index ?? '' }}][volume]"
                    value="{{ $product->volume ?? old('products.' . $index . '.volume', '') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">

                <!-- Select para la unidad de medida -->
                <select name="products[{{ $index ?? '' }}][volume_unit]"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    <option value="kg_vol" @selected(isset($product) && $product->volume_unit == 'kg_vol')>vol/kg</option>
                    <option value="m3" @selected(isset($product) && $product->volume_unit == 'm3')>M³</option>
                </select>
            </div>
        </div>

        @unless ($isTemplate)
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
        @endunless
        <input type="hidden" name="products[{{ $index ?? '' }}][description]"
            value="sin description">
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Actualiza el campo oculto cuando cambian los inputs
        document.querySelectorAll('.quantity-input').forEach(input => {
            input.addEventListener('input', function() {
                const index = this.getAttribute('data-index');
                const part1 = document.getElementById(`quantity_part1_${index}`).value;
                const part2 = document.getElementById(`quantity_part2_${index}`).value;

                // Combina los valores en "1 x 40" y los guarda en el campo real
                document.getElementById(`real_quantity_${index}`).value = `${part1} x ${part2}`;
            });
        });
    });
</script>
