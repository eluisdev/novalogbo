<form action="{{ isset($incoterm) ? route('incoterms.update', $incoterm->id) : route('incoterms.store') }}" method="POST"
    data-loading-form class="bg-white mx-auto max-w-2xl p-8 space-y-4 rounded-xl shadow-lg border-2 border-blue-200">

    @csrf
    @if (isset($incoterm))
        @method('PUT')
    @endif

    @if ($errors->any())
        <div class="bg-red-100 text-red-700 p-4 rounded-md">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $fields = [
            'name' => 'Nombre de incoterm',
            'code' => 'Codigo',
        ];
    @endphp

    @foreach ($fields as $field => $label)
        <div>
            <label class="block font-semibold text-gray-700" for="{{ $field }}">{{ $label }}</label>
            <input type="text" id="{{ $field }}" name="{{ $field }}"
                class="mt-2 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                value="{{ old($field, isset($incoterm) ? $incoterm->$field : '') }}" />
        </div>
    @endforeach

    <div class="flex items-center justify-between mt-4">
        <span class="font-semibold text-gray-700">Estado del incoterm</span>
        <label class="relative inline-flex items-center cursor-pointer">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" class="sr-only peer" value="1"
                {{ old('is_active', isset($service) ? $service->is_active : true) ? 'checked' : '' }}>
            <div
                class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
            </div>
            <span class="ml-3 text-sm font-medium text-gray-900">
                {{ old('active', isset($service) ? $service->is_active : true) ? 'Activo' : 'Inactivo' }}
            </span>
        </label>
    </div>

    <button type="submit"
        class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gradient-to-r from-[#0e71a2] to-[#074665] hover:from-[#084665] hover:to-[#06364e] transition-colors duration-200 hover:cursor-pointer mt-6"
        data-loading-text="{{ isset($incoterm) ? 'Actualizando...' : 'Creando...' }}"
        data-loading-classes="from-gray-400 to-gray-500">
        <span data-button-text>{{ isset($incoterm) ? 'Actualizar datos' : 'Crear incoterm' }}</span>
        <span data-loading-spinner class="hidden">
            <x-loading-spinner />
        </span>
    </button>

</form>
