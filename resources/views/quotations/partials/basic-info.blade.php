@props(['quotation' => null])
@php
    $customers = isset($quotation_data) ? $quotation_data['formSelects']['customers'] : $customers;
    $exchangeRates = isset($quotation_data) ? $quotation_data['formSelects']['exchangeRates'] : $exchangeRates;
@endphp

<div class="p-6 border-b-2 border-blue-600">
    <div class="flex items-center mb-6">
        <span class="inline-flex items-center justify-center p-3 rounded-full bg-blue-50 text-blue-600 mr-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </span>
        <h3 class="text-lg font-semibold text-gray-800">Información Básica *</h3>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
            <label for="NIT" class="block text-sm font-medium text-gray-700 mb-1">Cliente *</label>
            <select id="NIT" name="NIT"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 select2">
                <option value=""></option>
                @if (old('NIT') || (isset($quotation_data) && $quotation_data['formData']['NIT']))
                    @php
                        $nit = old('NIT', isset($quotation_data) ? $quotation_data['formData']['NIT'] : '');
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
                @foreach ($exchangeRates as $rate)
                    <option value="{{ $rate->target_currency }}" @selected((old('currency') ? old('currency') : $quotation_data['formData']['currency'] ?? '') == $rate->target_currency)
                        data-rate="{{ $rate->rate }}" data-symbol="{{ $rate->symbol ?? '' }}">
                        {{ $rate->name ?? $rate->target_currency }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="exchange_rate" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Cambio *</label>
            <input type="number" step="0.0001" id="exchange_rate" name="exchange_rate"
                value="{{ isset($quotation_data) ? $quotation_data['formData']['exchange_rate'] : '' }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        </div>

        {{-- @if (isset($quotation_data) && $quotation_data['formData']['date_finalization'])
            <div>
                <label for="reference_number" class="block text-sm font-medium text-gray-700 mb-1">Numero de
                    cotizacion</label>
                <input type="text" id="reference_number" name="reference_number" readonly
                    value="{{ $quotation_data['formData']['reference_number'] }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-gray-200">
            </div>
        @endif --}}

        {{-- <div>
            <label for="valid_until" class="block text-sm font-medium text-gray-700 mb-1">Válido hasta *</label>
            <input type="date" id="valid_until" name="valid_until"
                value="{{ $quotation ? $quotation->valid_until->format('Y-m-d') : '' }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        </div> --}}
    </div>
    <div>
        <div class="grid sm:grid-cols-2 gap-6 mt-2">
            <div>
                <label for="reference_customer" class="block text-sm font-medium text-gray-700 mb-1">Referencia
                    (Opcional)</label>
                <input type="text" id="reference_customer" name="reference_customer"
                    value="{{ old('reference_customer', isset($quotation_data) ? $quotation_data['formData']['reference_customer'] : '') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" />
            </div>


            @if (isset($quotation_data) && $quotation_data['formData']['reference_number'])
                <div>
                    <label for="reference_number" class="block text-sm font-medium text-gray-700 mb-1">Número de
                        cotizacion</label>
                    <input type="text" id="reference_number" name="reference_number" readonly
                        value="{{ $quotation_data['formData']['reference_number'] }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-gray-200">
                </div>
            @else
                <div>
                    <label for="reference_number" class="block text-sm font-medium text-gray-700 mb-1">Número de
                        cotizacion</label>
                    <input type="text" id="reference_number" name="reference_number" readonly
                        value="Sin número de cotizacion"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-gray-200">
                </div>
            @endif
        </div>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
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
                    };
                },
                processResults: function(data, params) {
                    let results = data.map(customer => ({
                        id: customer.id,
                        text: customer.name,
                        customer: customer
                    }));

                    if (results.length === 0 && params.term && params.term.length >= 2) {
                        results.push({
                            id: 'NEW_' + params.term,
                            text: `+ Crear nuevo cliente: "${params.term}"`,
                            isNew: true,
                            searchTerm: params.term
                        });
                    }

                    return {
                        results,
                    };
                },
                cache: true
            },
            minimumInputLength: 2,
            templateResult: formatCustomerResult,
            templateSelection: formatCustomerSelection
        }).on('select2:select', function(e) {
            var data = e.params.data
            if (data && data.isNew) {
                const form = document.getElementById('create-customer-quotation-form');
                form.reset()
                const nameInput = form.querySelector('input[name="name"]');
                if (nameInput && data.searchTerm) {
                    nameInput.value = data.searchTerm;
                }
                document.getElementById('create-customer-quotation').classList.remove('hidden');
                nameInput.focus();
                $(this).val(null).trigger('change');
            }
        });

        @if (old('NIT') || (isset($quotation_data) && $quotation_data['formData']['NIT']))
            var initialData = {
                id: "{{ $selectedCustomer->NIT }}",
                text: "{{ $selectedCustomer->name }}",
                customer: @json($selectedCustomer->toArray())
            };
            var option = new Option(initialData.text, initialData.id, true, true);
            $('#NIT').append(option).trigger('change');
        @endif

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
                e.preventDefault();

                const formData = new FormData(this);
                const url = this.getAttribute('action');
                const method = this.getAttribute('method') || 'POST';

                fetch(url, {
                        method: method,
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        const oldErrorContainer = createCustomerForm.querySelector(
                            '.error-container');
                        if (oldErrorContainer) {
                            oldErrorContainer.remove();
                        }

                        if (data.success) {
                            if (data.customer) {
                                const newOption = new Option(data.customer.name, data.customer.NIT,
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
                            let errorHtml =
                                '<div class="bg-red-100 text-red-700 p-2 rounded text-sm error-container"><ul class="list-disc pl-4">';
                            const errorArray = Array.isArray(data.errors) ?
                                data.errors :
                                Object.values(data.errors).flat();

                            errorArray.forEach(error => {
                                errorHtml += `<li>${error}</li>`;
                            });
                            errorHtml += '</ul></div>';
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
