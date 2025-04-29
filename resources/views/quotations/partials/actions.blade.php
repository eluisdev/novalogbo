<div class="px-6 py-4 bg-gray-50 text-right">
    <button type="button" onclick="openPreviewModal()"
        class="fixed z-10 right-6 bottom-6 px-5 py-3 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-full shadow-xl hover:from-purple-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-400 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105 flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
        </svg>
        <span class="font-semibold">Previsualizar</span>
    </button>
    <div class="flex flex-wrap gap-3 mt-6">
        <button type="submit"
            class="flex-1 sm:flex-none px-5 py-2.5 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg text-sm font-semibold hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 transition-all duration-200 shadow-md hover:shadow-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-2" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ isset($quotation_data) ? 'Guardar cambios' : 'Guardar Cotizacion' }}
        </button>
    </div>
</div>

<script>
    function openPreviewModal() {
        const modal = document.getElementById('preview-modal');
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        fetchPreviewContent();
    }

    function closePreviewModal() {
        document.getElementById('preview-modal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    function fetchPreviewContent() {
        const previewData = collectPreviewData();
        const contentModal = document.querySelector('.content-modal-quotation');
        contentModal.innerHTML = '';

        const previewHTML = `
        <img src="/images/pestop.png" class="w-[80%] absolute md:top-19 top-27 md:right-6 right-5" />
        <div class="mt-15">
            <img src="/images/logoNova.png" class="w-32 h-28 -mx-1" />
            <h4 class="font-medium my-2">Señores</h4>
            <p class="uppercase mb-2 font-bold">${previewData.basicInfo.clientName}</p>
            <p>Presente.-</p>
            <span class="font-bold block my-2 underline">
                REF: ${previewData.basicInfo.referenceNumber !== "Sin número de cotizacion" ? 
                    `COTIZACION ${previewData.basicInfo.referenceNumber}` : 'Sin número de cotización'}
            </span>
            <p>Estimado cliente, por medio la presente tenemos el agrado de enviarle nuestra cotización de acuerdo con su requerimiento e información proporcionada.</p>
        </div>

        <div class="preview-section">
        ${previewData.products.length > 0 ? `
            <div>
            ${previewData.products.map((product, index) => `
                <div class="grid grid-cols-1 md:grid-cols-[68%_30%] gap-4 mt-5">
                    <div class="flex flex-col md:flex-row border">
                        <div class="bg-blue-300 w-full md:w-auto">
                            <div class="p-3 border-b font-bold">CLIENTE</div>
                            <div class="p-3 border-b font-bold">ORIGEN</div>
                            <div class="p-3 border-b font-bold">DESTINO</div>
                            <div class="p-3 font-bold">INCOTERM</div>
                        </div>
                        <div class="border-l flex-grow">
                            <div class="p-3 border-b uppercase">${previewData.basicInfo.clientName || 'Sin cliente'}</div>
                            <div class="p-3 border-b">${product.origin}</div>
                            <div class="p-3 border-b">${product.destination}</div>
                            <div class="p-3">${product.incoterm}</div>
                        </div>
                    </div>
                    
                    <div class="h-full flex flex-col justify-end">
                        <div class="flex flex-col md:flex-row border h-full">
                            <div class="bg-blue-300 w-full md:w-auto">
                                <div class="p-3 border-b font-bold">CANTIDAD</div>
                                <div class="p-3 border-b font-bold">PESO</div>
                                <div class="p-3 uppercase font-bold">${product.volumeUnit}</div>
                            </div>
                            <div class="border-l flex-grow">
                                <div class="p-3 border-b">${product.quantity}</div>
                                <div class="p-3 border-b">${product.weight || '0'} KG</div>
                                <div class="p-3 uppercase">${product.volume} ${product.volumeUnit}</div>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('')}
            </div>
            ` : '<p class="text-sm text-gray-500">No se han agregado productos/servicios</p>'}
        </div>

        
        <p>Para el requerimiento de transporte y logistica los costos se encuentran líneas abajo</p>

        ${previewData.isParallelChecked ? `
            <p class="bg-yellow-300 p-1 inline-block font-semibold underline">OPCION 1) PAGO EN EFECTIVO A UN TC DE ${previewData.exchange_rate.value}</p>
            <div class="preview-section">
                <div class="w-full">
                    <table class="w-3/4 border border-black border-collapse mx-auto">
                        <thead class="bg-blue-300">
                            <tr class="text-center">
                                <th class="font-bold w-[70%] border border-black">CONCEPTO</th>  
                                <th class="font-bold w-[30%] border border-black">MONTO ${previewData.basicInfo.currency}</th>
                            </tr>   
                        </thead>
                        <tbody>
                            ${previewData.costs.map(cost => `
                                <tr>
                                    <td class="text-center w-[70%] border border-black">${cost.name}</td>
                                    <td class="text-center w-[30%] border border-black">${cost.amount_parallel || cost.amount}</td>
                                </tr>
                            `).join('')}
                            <tr class="font-bold">
                                <td class="text-center w-[70%] border border-black">TOTAL</td>
                                <td class="text-center w-[30%] border border-black">
                                    ${previewData.costs.reduce((total, cost) => total + parseFloat(cost.amount_parallel || cost.amount), 0)}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <p class="bg-yellow-300 p-1 inline-block font-semibold underline my-5">OPCION 2) PAGO EFECTIVO EN USD O DE ACUERDO CON EL TC PARALELO</p>
            <div class="preview-section">
                <div class="w-full">
                    <table class="w-3/4 border border-black border-collapse mx-auto">
                        <thead class="bg-blue-300">
                            <tr class="text-center">
                                <th class="font-bold w-[70%] border border-black">CONCEPTO</th>  
                                <th class="font-bold w-[30%] border border-black">MONTO ${previewData.basicInfo.currency}</th>
                            </tr>   
                        </thead>
                        <tbody>
                            ${previewData.costs.map(cost => `
                                <tr>
                                    <td class="text-center w-[70%] border border-black">${cost.name}</td>
                                    <td class="text-center w-[30%] border border-black">${cost.amount}</td>
                                </tr>
                            `).join('')}
                            <tr class="font-bold">
                                <td class="text-center w-[70%] border border-black">TOTAL</td>
                                <td class="text-center w-[30%] border border-black">
                                    ${previewData.costs.reduce((total, cost) => total + parseFloat(cost.amount), 0)}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        ` : `
            <p class="bg-yellow-300 p-1 inline-block font-semibold underline">OPCION 1) PAGO EN EFECTIVO EN BS DE EN BOLIVIA</p>
            <div class="preview-section">
                <div class="w-full">
                    <table class="w-3/4 border border-black border-collapse mx-auto">
                        <thead class="bg-blue-300">
                            <tr class="text-center">
                                <th class="font-bold w-[70%] border border-black">CONCEPTO</th>  
                                <th class="font-bold w-[30%] border border-black">MONTO ${previewData.basicInfo.currency}</th>
                            </tr>   
                        </thead>
                        <tbody>
                            ${previewData.costs.map(cost => `
                                <tr>
                                    <td class="text-center w-[70%] border border-black">${cost.name}</td>
                                    <td class="text-center w-[30%] border border-black">${cost.amount}</td>
                                </tr>
                            `).join('')}
                            <tr class="font-bold">
                                <td class="text-center w-[70%] border border-black">TOTAL</td>
                                <td class="text-center w-[30%] border border-black">
                                    ${previewData.costs.reduce((total, cost) => total + parseFloat(cost.amount), 0)}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <p class="font-bold mt-3 align-text-bottom">** De acuerdo con el TC paralelo vigente.</p>
        `}

        ${previewData.services.included.length > 0 ? `
            <div class="preview-section">
                <p class="font-bold mb-3">El servicio incluye:</p>
                <div>
                    ${previewData.services.included.map(item => `
                        <div class="flex items-start mb-3">
                            <span class="mr-8">-</span>
                            <p>${item.name}</p>
                        </div>
                    `).join('')}
                </div>
            </div>
        ` : ''}

        ${previewData.services.excluded.length > 0 ? `
            <div class="preview-section">
                <p class="font-bold mb-3">El servicio no incluye:</p>
                <div>
                    ${previewData.services.excluded.map(item => `
                        <div class="flex items-start mb-3">
                            <span class="mr-8">-</span>
                            <p>${item.name}</p>
                        </div>
                    `).join('')}
                </div>
            </div>
        ` : ''}

        <p>
            <span class="font-bold">Seguro: </span>
            ${previewData.insurance.value ? previewData.insurance.value : 'Se recomienda tener una póliza de seguro para el embarque, ofrecemos la misma de manera adicional considerando el 0.35% sobre el valor declarado, con un min de 30 usd, previa autorización por la compañía de seguros.'}
        </p>

        <p>
            <span class="font-bold">Forma de pago: </span>
            ${previewData.payment_method.value ? previewData.payment_method.value : 'Una vez se confirme el arribo del embarque a puerto de destino.'}
        </p>

        <p>
            <span class="font-bold">Validez: </span>
            ${previewData.validity.value ? previewData.validity.value : 'Los fletes son válidos hasta 10 días, posterior a ese tiempo, validar si los costos aún están vigentes.'}
        </p>

        <p>
            <span class="font-bold">Observaciones: </span>
            ${previewData.observations.value ? previewData.observations.value : 'Se debe considerar como un tiempo de tránsito 48 a 50 días hasta puerto de Iquique.'}
        </p>

        ${previewData.isParallelChecked ? `
            <span class="bg-yellow-300 p-1 inline-block font-semibold">
                **Debido a la coyuntura actual, en la presente cotización se está aplicando el costo de 
                transferencia del ${juncture.value || '113'}% (CCFEE) sobre los recargos generados en origen, de acuerdo 
                con la comisión que cobra nuestro banco actualmente. Si esta llega a variar, considerar 
                la modificación de ese monto de acuerdo con la tarifa vigente.
            </span>
        ` : ''}

        <p>Atentamente</p>
        <div>
            <p>${previewData.userName}</p>
            <p class="font-bold pb-30">${previewData.charge}</p>
        </div>
        <img src="/images/contacto.png" class="w-[40%] absolute bottom-22 right-14" />    
        <img src="/images/pesbottom.png" class="w-[93%] absolute bottom-6 left-8" />
    `;

        contentModal.innerHTML = previewHTML;
    }

    function collectPreviewData() {
        // Recolectar información básica
        const basicInfo = {
            client: document.getElementById('NIT').value,
            referenceNumber: document.getElementById('reference_number')?.value,
            clientName: document.querySelector('#NIT option:checked').textContent,
            currency: document.getElementById('currency').value,
            exchangeRate: document.getElementById('exchange_rate').value,
        };
        // Recolectar costos logísticos
        const costs = [];
        const isParallelChecked = document.getElementById('parallel_exchange_checkbox').checked;

        document.querySelectorAll('.cost-item').forEach(card => {
            const costId = card.dataset.costId; // Obtiene el ID del cost-item
            const costName = card.querySelector('h4').textContent
                .trim(); // Nombre del concepto (ej: "FLETE TERRESTRE")

            // Obtiene el valor del importe principal
            const amountInput = card.querySelector('input[name^="costs"][name$="[amount]"]');
            const amount = amountInput ? parseFloat(amountInput.value) || 0 : 0;

            // Obtiene el valor del importe paralelo (si existe)
            const amountParallelInput = card.querySelector('input[name^="costs"][name$="[amount_parallel]"]');
            const amountParallel = amountParallelInput ? parseFloat(amountParallelInput.value) || null :
                null; // null si está vacío

            // Obtiene la moneda (asumiendo que es la misma para ambos campos)
            const currencyCode = card.querySelector('.currency-code').textContent;

            costs.push({
                id: costId,
                name: costName,
                amount: amount,
                amount_parallel: amountParallel, // Agregado el campo paralelo
                currencyCode: currencyCode
            });
        });

        // Recolectar detalles de productos/servicios
        const products = [];
        document.querySelectorAll('.product-block').forEach(productBlock => {

            const index = productBlock.dataset.index || 0;
            const isContainer = productBlock.querySelector(
                `input[name="products[${index}][is_container]"]:checked`).value; //1 is_container
            // Lógica para obtener los demás valores del producto
            const productName = productBlock.querySelector(`[name="products[${index}][name]"]`).value;
            const originSelect = productBlock.querySelector(`[name="products[${index}][origin_id]"]`);
            const origin = originSelect ? originSelect.options[originSelect.selectedIndex]?.textContent : '';
            const destinationSelect = productBlock.querySelector(`[name="products[${index}][destination_id]"]`);
            const destination = destinationSelect ? destinationSelect.options[destinationSelect.selectedIndex]
                ?.textContent : '';
            const weight = productBlock.querySelector(`[name="products[${index}][weight]"]`).value;
            const incotermSelect = productBlock.querySelector(`[name="products[${index}][incoterm_id]"]`);
            const incoterm = incotermSelect ? incotermSelect.options[incotermSelect.selectedIndex]
                ?.textContent : '';
            let quantity;
            if (isContainer == 1) {
                quantity = productBlock.querySelector(`[name="products[${index}][quantity]"]`).value;
            } else {
                const selectElement = productBlock.querySelector(
                    `[name="products[${index}][quantity_description_id]"]`);
                const selectedOption = selectElement.options[selectElement
                    .selectedIndex]; // Obtiene la opción seleccionada
                const selectedText = selectedOption.text;

                quantity = productBlock.querySelector(`[name="products[${index}][quantity]"]`).value + ' ' +
                    selectedText;
            }
            const volume = productBlock.querySelector(`[name="products[${index}][volume]"]`).value;
            const volumeUnitSelect = productBlock.querySelector(`[name="products[${index}][volume_unit]"]`);
            const volumeUnit = volumeUnitSelect ? volumeUnitSelect.options[volumeUnitSelect.selectedIndex]
                ?.textContent : '';

            // Añadir el producto al array
            products.push({
                index: index,
                productName: productName,
                origin: origin,
                destination: destination,
                weight: weight,
                incoterm: incoterm,
                quantity: quantity,
                volume: volume,
                volumeUnit: volumeUnit,
            });
        });

        const services = {
            included: [],
            excluded: []
        };

        document.querySelectorAll('#selectedServices div[data-service-id]').forEach(serviceDiv => {
            const serviceId = serviceDiv.dataset.serviceId;
            const serviceName = serviceDiv.childNodes[0].textContent
                .trim(); // El primer nodo de texto contiene el nombre
            const selectElement = serviceDiv.querySelector('select[name^="services["]');
            const status = selectElement.value;

            if (status === "include") {
                services.included.push({
                    id: serviceId,
                    name: serviceName
                });
            } else if (status === "exclude") {
                services.excluded.push({
                    id: serviceId,
                    name: serviceName
                });
            }
        });

        const insurance = document.querySelector('#insurance')
        const payment_method = document.querySelector('#payment_method')
        const validity = document.querySelector('#validity')
        const observations = document.querySelector('#observations')

        const exchange_rate = document.querySelector('#exchange_rate')
        const juncture = document.querySelector('#juncture')

        const user = @json(Auth::user());
        const userName = `${user.name} ${user.surname}`;
        const role = user.role ?? 'user'; // valor por defecto en caso de que no haya role
        const charge = role.description == 'admin' ? 'Responsable de Logística y Comex.' : 'Responsable Comercial.';

        const previewData = {
            basicInfo: basicInfo,
            costs: costs,
            products: products,
            services,
            isParallelChecked,
            insurance,
            payment_method,
            validity,
            observations,
            userName,
            charge,
            exchange_rate,
            juncture
        };

        return previewData;
    }
</script>
