<form action="{{ isset($country) ? route('countries.update', $country->id) : route('countries.store') }}" method="POST"
    data-loading-form class="bg-white mx-auto max-w-2xl p-8 space-y-4 rounded-xl shadow-lg border-2 border-blue-200">

    @csrf
    @if (isset($country))
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
            'name' => 'Nombre de pais',
            'code' => 'Codigo de pais',
        ];
    @endphp

    @foreach ($fields as $field => $label)
        <div>
            <label class="block font-semibold text-gray-700" for="{{ $field }}">{{ $label }}</label>
            <input type="text" id="{{ $field }}" name="{{ $field }}"
                class="mt-2 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                value="{{ old($field, isset($country) ? $country->$field : '') }}" />
        </div>
    @endforeach

    <select name="continent_id" id="continent_id"
        class="w-full px-4 py-2 mt-1 text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out">
        <option value="" class="text-gray-400">-- Seleccione el continente --</option>
        @foreach ($continents as $continent)
            <option value="{{ $continent->id }}" @if (old('continent_id', isset($country) ? $country->continent_id : '') == $continent->id) selected @endif
                class="text-gray-800 hover:bg-blue-50">
                {{ $continent->name }}
            </option>
        @endforeach
    </select>

    <button type="submit"
        class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gradient-to-r from-[#0e71a2] to-[#074665] hover:from-[#084665] hover:to-[#06364e] transition-colors duration-200 hover:cursor-pointer mt-6"
        data-loading-text="{{ isset($country) ? 'Actualizando...' : 'Creando...' }}"
        data-loading-classes="from-gray-400 to-gray-500">
        <span data-button-text>{{ isset($country) ? 'Actualizar datos' : 'Crear pais' }}</span>
        <span data-loading-spinner class="hidden">
            <x-loading-spinner />
        </span>
    </button>

    </button>

</form>
