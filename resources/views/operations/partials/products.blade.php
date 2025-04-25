@props(['quotation' => null, 'incoterms' => null])

@php
    $oldProducts = old('products', []);

    // Check if we have old data from a form submission error
    if (!empty($oldProducts)) {
        $products = $oldProducts;
    }
    // Otherwise use the data from the backend
    elseif (isset($quotation_data['formData']['products']) && !empty($quotation_data['formData']['products'])) {
        $products = $quotation_data['formData']['products'];
    }
    // Fallback to empty template
    else {
        $products = [['origin_id' => '', 'destination_id' => '']];
    }
@endphp

<div class="p-6 border-b-2 border-blue-600">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div class="flex items-center sm:flex-row flex-col">
            <span class="inline-flex items-center justify-center p-3 rounded-full bg-blue-50 text-blue-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </span>
            <h3 class="ml-3 text-lg font-semibold text-gray-800">Productos *</h3>
            <p class="text-sm text-gray-500 sm:ml-4">Crea o edita los productos de la cotización.</p>
        </div>

        <button type="button" onclick="addProductBlock()"
            class="flex items-center px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg text-sm font-medium hover:from-green-600 hover:to-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200 shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                    clip-rule="evenodd" />
            </svg>
            Agregar Producto
        </button>
    </div>

    <div class="space-y-4 relative" id="productBlocks">
        @foreach ($products as $index => $product)
            @include('quotations.partials.product-block', [
                'incoterms' => $incoterms,
                'index' => $index,
                'product' => is_array($product) ? (object) $product : $product,
                'isClone' => false,
            ])
        @endforeach
    </div>
</div>

<script>
    function formatLocationResult(item) {
        if (!item.id) return item.text;
        if (item.id.toString().startsWith('country_')) {
            return $('<div class="bg-gray-600 text-white p-2">' + item.text + '</div>');
        }
        return $('<div class="city-option hover:font-bold transition-colors duration-500 ease-in">' + item
            .text +
            '</div>');
    }

    function formatLocationSelection(item) {
        return item.text;
    }

    function formatQuantityDescriptionResult(item) {
        if (!item.id) return item.text;
        return $('<div class="p-2 hover:bg-gray-100">' + item.text + '</div>');
    }

    function formatQuantityDescriptionSelection(item) {
        return item.text || item;
    }

    function initSelect2ForBlock(block) {
        if (!block) return;

        // Configuración para descripciones de cantidad
        const quantityDescriptionConfig = {
            theme: 'bootstrap-5',
            allowClear: true,
            width: '100%',
            language: {
                noResults: () => "No se encontraron descripciones",
                searching: () => "Buscando...",
                inputTooShort: () => "Ingrese al menos 1 carácter"
            },
            ajax: {
                url: '/quotations/searchQuantityDescription',
                dataType: 'json',
                delay: 300,
                data: params => ({
                    search: params.term,
                }),
                processResults: function(data, params) {
                    const items = Array.isArray(data) ? data : (data.data || []);
                    const results = items.map(item => ({
                        id: item.id,
                        text: item.name
                    }));

                    if (results.length === 0 && params.term && params.term.length >= 2) {
                        results.push({
                            id: 'NEW_' + params.term,
                            text: `+ Crear nuevo: "${params.term}"`,
                            isNew: true,
                            searchTerm: params.term
                        });
                    }

                    return {
                        results
                    };
                }
            },
            minimumInputLength: 1,
            templateResult: formatQuantityDescriptionResult,
            templateSelection: formatQuantityDescriptionSelection
        };

        const select2Config = {
            theme: 'bootstrap-5',
            allowClear: true,
            width: '100%',
            language: {
                noResults: () => "No se encontraron países",
                searching: () => "Buscando...",
                inputTooShort: () => "Ingrese al menos 2 caracteres"
            },
            ajax: {
                url: '/quotations/searchLocation',
                dataType: 'json',
                delay: 300,
                data: params => ({
                    searchTerm: params.term
                }),
                processResults: data => {
                    if (!data.success || !data.data) {
                        return {
                            results: []
                        };
                    }

                    const results = data.data.flatMap(country => {
                        const countryEntry = {
                            id: `country_${country.id}`,
                            text: country.name,
                            disabled: true
                        };

                        const cityEntries = (country.cities || []).map(city => ({
                            id: city.id,
                            text: `${city.name}, ${country.name}`,
                            cityName: city.name,
                            countryName: country.name,
                            countryId: country.id
                        }));

                        return [countryEntry, ...cityEntries];
                    });
                    return {
                        results
                    };
                }
            },
            minimumInputLength: 2,
            templateResult: formatLocationResult,
            templateSelection: formatLocationSelection
        };

        // Destruir Select2 de manera segura
        $(block).find('.origin-select, .destiny-select, .quantity-description-select').each(function() {
            try {
                if ($(this).data('select2')) {
                    $(this).select2('destroy');
                }
                $(this).next('.select2-container').remove();
                $(this).removeClass('select2-hidden-accessible');
            } catch (e) {
                console.warn('Error al limpiar Select2:', e);
            }
        });

        // Inicializar Select2 para origen
        $(block).find('.origin-select').select2({
            ...select2Config,
            placeholder: 'Buscar país de origen...'
        });

        // Inicializar Select2 para destino
        $(block).find('.destiny-select').select2({
            ...select2Config,
            placeholder: 'Buscar país de destino...'
        });

        // Inicializar Select2 para descripción de cantidad
        $(block).find('.quantity-description-select').select2({
            ...quantityDescriptionConfig,
            placeholder: 'Unidad de cantidad...'
        }).on('select2:select', function(e) {
            var data = e.params.data
            if (data && data.isNew) {
                const form = document.getElementById('create-quotation-description-form');
                form.reset()
                const nameInput = form.querySelector('input[name="name"]');
                if (nameInput && data.searchTerm) {
                    nameInput.value = data.searchTerm;
                }
                document.getElementById('create-quotation-description').classList.remove('hidden');
                nameInput.focus();
                $(this).val(null).trigger('change');
            }
        });
    }




    function removeProductBlock(button) {
        const productBlocks = document.querySelectorAll('.product-block');

        if (productBlocks.length <= 1) {
            Swal.fire({
                icon: 'warning',
                title: 'No se puede eliminar',
                text: 'Debe haber al menos un producto en la cotización.',
                confirmButtonText: 'Entendido'
            });
            return;
        }

        const block = button.closest('.product-block');
        block.style.transition = 'opacity 0.3s';
        block.style.opacity = '0';

        // Destruir Select2 antes de eliminar
        $(block).find('.origin-select, .destiny-select, .quantity-description-select').select2('destroy');

        setTimeout(() => {
            block.remove();
        }, 300);
    }

    function addProductBlock() {
        const $container = $('#productBlocks');
        const $lastBlock = $container.find('.product-block').last();

        // Destroy Select2 before cloning
        $lastBlock.find('.origin-select, .destiny-select, .quantity-description-select').select2('destroy');

        const lastIndex = parseInt($lastBlock.data('index')) || 0;
        const newIndex = lastIndex + 1;
        const uniqueSuffix = '_clone_' + Date.now();

        const $clone = $lastBlock.clone();

        $clone.attr('data-index', newIndex);

        // Update IDs, names and for attributes
        $clone.find('[id], [name], [for]').each(function() {
            if (this.id) this.id = this.id.replace(/\d+(_clone_\d+)?/, newIndex + uniqueSuffix);
            if (this.name) this.name = this.name.replace(/\[\d+]/, `[${newIndex}]`);
            if (this.htmlFor) this.htmlFor = this.htmlFor.replace(/\d+(_clone_\d+)?/, newIndex +
                uniqueSuffix);
        });

        // Clear Select2 properly
        $clone.find('.select2').remove();
        $clone.find('.select2-hidden-accessible').removeClass('select2-hidden-accessible');

        // Reset values in the clone
        $clone.find('input[type="text"], input[type="number"], textarea').val('');
        $clone.find('select').val('').prop('selectedIndex', 0);

        $clone.appendTo($container);

        // Reinitialize Select2
        initSelect2ForBlock($clone);
        initSelect2ForBlock($lastBlock);
    }

    function updateRealQuantity() {
        const index = this.getAttribute('data-index');
        const part1 = document.getElementById(`quantity_part1_${index}`)?.value || '';
        const part2 = document.getElementById(`quantity_part2_${index}`)?.value || '';
        const realQuantityInput = document.getElementById(`real_quantity_${index}`);

        if (realQuantityInput) {
            realQuantityInput.value = part1 && part2 ? `${part1} x ${part2}` : '';
        }
    }

    // Inicializar Select2 cuando el DOM esté listo
    window.addEventListener("DOMContentLoaded", () => {
        $(document).ready(function() {
            $('#productBlocks .product-block').each(function() {
                initSelect2ForBlock($(this));
            });
        });

        const createQuantityDescriptionForm = document.getElementById('create-quotation-description-form');

        if (createQuantityDescriptionForm) {
            createQuantityDescriptionForm.addEventListener('submit', function(e) {
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
                        const oldErrorContainer = createQuantityDescriptionForm.querySelector(
                            '.error-container');
                        if (oldErrorContainer) {
                            oldErrorContainer.remove();
                        }

                        if (data.success) {
                            if (data.quantityDescription) {
                                const newOption = new Option(data.quantityDescription.name, data
                                    .quantityDescription.id,
                                    true, true);
                                $('.quantity-description-select').append(newOption).trigger(
                                    'change');
                            }
                            window.closeModalDescriptionQuotation();
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: data.message || 'Description creada correctamente',
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
                            createQuantityDescriptionForm.insertAdjacentHTML('afterbegin',
                                errorHtml);
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

    window.closeModalDescriptionQuotation = function() {
        document.getElementById('create-quotation-description').classList.add('hidden');
        document.querySelector('form#create-quotation-description')?.reset();
    }
</script>
