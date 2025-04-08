document.addEventListener('DOMContentLoaded', function () {
    // Verifica si la URL contiene 'quotations/create'
    if (window.location.pathname.includes('quotations/create')) {
        initializePage(); // Ejecuta tu función principal
        document.getElementById('currency').addEventListener('change', updateExchangeRate);
    }
});

function initializePage() {
    window.updateExchangeRate();

    if (document.querySelectorAll('.product-block').length === 0) {
        addProductBlock();
    }

    setupEventListeners();
}

//TODO arreglar indexacion

function setupEventListeners() {
    // Selector de moneda
    document.getElementById('currency').addEventListener('change', window.updateExchangeRate());

    // Manejar checkboxes de costos logísticos
    document.querySelectorAll('input[name^="costs"][type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', toggleCostAmount);
    });

    // Manejar el foco en los select2 cuando se abren
    $(document).on('select2:open', () => {
        document.querySelector('.select2-container--open .select2-search__field').focus();
    });
}

function toggleCostAmount(event) {
    const amountInput = event.target.closest('div.flex.flex-col').querySelector('input[type="number"]');
    amountInput.disabled = !event.target.checked;
    if (!event.target.checked) amountInput.value = '';
}

window.updateExchangeRate = function() {
    const currencySelect = document.getElementById('currency');
    const exchangeRateInput = document.getElementById('exchange_rate');
    const selectedCurrency = currencySelect.value;

    // Tasas de cambio fijas (puedes reemplazar con llamada API)
    const exchangeRates = {
        'USD': 6.96,
        'EUR': 10.65,
        'BOB': 1
    };

    // Mapeo de símbolos y códigos por moneda
    const currencySymbols = {
        'USD': { symbol: '$', code: 'USD' },
        'EUR': { symbol: '€', code: 'EUR' },
        'BOB': { symbol: 'Bs', code: 'BOB' }
    };

    // 1. Actualizar tipo de cambio
    exchangeRateInput.value = exchangeRates[selectedCurrency];

    // 2. Actualizar símbolos monetarios en toda la página
    const { symbol, code } = currencySymbols[selectedCurrency];
    
    // Actualizar símbolos ($, €, Bs)
    document.querySelectorAll('.currency-symbol').forEach(el => {
        el.textContent = symbol;
    });
    
    // Actualizar códigos (USD, EUR, BOB)
    document.querySelectorAll('.currency-code').forEach(el => {
        el.textContent = code;
    });

    // 3. Opcional: Habilitar/deshabilitar campo de tipo de cambio
    if (selectedCurrency === 'BOB') {
        exchangeRateInput.value = '1.0000';
        exchangeRateInput.disabled = true;
    } else {
        exchangeRateInput.disabled = false;
    }
};
//QUOTATIOS

window.initSelect2ForBlock = function(block) {
    $(block).find('.origin-select').select2({
        theme: 'bootstrap-5',
        placeholder: 'Buscar país de origen...',
        allowClear: true,
        width: '100%',
        language: {
            noResults: function () {
                return "No se encontraron países";
            },
            searching: function () {
                return "Buscando...";
            },
            inputTooShort: function () {
                return "Ingrese al menos 2 caracteres";
            }
        },
        ajax: {
            url: '/quotations/searchLocation',
            dataType: 'json',
            delay: 300,
            data: function(params) {
                return {
                    searchTerm: params.term
                };
            },
            processResults: function(data) {
                if (!data.success || !data.data) {
                    return {
                        results: []
                    };
                }

                // Procesamos los resultados de forma segura
                let results = [];

                data.data.forEach(function(country) {
                    // Agregar el país como grupo
                    results.push({
                        id: `country_${country.id}`,
                        text: country.name,
                        disabled: true
                    });

                    // Agregar las ciudades de este país
                    if (country.cities && Array.isArray(country.cities)) {
                        country.cities.forEach(function(city) {
                            results.push({
                                id: `${city.id}`,
                                text: `${city.name}, ${country.name}`,
                                cityName: city.name,
                                countryName: country.name,
                                countryId: country.id
                            });
                        });
                    }
                });

                return {
                    results: results
                };
            }
        },
        minimumInputLength: 2,
        templateResult: formatLocationResult,
        templateSelection: formatLocationSelection
    });

    $(block).find('.destiny-select').select2({
        theme: 'bootstrap-5',
        placeholder: 'Buscar país de destino...',
        allowClear: true,
        width: '100%',
        language: {
            noResults: function () {
                return "No se encontraron países";
            },
            searching: function () {
                return "Buscando...";
            },
            inputTooShort: function () {
                return "Ingrese al menos 2 caracteres";
            }
        },
        ajax: {
            url: '/quotations/searchLocation',
            dataType: 'json',
            delay: 300,
            data: function(params) {
                return {
                    searchTerm: params.term
                };
            },
            processResults: function(data) {
                if (!data.success || !data.data) {
                    return {
                        results: []
                    };
                }

                // Procesamos los resultados de forma segura
                let results = [];

                data.data.forEach(function(country) {
                    // Agregar el país como grupo
                    results.push({
                        id: `country_${country.id}`,
                        text: country.name,
                        disabled: true
                    });

                    // Agregar las ciudades de este país
                    if (country.cities && Array.isArray(country.cities)) {
                        country.cities.forEach(function(city) {
                            results.push({
                                id: `${city.id}`,
                                text: `${city.name}, ${country.name}`,
                                cityName: city.name,
                                countryName: country.name,
                                countryId: country.id
                            });
                        });
                    }
                });

                return {
                    results: results
                };
            }
        },
        minimumInputLength: 2,
        templateResult: formatLocationResult,
        templateSelection: formatLocationSelection
    });

        
    function formatLocationResult(item) {
        if (!item.id) {
            return item.text;
        }

        if (item.id.startsWith('country_')) {
            return $('<div class="bg-gray-600 text-white p-2">' + item.text + '</div>');
        } else {
            return $('<div class="city-option hover:font-bold transition-colors duration-500 ease-in">' + item.text + '</div>');
        }
    }

    function formatLocationSelection(item) {
        if (!item.id) {
            return item.text;
        }

        return item.text;
    }
}


