document.addEventListener('DOMContentLoaded', function () {
    if (window.location.pathname.includes('quotations/create')) {
        initializePage();
    }
});

function initializePage() {
    setupEventListeners();
}

function setupEventListeners() {
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