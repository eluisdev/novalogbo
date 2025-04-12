document.addEventListener('DOMContentLoaded', function () {
    if (window.location.pathname.includes('quotations/create')) {
        initializePage();
        document.getElementById('currency').addEventListener('change', updateExchangeRate);
    }
});

function initializePage() {
    setupEventListeners();
}

//TODO arreglar indexacion

function setupEventListeners() {
    document.getElementById('currency').addEventListener('change', window.updateExchangeRate());
    document.querySelectorAll('input[name^="costs"][type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', toggleCostAmount);
    });
    $(document).on('select2:open', () => {
        document.querySelector('.select2-container--open .select2-search__field').focus();
    });
}

function toggleCostAmount(event) {
    const amountInput = event.target.closest('div.flex.flex-col').querySelector('input[type="number"]');
    amountInput.disabled = !event.target.checked;
    if (!event.target.checked) amountInput.value = '';
}

window.updateExchangeRate = function () {
    const currencySelect = document.getElementById('currency');
    const exchangeRateInput = document.getElementById('exchange_rate');
    const selectedCurrency = currencySelect.value;
    const exchangeRates = {
        'USD': 6.96,
        'EUR': 10.65,
        'BOB': 1
    };
    const currencySymbols = {
        'USD': { symbol: '$', code: 'USD' },
        'EUR': { symbol: '€', code: 'EUR' },
        'BOB': { symbol: 'Bs', code: 'BOB' }
    };
    exchangeRateInput.value = exchangeRates[selectedCurrency];
    const { symbol, code } = currencySymbols[selectedCurrency];


    document.querySelectorAll('.currency-symbol').forEach(el => {
        el.textContent = symbol;
    });

    document.querySelectorAll('.currency-code').forEach(el => {
        el.textContent = code;
    });

    if (selectedCurrency === 'BOB') {
        exchangeRateInput.value = '1.0000';
        exchangeRateInput.disabled = true;
    } else {
        exchangeRateInput.disabled = false;
    }
};



