@extends('layouts.admin')

@section('dashboard-option')
    <div class="relative overflow-x-auto max-w-6xl mx-auto">
        <div class="flex items-center justify-between bg-white my-5 p-2 px-4 rounded-full border-2 shadow-2xl">
            <h2 class="text-xl font-black text-yellow-700">Ver cliente</h2>
            <a href="{{ route('customers.index') }}"
                class="bg-[#0B628D] hover:bg-[#2d4652] text-white rounded-sm p-2 text-sm font-semibold hover:cursor-pointer">Volver
                inicio</a>
        </div>

        <div class="bg-white shadow-xl rounded-lg p-6 max-w-6xl mx-auto">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class=""><span class="font-semibold">CI/NIT:</span> {{ $customer->NIT }}</p>
                    <p class=""><span class="font-semibold">Nombre:</span> {{ $customer->name }}</p>
                    <p class=""><span class="font-semibold">Correo Electrónico:</span> {{ $customer->email }}</p>
                    <p class=""><span class="font-semibold">Teléfono:</span> {{ $customer->phone ?? 'No disponible' }}</p>
                    <p class=""><span class="font-semibold">Celular:</span> {{ $customer->cellphone ?? 'No disponible' }}</p>
                    <p class=""><span class="font-semibold">Direccion:</span> {{ $customer->address ?? 'No disponible' }}</p>
                </div>
                
                <div>
                    <p class=""><span class="font-semibold">Departamento:</span> {{ $customer->department ?? 'No disponible' }}</p>
                    <p class=""><span class="font-semibold">Estado:</span>
                        <span class="px-3 py-1 rounded-full"
                        {{-- {{ $customer->active ? 'bg-green-500' : 'bg-red-500' }}" --}}
                        >
                            {{ $customer->active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </p>
                    <p class=""><span class="font-semibold">Verificación de Email:</span>
                        {{ $customer->email_verified_at ? $customer->email_verified_at->format('d/m/Y H:i') : 'No verificado' }}
                    </p>
                    <p class="">
                        <span class="font-semibold">Creado el:</span>
                        {{ $customer->created_at->translatedFormat('F j, Y - H:i') }}
                    </p>

                    <p class="">
                        <span class="font-semibold">Última actualización:</span>
                        {{ $customer->updated_at->translatedFormat('F j, Y - H:i') }}
                    </p>
                </div>

            </div>
        </div>
        <div class="bg-white shadow-xl rounded-lg p-6 max-w-6xl mx-auto mt-5">
            <h2 class="text-2xl font-bold text-gray-800">Cotizaciones</h2>
        </div>

    </div>
@endsection
