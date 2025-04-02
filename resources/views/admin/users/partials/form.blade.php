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
    @if (isset($usuario))

    <div>
        <label class="block font-semibold text-gray-700" for="username">Usuario</label>
        <input 
            type="text"
            id="username" 
            name="username" 
            class="mt-2 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            value="{{ old("username", isset($usuario) ? $usuario->username : '') }}"
        />
    </div>
    
    <div class="flex items-center justify-between mt-4">
        <span class="font-semibold text-gray-700">Cambiar contraseña</span>
        <label class="relative inline-flex items-center cursor-pointer">
            <input 
                type="checkbox" 
                id="toggle-password" 
                class="sr-only peer"
                {{ old('change_password') ? 'checked' : '' }}
            >
            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
            <span class="ml-3 text-sm font-medium text-gray-900">
                {{ old('change_password') ? 'No' : 'Si' }}
            </span>
        </label>
    </div>

    <div id="password-fields" class="hidden mt-4">
        <div>
            <label class="block font-semibold text-gray-700" for="password">Contraseña</label>
            <input 
                type="password"
                id="password" 
                name="password"
                class="mt-2 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            />
        </div>
    
        <div class="mt-2">
            <label class="block font-semibold text-gray-700" for="password_confirmation">Confirmar contraseña</label>
            <input 
                type="password"
                id="password_confirmation" 
                name="password_confirmation"
                class="mt-2 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            />
        </div>
    </div>
    
    @endif

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
                        {{ old('role_id', isset($usuario) ? $usuario->role->id : '') == $value ? 'checked' : '' }}
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

<script>
    document.getElementById('toggle-password').addEventListener('change', function() {
        document.getElementById('password-fields').classList.toggle('hidden');
    });
</script>