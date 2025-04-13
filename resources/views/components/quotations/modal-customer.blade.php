<div id="create-customer-quotation" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-gray-50/90"></div> <!-- ¡Esta es la sintaxis correcta! -->

    <div class="relative z-50 flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6">
            <h1 class="text-center mb-5 font-bold w-full bg-[#0B628D] rounded p-3 text-white">Crear cliente</h1>
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

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach ($fields as $field => $label)
                        <div>
                            <label class="block text-sm text-gray-700 font-medium"
                                for="{{ $field }}">{{ $label }}</label>
                            <input type="text" id="{{ $field }}" name="{{ $field }}"
                                class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500"
                                value="{{ old($field, isset($customer) ? $customer->$field : '') }}" />
                        </div>
                        @if ($loop->first)
                            <input type="hidden" id="role_id" name="role_id" value="{{ Auth::user()->role_id }}" />
                        @endif
                    @endforeach
                </div>

                <input type="hidden" name="active" class="sr-only peer" value="1">

                <div class="flex gap-3 mt-6">
                    <button type="submit"
                        class="flex-1 py-2 px-4 rounded-md text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-blue-800 hover:from-blue-700 hover:to-blue-900 transition">
                        <span>{{ isset($customer) ? 'Actualizar datos' : 'Crear cliente' }}</span>
                    </button>

                    <button type="button" onclick="closeModalUserQuotation()"
                        class="flex-none px-4 py-2 text-sm border rounded-md border-gray-300 bg-white hover:bg-gray-50 transition">
                        Cerrar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
