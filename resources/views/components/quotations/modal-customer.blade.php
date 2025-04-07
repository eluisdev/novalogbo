<div id="create-customer-quotation" class="fixed inset-0 z-20 hidden bg-gray-100 bg-opacity-90">
    <div class="flex justify-center items-center min-h-screen px-4">
        <div class="inline-block bg-white max-w-lg w-full p-4 space-y-3 rounded-lg shadow border border-blue-200 text-left align-middle">
            <form id="create-customer-quotation-form" action="{{ route('quotations.storeCustomer') }}" method="POST">
                @csrf

                @if ($errors->any())
                    <div class="bg-red-100 text-red-700 p-2 rounded text-sm">
                        <ul class="list-disc pl-4">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @php
                    $fields = [
                        'name' => 'Nombre o Razon social',
                        'NIT' => 'CI / NIT',
                        'email' => 'Correo electrónico',
                        'phone' => 'Teléfono',
                        'cellphone' => 'Celular',
                        'address' => 'Dirección',
                        'department' => 'Lugar de residencia',
                    ];
                @endphp

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach ($fields as $field => $label)
                        <div>
                            <label class="block text-sm text-gray-700 font-medium"
                                for="{{ $field }}">{{ $label }}</label>
                            <input type="text" id="{{ $field }}" name="{{ $field }}"
                                class="mt-1 w-full px-3 py-1.5 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500"
                                value="{{ old($field, isset($customer) ? $customer->$field : '') }}" />
                        </div>
                        @if ($loop->first)
                            <input type="hidden" id="role_id" name="role_id" value="{{ Auth::user()->role_id }}" />
                        @endif
                    @endforeach
                </div>

                <input type="hidden" name="active" class="sr-only peer" value="1">

                <div class="flex gap-2 mt-4">
                    <button type="submit"
                        class="flex-1 py-2 px-3 rounded-md text-sm font-medium text-white bg-gradient-to-r from-[#0e71a2] to-[#074665] hover:from-[#084665] hover:to-[#06364e] transition"
                        >
                        <span>{{ isset($customer) ? 'Actualizar datos' : 'Crear cliente' }}</span>
                    </button>

                    <button type="button" onclick="closeModalUserQuotation()"
                        class="flex-none px-4 py-2 text-sm border rounded-md border-gray-300 hover:bg-gray-100">
                        Cerrar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
