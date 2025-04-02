@if(Auth::user()->role->description === 'admin')
    @php $layout = 'layouts.admin'; @endphp
@else
    @php $layout = 'layouts.operator'; @endphp
@endif

@extends($layout)

@section("dashboard-option")
<div class="flex items-center justify-between bg-white my-5 p-2 px-4 rounded-full">
    <h2 class="text-xl font-black text-yellow-700">Editar datos</h2>
    @if(Auth::user()->role->description === 'operator')
        <a href="{{ route('customers.index') }}" class="bg-[#0B628D] hover:bg-[#2d4652] text-white rounded-sm p-2 text-sm font-semibold hover:cursor-pointer">
            Volver a inicio
        </a>
    @else
        <a href="{{ route('users.index') }}" class="bg-[#0B628D] hover:bg-[#2d4652] text-white rounded-sm p-2 text-sm font-semibold hover:cursor-pointer">
            Volver a inicio
        </a>
    @endif
</div>

<form 
    action="{{ route('profile.update') }}" 
    method="POST" 
    class="bg-white mx-auto max-w-2xl p-8 space-y-4 rounded-xl shadow-lg border-2 border-blue-200"
>
    @csrf
    @method('PUT')

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
                value="{{ old($field, auth()->user()->$field) }}"
            />
        </div>
    @endforeach

    <div>
        <label class="block font-semibold text-gray-700" for="username">Usuario</label>
        <input 
            type="text"
            id="username" 
            name="username" 
            class="mt-2 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-300"
            value="{{ old('username', auth()->user()->username) }}"
            disabled
        />
    </div>

    <div>
        <label class="inline-flex items-center">
            <input type="checkbox" id="toggle-password" class="form-checkbox h-5 w-5 text-blue-600">
            <span class="ml-2 text-gray-700 font-semibold">Cambiar contraseña</span>
        </label>
    </div>

    <div id="password-fields" class="hidden mt-4">
        <div>
            <label class="block font-semibold text-gray-700" for="current_password">Contraseña actual</label>
            <input 
                type="password"
                id="current_password" 
                name="current_password"
                class="mt-2 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            />
        </div>
    
        <div class="mt-2">
            <label class="block font-semibold text-gray-700" for="new_password">Nueva contraseña</label>
            <input 
                type="password"
                id="new_password" 
                name="new_password"
                class="mt-2 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            />
        </div>
    </div>

    <button
        type="submit"
        class="bg-gradient-to-r from-[#0e71a2] to-[#074665] hover:from-[#084665] hover:to-[#06364e] hover:cursor-pointer transition-all duration-200 p-2 rounded-lg text-white w-full font-semibold shadow-md"
    >
        Actualizar mis datos
    </button>
</form>

<script>
    document.getElementById('toggle-password').addEventListener('change', function() {
        document.getElementById('password-fields').classList.toggle('hidden');
    });
</script>

@endsection