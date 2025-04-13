@if ($user->role_id == '1')
    @php $layout = 'layouts.admin'; @endphp
@else
    @php $layout = 'layouts.operator'; @endphp
@endif

@extends($layout)

@section('dashboard-option')
<div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white rounded-xl shadow-sm p-3 mb-6 border border-gray-200">
        <h2 class="text-xl font-black text-gray-800">
            <span class="text-[#0B628D]">Mi Perfil</span>
        </h2>
        <div class="flex space-x-2">
            @if($user->role->description === 'operator')
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

    <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
            <div class="space-y-4">
                <div class="border-b border-gray-100 pb-2">
                    <p class="text-sm font-medium text-gray-500">Nombre</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $user->name }}</p>
                </div>
                
                <div class="border-b border-gray-100 pb-2">
                    <p class="text-sm font-medium text-gray-500">Apellidos</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $user->surname }}</p>
                </div>
    
                <div class="border-b border-gray-100 pb-2">
                    <p class="text-sm font-medium text-gray-500">Usuario</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $user->username }}</p>
                </div>
    
                <div class="border-b border-gray-100 pb-2">
                    <p class="text-sm font-medium text-gray-500">Correo Electrónico</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $user->email }}</p>
                </div>
                
                @if($user->phone)
                <div class="border-b border-gray-100 pb-2">
                    <p class="text-sm font-medium text-gray-500">Teléfono</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $user->phone }}</p>
                </div>
                @endif
            </div>
    
            <div class="space-y-4">
                <div class="border-b border-gray-100 pb-2">
                    <p class="text-sm font-medium text-gray-500">Rol</p>
                    <p class="text-lg font-semibold text-gray-900">
                        @if($user->role_id == 1)
                            Administrador
                        @else
                            Operador
                        @endif
                    </p>
                </div>
                
                <div class="border-b border-gray-100 pb-2">
                    <p class="text-sm font-medium text-gray-500">Fecha de Registro</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $user->created_at->translatedFormat('l, j F Y - H:i') }}</p>
                </div>
                
                <div class="border-b border-gray-100 pb-2">
                    <p class="text-sm font-medium text-gray-500">Última Actualización</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $user->updated_at->translatedFormat('l, j F Y - H:i') }}</p>
                </div>
            </div>
        </div>
        
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">

            <a href="{{ route('profile.edit') }}" 
               class="flex items-center justify-center px-4 py-2 bg-[#0B628D] hover:bg-[#19262c] text-white text-sm font-medium rounded-lg transition-colors duration-200 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Editar Perfil
            </a>
        </div>
    </div>
</div>
@endsection