@props(['quotation' => null])

<div class="p-6 border-b-2 border-blue-600">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div class="flex items-center">
            <span class="inline-flex items-center justify-center p-3 rounded-full bg-blue-50 text-blue-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </span>
            <h3 class="ml-3 text-lg font-semibold text-gray-800">Productos</h3>
            <p class="text-sm text-gray-500 sm:ml-4">Cree o edite los productos de la cotizacion.</p>
        </div>
        
        <button type="button" onclick="addDetailBlock()"
                class="flex items-center px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg text-sm font-medium hover:from-green-600 hover:to-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200 shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            Agregar Producto
        </button>
    </div>

    <div class="space-y-4 relative" id="detailBlocks">

        @if ($quotation && $quotation->details->count())
            @foreach ($quotation->details as $index => $detail)
                @include('quotations.partials.detail-block', [
                    'incoterms' => $incoterms,
                    'index' => $index,
                    'detail' => $detail,
                ])
            @endforeach
        @endif
    </div>
</div>

<script>
    let detailIndex = {{ isset($quotation) && $quotation->details ? $quotation->details->count() : 1 }};
    const addDetailBlock = function() {
        const template = document.querySelector('.detail-template');
        const clone = template.cloneNode(true);

        // Actualizar propiedades
        clone.classList.remove('detail-template', 'hidden');
        clone.classList.add('detail-block');

        // Actualizar índices
        const newIndex = detailIndex++;
        clone.innerHTML = clone.innerHTML.replace(/details\[__INDEX__\]/g, `details[${newIndex}]`);

        // Agregar botón de eliminar (que estaba excluido en el template)
        const deleteBtn = document.createElement('div');
        deleteBtn.className = 'absolute -top-3 -right-3';
        deleteBtn.innerHTML = `
        <button type="button" onclick="removeDetailBlock(this)" aria-label="Eliminar product"
            class="flex items-center justify-center w-8 h-8 rounded-full bg-red-500 text-white shadow-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors duration-200 transform hover:scale-105 active:scale-95">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </button>
    `;
        clone.appendChild(deleteBtn);

        // Insertar en el DOM
        document.getElementById('detailBlocks').appendChild(clone);

        // Inicializar Select2
        window.initSelect2ForBlock(clone);
    };

    const removeDetailBlock = function(button) {
        const block = button.closest('.detail-block');
        block.style.transition = 'opacity 0.3s';
        block.style.opacity = '0';

        setTimeout(() => {
            $(block).find('.origin-select, .destiny-select').select2('destroy');
            block.remove();
        }, 300);
    };
</script>
