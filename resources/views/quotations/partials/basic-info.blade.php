@props(['quotation' => null])

<div class="p-6 border-b-2 border-blue-600">
    <div class="flex items-center mb-6">
        <span class="inline-flex items-center justify-center p-3 rounded-full bg-blue-50 text-blue-600 mr-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </span>
        <h3 class="text-lg font-semibold text-gray-800">Información Básica</h3>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
            <label for="NIT" class="block text-sm font-medium text-gray-700 mb-1">Cliente *</label>
            <select id="NIT" name="NIT"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 select2">
                <option value=""></option>
                @if (old('NIT') || (isset($quotation) && $quotation->NIT))
                    @php
                        $nit = old('NIT', isset($quotation) ? $quotation->customer_id : '');
                        $selectedCustomer = $customers->firstWhere('NIT', $nit);
                    @endphp
                    @if ($selectedCustomer)
                        <option value="{{ $selectedCustomer->id }}" selected>{{ $selectedCustomer->name }}</option>
                    @endif
                @endif
            </select>
        </div>

        <div>
            <label for="currency" class="block text-sm font-medium text-gray-700 mb-1">Moneda *</label>
            <select id="currency" name="currency"
                class="currency-selector w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                <option value="USD" @selected((old('currency') ? old('currency') : $quotation->currency ?? 'USD') == 'USD') data-symbol="$">Dólares (USD)</option>
                <option value="EUR" @selected((old('currency') ? old('currency') : $quotation->currency ?? 'USD') == 'EUR') data-symbol="€">Euros (EUR)</option>
                <option value="BOB" @selected((old('currency') ? old('currency') : $quotation->currency ?? 'USD') == 'BOB') data-symbol="Bs">Bolivianos (BOB)</option>
            </select>
        </div>

        <div>
            <label for="exchange_rate" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Cambio *</label>
            <input type="number" step="0.0001" id="exchange_rate" name="exchange_rate"
                value="{{ $quotation ? $quotation->exchange_rate : '' }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label for="exchange_rate" class="block text-sm font-medium text-gray-700 mb-1">Referencia *</label>
            <input type="number" step="0.0001" id="reference_number" name="reference_number"
                value="{{ old('reference_number', isset($quotation) ? $quotation->exchange_rate : '') }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" />
        </div>

        {{-- <div>
            <label for="valid_until" class="block text-sm font-medium text-gray-700 mb-1">Válido hasta *</label>
            <input type="date" id="valid_until" name="valid_until"
                value="{{ $quotation ? $quotation->valid_until->format('Y-m-d') : '' }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        </div> --}}
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar Select2 con AJAX
        $('#NIT').select2({
            theme: 'bootstrap-5',
            placeholder: 'Buscar cliente...',
            allowClear: true,
            width: '100%',
            language: {
                noResults: function() {
                    return "No se encontraron resultados";
                },
                searching: function() {
                    return "Buscando...";
                },
                inputTooShort: function() {
                    return "Ingrese al menos 2 caracteres";
                }
            },
            ajax: {
                url: '/quotations/searchCustomer',
                dataType: 'json',
                delay: 300,
                data: function(params) {
                    return {
                        search: params.term,
                        page: params.page || 1
                    };
                },
                processResults: function(data, params) {
                    let results = data.map(customer => ({
                        id: customer.id,
                        text: customer.name,
                        customer: customer
                    }));

                    if (results.length === 0 && params.term && params.term.length >=
                        2) {
                        results.push({
                            id: 'NEW_' + params.term,
                            text: `+ Crear nuevo cliente: "${params.term}"`,
                            isNew: true,
                            searchTerm: params.term
                        });
                    }

                    return {
                        results: results,
                        pagination: {
                            more: false
                        }
                    };
                },
                cache: true
            },
            minimumInputLength: 2,
            templateResult: formatCustomerResult,
            templateSelection: formatCustomerSelection
        });

        @if (old('NIT') || (isset($quotation) && $quotation->NIT))
            var initialData = {
                id: "{{ $selectedCustomer->NIT }}",
                text: "{{ $selectedCustomer->name }}",
                customer: @json($selectedCustomer->toArray())
            };
            var option = new Option(initialData.text, initialData.id, true, true);
            $('#NIT').append(option).trigger('change');
        @endif

        // Funciones de formateo
        function formatCustomerResult(data) {
            if (data.loading) return data.text;

            if (data.isNew) {
                return $(`
                        <div class="flex items-center text-green-600 p-2">
                            <i class="fas fa-plus-circle mr-2"></i>
                        <div>
                        <div class="font-semibold">${data.text}</div>
                            <small class="text-xs text-gray-500">Click para registrar nuevo cliente</small>
                        </div>`);
            }

            return $(`
                    <div class="flex items-center p-2">
                        <div class="mr-3">
                            <div class="font-semibold">${data.customer.name}</div>
                            ${data.customer.email ? `<div class="text-sm text-gray-600">${data.customer.email}</div>` : ''}
                            ${data.customer.phone ? `<div class="text-sm text-gray-600">${data.customer.phone}</div>` : ''}
                        </div>
                    </div>`);
        }

        function formatCustomerSelection(data) {
            if (data.isNew) return data.searchTerm;
            return data.text || data.customer?.name;
        }

        const createCustomerForm = document.querySelector('#create-customer-quotation-form');

        if (createCustomerForm) {
            createCustomerForm.addEventListener('submit', function(e) {
                e.preventDefault(); // Evita el envío tradicional del formulario

                const formData = new FormData(this);
                const url = this.getAttribute('action');
                const method = this.getAttribute('method') || 'POST';

                // Realizar la petición AJAX
                fetch(url, {
                        method: method,
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Limpiar errores anteriores
                        const oldErrorContainer = createCustomerForm.querySelector(
                            '.error-container');
                        if (oldErrorContainer) {
                            oldErrorContainer.remove();
                        }

                        if (data.success) {
                            // Agregar el nuevo cliente al select2 y cerrar modal
                            if (data.customer) {
                                const newOption = new Option(data.customer.name, data.customer.id,
                                    true, true);
                                $('#NIT').append(newOption).trigger('change');
                            }
                            window.closeModalUserQuotation();
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: data.message || 'Cliente creado correctamente',
                                showConfirmButton: true
                            })
                        } else if (data.errors) {
                            // Mostrar errores de validación
                            let errorHtml =
                                '<div class="bg-red-100 text-red-700 p-2 rounded text-sm error-container"><ul class="list-disc pl-4">';

                            // Convertir objeto de errores a array si es necesario
                            const errorArray = Array.isArray(data.errors) ?
                                data.errors :
                                Object.values(data.errors).flat();

                            errorArray.forEach(error => {
                                errorHtml += `<li>${error}</li>`;
                            });
                            errorHtml += '</ul></div>';

                            // Insertar errores al inicio del formulario
                            createCustomerForm.insertAdjacentHTML('afterbegin', errorHtml);
                        } else if (data.message) {
                            alert(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Ocurrió un error al procesar la solicitud');
                    });
            });
        }
    });

    window.closeModalUserQuotation = function() {
        document.getElementById('create-customer-quotation').classList.add('hidden');
        document.querySelector('form#create-customer-quotation')?.reset();
    }
</script>
