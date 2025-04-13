<form 
    action="{{ isset($continent) ? route('continents.update', $continent->id) : route('continents.store') }}" 
    method="POST" 
    class="bg-white mx-auto max-w-2xl p-8 space-y-4 rounded-xl shadow-lg border-2 border-blue-200"
    data-loading-form
>

    @csrf
    @if(isset($continent))
        @method("PUT")
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
            'name' => 'Nombre de continente',
            'code' => 'Codigo de continente',
        ];
    @endphp

    @foreach ($fields as $field => $label)
        <div>
            <label class="block font-semibold text-gray-700" for="{{ $field }}">{{ $label }}</label>
            <input 
                type="text"
                id="{{ $field }}" 
                name="{{ $field }}" 
                class="mt-2 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                value="{{ old($field, isset($continent) ? $continent->$field : '') }}"
            />
        </div>
    @endforeach

    <button type="submit"
        class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gradient-to-r from-[#0e71a2] to-[#074665] hover:from-[#084665] hover:to-[#06364e] transition-colors duration-200 hover:cursor-pointer mt-6"
        data-loading-text="{{ isset($continent) ? 'Actualizando...' : 'Creando...' }}" data-loading-classes="from-gray-400 to-gray-500">
        <span data-button-text>{{ isset($continent) ? 'Actualizar datos' : 'Crear continente' }}</span>
        <span data-loading-spinner class="hidden">
            <x-loading-spinner />
        </span>
    </button>

</form>