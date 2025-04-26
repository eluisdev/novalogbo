@props(['quotation_data' => null])

<div class="p-6 border-b-2 border-blue-600 bg-white shadow-sm">
    <div class="flex items-center mb-6 sm:flex-row flex-col">
        <span class="inline-flex items-center justify-center p-3 rounded-full bg-blue-50 text-blue-600 mr-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
        </span>
        <h3 class="text-lg font-semibold text-gray-800">Detalles de cotizacion</h3>
        <p class="text-sm text-gray-500 sm:ml-2">Escriba los detalles de la cotizacion</p>
    </div>

    <div class="flex flex-col gap-4">
        <!-- Seguro -->
        <div>
            <label for="insurance" class="block text-sm font-medium text-gray-700 mb-1">Seguro</label>
            <input type="text" id="insurance" name="insurance"
                value="{{ old('insurance', isset($quotation_data) ? $quotation_data['formData']['insurance'] : '') }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                placeholder="Detalles del seguro">
        </div>

        <!-- Forma de pago -->
        <div>
            <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">Forma de Pago</label>
            <input type="text" id="payment_method" name="payment_method"
                value="{{ old('payment_method', isset($quotation_data) ? $quotation_data['formData']['payment_method'] : '') }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                placeholder="Condiciones de pago">
        </div>

        <!-- Validez -->
        <div>
            <label for="validity" class="block text-sm font-medium text-gray-700 mb-1">Validez</label>
            <input type="text" id="validity" name="validity"
                value="{{ old('validity', isset($quotation_data) ? $quotation_data['formData']['validity'] : '') }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                placeholder="Tiempo de validez de la cotización">
        </div>

        <!-- Observaciones -->
        <div class="md:col-span-2">
            <label for="observations" class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
            <textarea id="observations" name="observations" rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                placeholder="Notas adicionales">{{ old('observations', isset($quotation_data) ? $quotation_data['formData']['observations'] : '') }}</textarea>
        </div>
    </div>
</div>
