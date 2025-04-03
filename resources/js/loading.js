class FormLoading {
    static init() {
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function (e) {
                const submitButton = form.querySelector('[type="submit"]');
                if (!submitButton) return;

                const buttonText = submitButton.querySelector('[data-button-text]');
                const loadingSpinner = submitButton.querySelector('[data-loading-spinner]');

                if (submitButton && buttonText && loadingSpinner) {
                    if (!submitButton.dataset.originalText) {
                        submitButton.dataset.originalText = buttonText.textContent;
                    }

                    submitButton.disabled = true;
                    buttonText.textContent = submitButton.dataset.loadingText || 'Procesando...';
                    loadingSpinner.classList.remove('hidden');

                    submitButton.classList.remove(
                        'from-[#0e71a2]', 'to-[#074665]',
                        'hover:from-[#084665]', 'hover:to-[#06364e]'
                    );
                    
                    submitButton.classList.add('from-gray-400', 'to-gray-500');
                }
            });
        });
    }
}

document.addEventListener('DOMContentLoaded', () => FormLoading.init());