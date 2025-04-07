<div class="px-6 py-4 bg-gray-50 text-right">
    <button type="button" onclick="openPreviewModal()"
        class="fixed z-50 right-6 bottom-6 px-5 py-3 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-full shadow-xl hover:from-purple-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-400 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105 flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
        </svg>
        <span class="font-semibold">Previsualizar</span>
    </button>
    <div class="flex flex-wrap gap-3 mt-6">
        <button type="button" onclick="generateInternalQuote()"
            class="flex-1 sm:flex-none px-5 py-2.5 bg-gradient-to-r from-yellow-500 to-yellow-600 text-white rounded-lg text-sm font-semibold hover:from-yellow-600 hover:to-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-2 transition-all duration-200 shadow-md hover:shadow-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-2" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Generar Cotización Interna (Excel)
        </button>

        <button type="submit"
            class="flex-1 sm:flex-none px-5 py-2.5 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg text-sm font-semibold hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 transition-all duration-200 shadow-md hover:shadow-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-2" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Guardar y Generar Cotización Final (Word)
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
        console.log(previewData);
        const contentModal = document.querySelector('.content-modal-quotation');
        // Simulamos una carga con setTimeout (reemplaza esto con tu llamada AJAX real)
        contentModal.innerHTML = '';

        // Crear estructura del modal con los datos
        const previewHTML = `
            <img src="/images/pestop.png" class="w-[80%] absolute top-19 right-6" />
            <div class=" mt-15">
                <img src="/images/logoNova.png" class="w-32 h-28 -mx-1" />
                <h4 class="font-medium my-2">Señores</h4>
                <p class="uppercase mb-2 font-bold">${previewData.basicInfo.clientName}</p>
                <p>Presente.-</p>
                <span class="font-bold block my-2 underline">REF: COTIZACION 016/25</span>
                <p>Estimado cliente, por medio la presente tenemos el agrado de enviarle nuestra cotización de acuerdo con su requerimiento e información proporcionada.</p>
                </div>
            </div>
        

            <div class="preview-section">
                ${previewData.products.length > 0 ? `
                    <div class="">
                        ${previewData.products.map((product, index) => `
                            <div class="grid grid-cols-[68%_30%] gap-4"> <!-- Cambiado a 70%/30% -->

                                <div class="flex border">
                                    <div class="bg-blue-300">
                                        <div class="p-3 border-b font-bold">CLIENTE</div>
                                        <div class="p-3 border-b font-bold">ORIGEN</div>
                                        <div class="p-3 border-b font-bold">DESTINO</div>
                                        <div class="p-3 font-bold">INCOTERM</div>
                                    </div>
                                
                                    <div class="border-l flex-grow">
                                        <div class="p-3 border-b uppercase">${previewData.basicInfo.clientName}</div>
                                        <div class="p-3 border-b ">${product.origin}</div>
                                        <div class="p-3 border-b ">${product.destination}</div>
                                        <div class="p-3">${product.incoterm}</div>
                                    </div>
                                </div>
                            
                                <div class="h-full flex flex-col justify-end">
                                    <div class="flex border">
                                        <div class="bg-blue-300">
                                            <div class="p-3 border-b font-bold">CANTIDAD</div>
                                            <div class="p-3 border-b font-bold">PESO</div>
                                            <div class="p-3 uppercase font-bold">${product.volumeUnit}</div>
                                        </div>
                                    
                                        <div class="border-l flex-grow">
                                            <div class="p-3 border-b ">${product.unitQuantity} X ${product.quantity}</div>
                                            <div class="p-3 border-b ">${product.weight || '0'} KG</div>
                                            <div class="p-3 uppercase">${product.volume} ${product.volumeUnit}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                ` : '<p class="text-sm text-gray-500">No se han agregado productos/servicios</p>'}
            </div>
            <p class="">Para el requerimiento de transporte y logistica los costos se encuentran líneas abajo</p>

         
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
                            <!-- Fila del total -->
                            <tr class="font-bold">
                                <td class="text-center w-[70%] border border-black">TOTAL</td>
                                <td class="text-center w-[30%] border border-black">
                                    ${previewData.costs.reduce((total, cost) => total + parseFloat(cost.amount), 0)}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p class="font-bold mt-3 align-text-bottom">** De acuerdo con el TC paralelo vigente.</p>
            </div>
            ${previewData.services.included.length > 0 ? (
                `<div class="preview-section">
                    <p class="font-bold mb-3">El servicio incluye:</p>
                        <div class="">
                        ${previewData.services.included.map(item => `
                            <div class="flex items-start mb-3">
                                <span class="mr-8">-</span>
                                <p>${item.name}</p>
                            </div>`)
                            .join('')
                        }
                </div>`) :  ''
            }    
                    
            ${previewData.services.excluded.length > 0 ? (
                `<div class="preview-section">
                    <p class="font-bold mb-3">El servicio no incluye:</p>
                        <div class="">
                        ${previewData.services.excluded.map(item => `
                            <div class="flex items-start mb-3">
                                <span class="mr-8">-</span>
                                <p>${item.name}</p>
                            </div>`)
                            .join('')
                        }
                </div>`) :  ''
            }  
                </div>
            </div>
                       
            <p><span class="font-bold">Seguro:</span> Se recomienda tener una póliza de seguro para el embarque, ofrecemos la misma de manera adicional considerando el 0.35% sobre el valor declarado, con un min de 30 usd, previa autorización por la compañía de seguros.</p>
            <p><span class="font-bold">Forma de pago:</span> Una vez se confirme el arribo del embarque a puerto de destino.</p>
            <p><span class="font-bold">Validez:</span> Los fletes son válidos hasta 10 días, posterior a ese tiempo, validar si los costos aún están vigentes.</p>
            <p><span class="font-bold">Observaciones:</span> Se debe considerar como un tiempo de tránsito 48 a 50 días hasta puerto de Iquique. </p>
            <p>Atentamente</p>
            <div class="">
                <p>Aidee Callisaya</p>
                <p class="font-bold pb-30">Responsable</p>
            </div>
            <img src="/images/contacto.png" class="w-[40%] absolute bottom-22 right-14" />    
            <img src="/images/pesbottom.png" class="w-[93%] absolute bottom-6 left-8" />    
        `
        contentModal.innerHTML = previewHTML;
    }

    function collectPreviewData() {
        // Recolectar información básica
        const basicInfo = {
            client: document.getElementById('NIT').value,
            clientName: document.querySelector('#NIT option:checked').textContent,
            currency: document.getElementById('currency').value,
            exchangeRate: document.getElementById('exchange_rate').value,
            referenceNumber: document.getElementById('reference_number').value
        };

        // Recolectar costos logísticos
        const costs = [];
        document.querySelectorAll('.cost-card').forEach(card => {
            const checkbox = card.querySelector('.cost-toggle');
            if (checkbox.checked) {
                const costId = checkbox.dataset.costId;
                const costName = card.querySelector('label[for^="cost_"]').textContent.trim();
                const amount = card.querySelector('.cost-amount').value;
                const currencySymbol = card.querySelector('.currency-symbol').textContent;
                const currencyCode = card.querySelector('.currency-code').textContent;

                costs.push({
                    id: costId,
                    name: costName,
                    amount: amount,
                    currencySymbol: currencySymbol,
                    currencyCode: currencyCode
                });
            }
        });

        // Recolectar detalles de productos/servicios
        const products = [];
        document.querySelectorAll('.detail-block:not(.detail-template)').forEach(detailBlock => {
            const index = detailBlock.dataset.index || 0;
            const detailName = detailBlock.querySelector('[name^="details["][name$="[detail_name]"]').value;
            const origin = detailBlock.querySelector('[name^="details["][name$="[origin_id]"] option:checked')
                .textContent;
            const destination = detailBlock.querySelector(
                '[name^="details["][name$="[destination_id]"] option:checked').textContent;
            const weight = detailBlock.querySelector('[name^="details["][name$="[weight]"]').value;
            const incoterm = detailBlock.querySelector(
                '[name^="details["][name$="[incoterm_id]"] option:checked').textContent;
            const quantity = detailBlock.querySelector('[name^="details["][name$="[quantity]"]').value;
            const unitQuantity = detailBlock.querySelector('[name^="details["][name$="[unit_quantity]"]').value;
            const quantityDescription = detailBlock.querySelector(
                '[name^="details["][name$="[quantity_description]"] option:checked').textContent;
            const volume = detailBlock.querySelector('[name^="details["][name$="[volume]"]').value;
            const volumeUnit = detailBlock.querySelector(
                '[name^="details["][name$="[volume_unit]"] option:checked').textContent;

            products.push({
                index: index,
                detailName: detailName,
                origin: origin,
                destination: destination,
                weight: weight,
                incoterm: incoterm,
                quantity: quantity,
                unitQuantity: unitQuantity,
                quantityDescription: quantityDescription,
                volume: volume,
                volumeUnit: volumeUnit
            });
        });

        const services = {
            included: [],
            excluded: []
        };

        document.querySelectorAll('input[name^="services["]:checked').forEach(radio => {
            const card = radio.closest('.relative.bg-white');
            const serviceData = {
                id: radio.name.match(/\[(\d+)\]/)[1],
                name: card.querySelector('h4').textContent.trim(),
                description: card.querySelector('p.text-xs')?.textContent.trim() || '',
                price: parseFloat(card.dataset.price) || 0 // Nuevo campo
            };

            services[radio.value === "include" ? "included" : "excluded"].push(serviceData);
        });

        // Estructurar los datos para la previsualización
        const previewData = {
            basicInfo: basicInfo,
            costs: costs,
            products: products,
            services
        };

        return previewData;
    }
</script>
