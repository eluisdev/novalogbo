<div class="p-6 border-b-2 border-blue-600 bg-white shadow-sm">
    <!-- Encabezado mejorado -->
    <div class="flex items-center mb-6">
        <span class="inline-flex items-center justify-center p-3 rounded-full bg-blue-50 text-blue-600 mr-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </span>
        <div>
            <h3 class="text-lg font-semibold text-gray-800">Costos Logísticos</h3>
            <p class="text-sm text-gray-500">Seleccione los costos aplicables a esta cotización</p>
        </div>
    </div>

    <!-- Grid de costos mejorado -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @foreach ($costs as $cost)
        @php
            $oldEnabled = old("costs.{$cost->id}.enabled", false);
            $oldAmount = old("costs.{$cost->id}.amount", '');
            $isDisabled = !$oldEnabled;
        @endphp
        
        <div class="cost-card bg-white rounded-lg border border-gray-200 hover:border-blue-300 transition-all duration-200 shadow-sm hover:shadow-md overflow-hidden">
            <div class="p-4">
                <div class="flex items-center mb-3">
                    <input type="checkbox" id="cost_{{ $cost->id }}" 
                           name="costs[{{ $cost->id }}][enabled]"
                           value="1" 
                           class="cost-toggle h-4 w-4 focus:ring-blue-500 text-blue-600 border-gray-300 rounded"
                           data-cost-id="{{ $cost->id }}" 
                           onchange="toggleCostFields({{ $cost->id }})"
                           @checked($oldEnabled)>
                    <label for="cost_{{ $cost->id }}" class="ml-2 block text-sm font-medium text-gray-700 cursor-pointer">
                        {{ $cost->name }}
                    </label>
                </div>

                <div class="relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="currency-symbol text-gray-500 sm:text-sm">$</span>
                    </div>
                    <input type="number" step="0.01" min="0"
                           name="costs[{{ $cost->id }}][amount]"
                           value="{{ $oldAmount }}"
                           class="cost-amount block w-full pl-7 pr-12 py-2 text-sm border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                           @disabled($isDisabled)
                           data-cost-id="{{ $cost->id }}" 
                           placeholder="0.00">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <span class="currency-code text-gray-500 sm:text-sm">USD</span>
                    </div>
                </div>
            </div>
            
            <input type="hidden" name="costs[{{ $cost->id }}][cost_id]" value="{{ $cost->id }}">
            <input type="hidden" name="costs[{{ $cost->id }}][concept]" value="sin valor">
            
        </div>
        @endforeach
    </div>
</div>

<script>
    function toggleCostFields(costId) {
        const checkbox = document.getElementById(`cost_${costId}`);
        const costCard = checkbox.closest('.cost-card');
        const amountInput = costCard.querySelector('.cost-amount');
        
        // Toggle disabled state
        amountInput.disabled = !checkbox.checked;
        
        // Visual feedback
        if (checkbox.checked) {
            costCard.classList.add('ring-1', 'ring-blue-200');
            costCard.querySelector('.cost-amount').focus();
        } else {
            costCard.classList.remove('ring-1', 'ring-blue-200');
            costCard.querySelector('.cost-amount').value = '';
        }
    }
</script>