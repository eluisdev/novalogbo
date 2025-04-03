@if(Auth::user()->role->description === 'admin')
    @php $layout = 'layouts.admin'; @endphp
@else
    @php $layout = 'layouts.operator'; @endphp
@endif

@extends($layout)

@section('dashboard-option')
<div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white rounded-xl shadow-sm p-3 mb-6 border border-gray-200">
        <h2 class="text-xl font-black text-gray-800">
            <span class="text-[#0B628D]">Editar datos de usuario</span>
        </h2>
        <div class="flex space-x-2">
            @if(Auth::user()->role->description === 'operator')
                <a href="{{ route('customers.index') }}" 
                   class="flex items-center justify-center px-4 py-2 bg-[#0B628D] hover:bg-[#19262c] text-white text-sm font-medium rounded-lg transition-colors duration-200 shadow-sm">
                    Volver a inicio
                </a>
            @else
                <a href="{{ route('users.index') }}" 
                   class="flex items-center justify-center px-4 py-2 bg-[#0B628D] hover:bg-[#19262c] text-white text-sm font-medium rounded-lg transition-colors duration-200 shadow-sm">
                    Volver a inicio
                </a>
            @endif
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200 p-6 max-w-2xl mx-auto">
        <form action="{{ route('profile.update') }}" method="POST">
            @csrf
            @method('PUT')

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-50 text-red-700 rounded">
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
                <div class="mb-4">
                    <label for="{{ $field }}" class="block text-sm font-medium text-gray-700 mb-1">{{ $label }}</label>
                    <input type="text"
                           id="{{ $field }}" 
                           name="{{ $field }}" 
                           value="{{ old($field, auth()->user()->$field) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
            @endforeach

            <div class="mb-4">
                <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Usuario</label>
                <input type="text"
                       id="username" 
                       name="username" 
                       value="{{ old('username', auth()->user()->username) }}"
                       disabled
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-100">
            </div>

            <div class="mb-4">
                <label class="inline-flex items-center">
                    <input type="checkbox" id="toggle-password" class="form-checkbox h-4 w-4 text-blue-600">
                    <span class="ml-2 text-sm text-gray-700">Cambiar contraseña</span>
                </label>
            </div>

            <div id="password-fields" class="hidden space-y-4 mb-6">
                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña actual</label>
                    <input type="password"
                           id="current_password" 
                           name="current_password"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
            
                <div>
                    <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">Nueva contraseña</label>
                    <input type="password"
                           id="new_password" 
                           name="new_password"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <button type="submit"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gradient-to-r from-[#0e71a2] to-[#074665] hover:from-[#084665] hover:to-[#06364e] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200 cursor-pointer">
                Actualizar mis datos
            </button>
        </form>
    </div>
</div>

<script>
    document.getElementById('toggle-password').addEventListener('change', function() {
        document.getElementById('password-fields').classList.toggle('hidden');
    });
</script>
@endsection