@extends('layouts.admin')

@section('dashboard-option')
    <div class="relative overflow-x-auto max-w-6xl mx-auto">
        <div class="flex items-center justify-between bg-white my-5 p-2 px-4 rounded-full border-2 shadow-2xl">
            <h2 class="text-xl font-black text-yellow-700">Ver usuario</h2>
            <a href="{{ route('users.index') }}"
                class="bg-[#0B628D] hover:bg-[#2d4652] text-white rounded-sm p-2 text-sm font-semibold hover:cursor-pointer">Volver
                inicio</a>
        </div>

        <div class="bg-white shadow-xl rounded-lg p-6 max-w-6xl mx-auto">

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <p class=""><span class="font-semibold">ID:</span> {{ $user->id }}</p>
                    <p class=""><span class="font-semibold">Usuario:</span> {{ $user->username }}</p>
                    <p class=""><span class="font-semibold">Nombre:</span> {{ $user->name }}</p>
                    <p class=""><span class="font-semibold">Apellido:</span> {{ $user->surname }}</p>
                </div>

                <div>
                    <p class=""><span class="font-semibold">Correo Electrónico:</span> {{ $user->email }}</p>
                    <p class=""><span class="font-semibold">Teléfono:</span> {{ $user->phone ?? 'No disponible' }}</p>
                    <p class=""><span class="font-semibold">Rol:</span> {{ $user->role->name ?? 'No asignado' }}</p>
                </div>

                <div>
                    <p class=""><span class="font-semibold">Estado:</span>
                        <span class="px-3 py-1 rounded-full"
                        {{-- {{ $user->active ? 'bg-green-500' : 'bg-red-500' }}" --}}
                        >
                            {{ $user->active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </p>
                    <p class=""><span class="font-semibold">Verificación de Email:</span>
                        {{ $user->email_verified_at ? $user->email_verified_at->format('d/m/Y H:i') : 'No verificado' }}
                    </p>
                    <p class="">
                        <span class="font-semibold">Creado el:</span>
                        {{ $user->created_at->translatedFormat('F j, Y - H:i') }}
                    </p>

                    <p class="">
                        <span class="font-semibold">Última actualización:</span>
                        {{ $user->updated_at->translatedFormat('F j, Y - H:i') }}
                    </p>
                </div>

            </div>
        </div>
        <div class="bg-white shadow-xl rounded-lg p-6 max-w-6xl mx-auto mt-5">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Cotizaciones</h2>
        </div>

    </div>
@endsection
