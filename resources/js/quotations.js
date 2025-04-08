document.addEventListener('DOMContentLoaded', function () {
    // initSelect2ForBlock(block);
    
    // Verifica si la URL contiene 'quotations/create'
    if (window.location.pathname.includes('quotations/create')) {
        initializePage(); // Ejecuta tu función principal
        document.getElementById('currency').addEventListener('change', updateExchangeRate);
    }
});

function initializePage() {
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



