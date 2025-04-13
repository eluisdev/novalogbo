@php
    $services = isset($quotation_data) ? $quotation_data['formSelects']['services'] : $services;
@endphp

<div class="p-6 border-b-2 border-blue-600 bg-white shadow-sm">
    <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-6">
        <div class="flex items-center">
            <h3 class="text-lg font-semibold text-gray-800">Servicios Adicionales</h3>
        </div>
        <p class="text-sm text-gray-500 sm:ml-4">Seleccione los servicios requeridos para su cotización</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
        @foreach ($services as $service)
            @php
                $oldValue = old("services.{$service->id}");
                $formValue = $quotation_data['formData']['services'][$service->id] ?? null;

                $isIncluded = $oldValue === 'include' || (!$oldValue && $formValue === 'include');
                $isExcluded = $oldValue === 'exclude' || (!$oldValue && $formValue === 'exclude');
                $isNone =
                    $oldValue === 'none' || (!$oldValue && (!$formValue || $formValue === '' || $formValue === 'none'));

                $bgColor = $isIncluded ? 'bg-green-100' : ($isExcluded ? 'bg-red-100' : 'bg-white');
            @endphp

            <div class="rounded-xl border border-gray-200 transition-all duration-200 shadow-sm hover:shadow-md p-4 {{ $bgColor }}"
                data-service-id="{{ $service->id }}">
                <h4 class="text-base font-medium text-gray-800 mb-3">
                    {{ $service->name }}
                </h4>

                <div class="flex flex-col gap-2">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="radio" name="services[{{ $service->id }}]" value="include"
                            class="service-radio h-4 w-4 text-green-500 focus:ring-green-400 border-gray-300"
                            @checked($isIncluded)>
                        <span class="ml-2 text-sm font-medium text-gray-700">Incluir</span>
                    </label>

                    <label class="inline-flex items-center cursor-pointer">
                        <input type="radio" name="services[{{ $service->id }}]" value="exclude"
                            class="service-radio h-4 w-4 text-red-500 focus:ring-red-400 border-gray-300"
                            @checked($isExcluded)>
                        <span class="ml-2 text-sm font-medium text-gray-700">Excluir</span>
                    </label>

                    <label class="inline-flex items-center cursor-pointer">
                        <input type="radio" name="services[{{ $service->id }}]" value="none"
                            class="service-radio h-4 w-4 text-gray-500 focus:ring-gray-400 border-gray-300"
                            @checked($isNone)>
                        <span class="ml-2 text-sm font-medium text-gray-700">Ninguno</span>
                    </label>
                </div>
            </div>
        @endforeach
    </div>
</div>
