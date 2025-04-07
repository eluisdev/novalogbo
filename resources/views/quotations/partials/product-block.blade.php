@props(['incoterms', 'index' => 0, 'detail' => null, 'isTemplate' => false])

@php
    $classes = $isTemplate ? 'hidden detail-template' : 'detail-block';
@endphp

<div
    class="{{ $classes }} rounded-lg shadow-[0_0_15px_rgba(0,0,0,0.30)] p-6 mb-6 bg-white relative overflow-visible">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
            <input type="text" name="details[{{ $index }}][detail_name]"
                value="{{ $detail->detail_name ?? '' }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Origen *</label>
            <select name="details[{{ $index ?? '' }}][origin_id]"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 origin-select">
                <option value="">Seleccionar</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Destino *</label>
            <select name="details[{{ $index ?? '' }}][destination_id]"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 destiny-select">
                <option value="">Seleccionar</option>
            </select>
        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mt-3">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Peso (kg) *</label>
            <input type="number" step="0.01" name="details[{{ $index ?? '' }}][weight]"
                value="{{ $detail->weight ?? '' }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Incoterm *</label>
            <select name="details[{{ $index ?? '' }}][incoterm_id]"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                <option value="">Seleccionar</option>
                @foreach ($incoterms as $incoterm)
                    <option value="{{ $incoterm->id }}" @if (isset($detail) && $detail->incoterm_id == $incoterm->id) selected @endif>
                        {{ $incoterm->code }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Cantidad *</label>
            <div class="flex items-center gap-2">
                <input type="number" name="details[{{ $index ?? '' }}][unit_quantity]"
                    value="{{ $detail->quantity ?? 1 }}"
                    class="w-16 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    min="1">
                <span class="text-gray-600">X</span>
                <input type="number" name="details[{{ $index ?? '' }}][quantity]"
                    value="{{ $detail->unit_quantity ?? 40 }}"
                    class="w-16 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    min="1">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Cantidad desplegable *</label>
            <div class="flex gap-2">
                <select name="details[{{ $index ?? '' }}][quantity_description]"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    <option value="box" @if (isset($detail) && $detail->quantity_type == 'box') selected @endif>Caja</option>
                    <option value="kg" @if (isset($detail) && $detail->quantity_type == 'entero') selected @endif>Entero</option>
                    <option value="m3" @if (isset($detail) && $detail->quantity_type == 'nose') selected @endif>nose</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Volumen *</label>
            <div class="flex gap-2">
                <!-- Input para el valor numérico -->
                <input type="number" 
                       step="0.01" 
                       name="details[{{ $index ?? '' }}][volume]"
                       value="{{ $detail->volume ?? old('details.'.$index.'.volume', '') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                       >
        
                <!-- Select para la unidad de medida -->
                <select name="details[{{ $index ?? '' }}][volume_unit]"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        >
                    <option value="vol_kg" @selected(isset($detail) && $detail->volume_unit == 'vol_kg')>vol/kg</option>
                    <option value="m3" @selected(isset($detail) && $detail->volume_unit == 'm3')>M³</option>
                </select>
            </div>
        </div>

        @unless ($isTemplate)
            <div class="absolute -top-3 -right-3">
                <button type="button" onclick="removeDetailBlock(this)" aria-label="Eliminar producto"
                    class="flex items-center justify-center w-8 h-8 rounded-full bg-red-500 text-white shadow-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors duration-200 transform hover:scale-105 active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        @endunless
    </div>
</div>