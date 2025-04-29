<!-- Modal de Previsualización -->
<div id="preview-modal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <div
            class="inline-block align-bottom bg-gray-100 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <!-- Header -->
            <div class="bg-blue-600 px-4 py-3 sm:px-6 sm:flex sm:items-center sm:justify-between">
                <h3 class="text-lg leading-6 font-medium text-white">
                    Vista Previa de la Cotización
                </h3>
                <button type="button" onclick="closePreviewModal()"
                    class="modal-close-btn ml-2 p-1 rounded-full text-blue-100 hover:text-white focus:outline-none focus:ring-2 focus:ring-white">
                    <span class="sr-only">Cerrar</span>
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-8 border">
                <div class="bg-white p-8 rounded-lg space-y-6 content-modal-quotation shadow-xl">

                </div>
            </div>
        </div>
    </div>
</div>
