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
    const selectedOption = currencySelect.options[currencySelect.selectedIndex];
    const selectedCurrency = currencySelect.value;
    const rate = selectedOption.getAttribute('data-rate');
    const symbol = selectedOption.getAttribute('data-symbol');
    exchangeRateInput.value = rate;

    document.querySelectorAll('.currency-symbol').forEach(el => {
        el.textContent = symbol;
    });

    document.querySelectorAll('.currency-code').forEach(el => {
        el.textContent = selectedCurrency;
    });
};

document.addEventListener('DOMContentLoaded', function () {
    updateExchangeRate();
    document.getElementById('currency').addEventListener('change', updateExchangeRate);
});

