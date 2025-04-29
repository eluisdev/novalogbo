@php
    $services = isset($quotation_data) ? $quotation_data['formSelects']['services'] : $services;
@endphp

<div class="p-6 border-b-2 border-blue-600 bg-white shadow-sm">
    <div class="flex items-center mb-6 sm:flex-row flex-col ">
        <span class="inline-flex items-center justify-center p-3 rounded-full bg-blue-50 text-blue-600 mr-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        </span>
        <div class="flex justify-center items-center gap-2 sm:flex-row flex-col">

            <h3 class="text-lg font-semibold text-gray-800">Servicios Adicionales</h3>

            <p class="text-sm text-gray-500">Agrege los servicios requeridos para su cotización</p>
        </div>
    </div>
    <!-- Search input -->
    <div class="relative max-w-md">
        <input type="text" id="serviceSearch"
            class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            placeholder="Buscar servicios..." onkeyup="searchServices(event)"
            onblur="setTimeout(() => document.getElementById('searchResults').classList.add('hidden'), 200)"
            autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-700">
            <svg class="fill-current h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                <path
                    d="M12.9 14.32a8 8 0 1 1 1.41-1.41l5.35 5.33-1.42 1.42-5.33-5.34zM8 14A6 6 0 1 0 8 2a6 6 0 0 0 0 12z" />
            </svg>
        </div>
    </div>

    <!-- Search results container -->
    <div id="searchResults" class="mt-2 hidden border border-gray-200 rounded-lg max-h-60 overflow-y-auto max-w-md">
        <!-- Results will be populated here by JavaScript -->
    </div>

    <!-- Selected services will appear here -->
    <div id="selectedServices" class="flex flex-wrap gap-3 mt-4 max-md:justify-center">
        @foreach ($services as $service)
            @php
                $oldValue = old("services.{$service->id}");
                $formValue = $quotation_data['formData']['services'][$service->id] ?? null;
                $status = $oldValue ?? ($formValue ?? null);
            @endphp

            @if ($status === 'include' || $status === 'exclude')
                <div class="flex items-center px-4 py-2 rounded-full text-base 
                    {{ $status === 'include' ? 'bg-green-100 text-green-800 border border-green-300' : 'bg-red-100 text-red-800 border border-red-300' }}"
                    data-service-id="{{ $service->id }}">
                    {{ $service->name }}
                    <select name="services[{{ $service->id }}]"
                        class="ml-2 bg-transparent border-none focus:ring-0 p-0 text-base"
                        onchange="updateServiceStatus(this)">
                        <option value="include" {{ $status === 'include' ? 'selected' : '' }}>Incluir</option>
                        <option value="exclude" {{ $status === 'exclude' ? 'selected' : '' }}>Excluir</option>
                    </select>
                    <button type="button" class="ml-2 text-gray-500 hover:text-gray-700 text-lg"
                        onclick="removeService('{{ $service->id }}')">
                        &times;
                    </button>
                </div>
            @endif
        @endforeach
    </div>
</div>

<script>
    // Array to store all available services
    const allServices = [
        @foreach ($services as $service)
            @php
                $oldValue = old("services.{$service->id}");
                $formValue = $quotation_data['formData']['services'][$service->id] ?? null;
                $alreadySelected = $oldValue === 'include' || $oldValue === 'exclude' || ($formValue === 'include' || $formValue === 'exclude');
            @endphp
            @if (!$alreadySelected)
                {
                    id: "{{ $service->id }}",
                    name: "{{ $service->name }}"
                },
            @endif
        @endforeach
    ];

    // Close results when clicking outside
    document.addEventListener('click', function(event) {
        const searchContainer = document.querySelector('.relative.max-w-md');
        const resultsContainer = document.getElementById('searchResults');

        if (!searchContainer.contains(event.target) && !resultsContainer.contains(event.target)) {
            resultsContainer.classList.add('hidden');
        }
    });

    function searchServices(event) {
        // Close on ESC key
        if (event.key === 'Escape') {
            document.getElementById('searchResults').classList.add('hidden');
            document.getElementById('serviceSearch').blur();
            return;
        }

        const input = document.getElementById('serviceSearch');
        const filter = input.value.toUpperCase();
        const resultsContainer = document.getElementById('searchResults');

        // Clear previous results
        resultsContainer.innerHTML = '';

        if (filter.length < 2) {
            resultsContainer.classList.add('hidden');
            return;
        }

        // Filter services
        const filteredServices = allServices.filter(service =>
            service.name.toUpperCase().includes(filter) &&
            !document.querySelector(`#selectedServices [data-service-id="${service.id}"]`)
        );

        if (filteredServices.length === 0) {
            resultsContainer.innerHTML = '<div class="p-3 text-gray-500">No se encontraron servicios</div>';
            resultsContainer.classList.remove('hidden');
            return;
        }

        // Add results to container
        filteredServices.forEach(service => {
            const serviceElement = document.createElement('div');
            serviceElement.className = 'p-3 hover:bg-gray-100 cursor-pointer text-base';
            serviceElement.textContent = service.name;
            serviceElement.onclick = () => {
                addSelectedService(service);
                resultsContainer.classList.add('hidden');
            };
            resultsContainer.appendChild(serviceElement);
        });

        resultsContainer.classList.remove('hidden');
    }

    function addSelectedService(service) {
        const selectedServices = document.getElementById('selectedServices');

        // Check if service is already added
        if (document.querySelector(`#selectedServices [data-service-id="${service.id}"]`)) {
            return;
        }

        const serviceHtml = `
        <div class="flex items-center px-4 py-2 rounded-full text-base bg-green-100 text-green-800 border border-green-300" 
             data-service-id="${service.id}">
            ${service.name}
            <select name="services[${service.id}]" class="ml-2 bg-transparent border-none focus:ring-0 p-0 text-base"
                onchange="updateServiceStatus(this)">
                <option value="include" selected>Incluir</option>
                <option value="exclude">Excluir</option>
            </select>
            <button type="button" class="ml-2 text-gray-500 hover:text-gray-700 text-lg" 
                onclick="removeService('${service.id}')">
                &times;
            </button>
        </div>
    `;

        selectedServices.insertAdjacentHTML('beforeend', serviceHtml);

        // Clear search
        document.getElementById('serviceSearch').value = '';
        document.getElementById('searchResults').classList.add('hidden');
    }

    function removeService(id) {
        const serviceElement = document.querySelector(`#selectedServices [data-service-id="${id}"]`);
        if (!serviceElement) return;

        // Remove the service element
        serviceElement.remove();
    }

    function updateServiceStatus(selectElement) {
        const serviceDiv = selectElement.closest('[data-service-id]');
        if (selectElement.value === 'include') {
            serviceDiv.className =
                'flex items-center px-4 py-2 rounded-full text-base bg-green-100 text-green-800 border border-green-300';
        } else {
            serviceDiv.className =
                'flex items-center px-4 py-2 rounded-full text-base bg-red-100 text-red-800 border border-red-300';
        }
    }
</script>
