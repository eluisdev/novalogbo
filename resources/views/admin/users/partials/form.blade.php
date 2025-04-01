<form 
    action="{{ isset($usuario) ? route('users.update', $usuario->id) : route('users.store') }}" 
    method="POST" 
    class="bg-white mx-auto max-w-2xl p-8 space-y-4 rounded-xl shadow-lg border-2 border-blue-200"
>
    @csrf
    @if(isset($usuario))
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
            'name' => 'Nombres',
            'surname' => 'Apellidos',
            'email' => 'Correo electrónico',
            'phone' => 'Teléfono',
            'username' => 'Usuario',
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
                value="{{ old($field, isset($usuario) ? $usuario->$field : '') }}"
            />
        </div>
    @endforeach

    <div>
        <label class="block font-semibold text-gray-700" for="password">Contraseña</label>
        <input 
            type="password"
            id="password" 
            name="password"
            class="mt-2 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            {{ isset($usuario) ? '' : 'required' }}
        />
    </div>
    
    <div>
        <label class="block font-semibold text-gray-700" for="confirmation-password">Confirmar contraseña</label>
        <input 
            type="password"
            id="confirmation-password" 
            name="confirmation-password"
            class="mt-2 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            {{ isset($usuario) ? '' : 'required' }}
        />
    </div>

    <div class="mb-4">
        <label class="block font-semibold text-gray-700 mb-2">Rol del usuario</label>
        <div class="flex justify-around">
            @foreach (["1" => "Administrador", "2" => "Operario"] as $value => $label)
                <label class="inline-flex items-center">
                    <input 
                        type="radio" 
                        id="role_id"
                        name="role_id" 
                        value="{{ $value }}" 
                        class="rounded-full border-gray-300 text-indigo-600 focus:ring-indigo-500"
                        {{ old('description', isset($usuario) ? $usuario->description : '') == $value ? 'checked' : '' }}
                    >
                    <span class="ml-2">{{ $label }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <button
        type="submit"
        class="bg-gradient-to-r from-[#0e71a2] to-[#074665] hover:from-[#084665] hover:to-[#06364e] hover:cursor-pointer transition-all duration-200 p-2 rounded-lg text-white w-full font-semibold shadow-md"
    >
        {{ isset($usuario) ? 'Actualizar datos' : 'Crear usuario' }}
    </button>
</form>
