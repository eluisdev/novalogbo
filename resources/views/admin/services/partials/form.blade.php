<form 
    action="{{ isset($service) ? route('services.update', $service->id) : route('services.store') }}" 
    method="POST" 
    class="bg-white mx-auto max-w-2xl p-8 space-y-4 rounded-xl shadow-lg border-2 border-blue-200"
>

    @csrf
    @if(isset($service))
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
            'name' => 'Nombre de servicio'
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
                value="{{ old($field, isset($service) ? $service->$field : '') }}"
            />
        </div>
    @endforeach

    <button
        type="submit"
        class="bg-gradient-to-r from-[#0e71a2] to-[#074665] hover:from-[#084665] hover:to-[#06364e] hover:cursor-pointer transition-all duration-200 p-2 rounded-lg text-white w-full font-semibold shadow-md"
    >
        {{ isset($service) ? 'Actualizar datos' : 'Crear servicio' }}
    </button>

</form>