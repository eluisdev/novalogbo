@if (Auth::user()->role_id == '1')
    @php $layout = 'layouts.admin'; @endphp
@else
    @php $layout = 'layouts.operator'; @endphp
@endif

@extends($layout)

@section('dashboard-option')
<div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white rounded-xl shadow-sm p-3 mb-6 border border-gray-200">
        <h2 class="text-xl font-black text-gray-800">
            <span class="text-[#0B628D]">Detalles del Cliente</span>
        </h2>
        <div class="flex space-x-2">
            <a href="{{ route('customers.index') }}"
               class="flex items-center justify-center px-4 py-2 bg-[#0B628D] hover:bg-[#19262c] text-white text-sm font-medium rounded-lg transition-colors duration-200 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver a clientes
            </a>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
            <div class="space-y-4">
                <div class="border-b border-gray-100 pb-2">
                    <p class="text-sm font-medium text-gray-500">CI/NIT</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $customer->NIT }}</p>
                </div>

                <div class="border-b border-gray-100 pb-2">
                    <p class="text-sm font-medium text-gray-500">Nombre/Razón Social</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $customer->name }}</p>
                </div>

                <div class="border-b border-gray-100 pb-2">
                    <p class="text-sm font-medium text-gray-500">Correo Electrónico</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $customer->email }}</p>

                </div>

                <div class="border-b border-gray-100 pb-2">
                    <p class="text-sm font-medium text-gray-500">Teléfono/Celular</p>
                    <div class="flex gap-4">
                        <p class="text-gray-700">{{ $customer->phone ?? 'N/A' }}</p>
                        <p class="text-gray-700">{{ $customer->cellphone ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div class="border-b border-gray-100 pb-2">
                    <p class="text-sm font-medium text-gray-500">Dirección</p>
                    <p class="text-gray-700">{{ $customer->address ?? 'No especificada' }}</p>
                </div>

                <div class="border-b border-gray-100 pb-2">
                    <p class="text-sm font-medium text-gray-500">Departamento</p>
                    <p class="text-gray-700">{{ $customer->department ?? 'No especificado' }}</p>
                </div>

                {{-- <div class="border-b border-gray-100 pb-2">
                    <p class="text-sm font-medium text-gray-500">Estado</p>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $customer->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $customer->active ? 'Activo' : 'Inactivo' }}
                    </span>
                </div> --}}

                <div class="border-b border-gray-100 pb-2">
                    <p class="text-sm font-medium text-gray-500">Fecha de Registro</p>
                    <p class="text-gray-700">{{ $customer->created_at->translatedFormat('l, j F Y - H:i') }}</p>
                </div>

                <div class="border-b border-gray-100 pb-2">
                    <p class="text-sm font-medium text-gray-500">Última Actualización</p>
                    <p class="text-gray-700">{{ $customer->updated_at->translatedFormat('l, j F Y - H:i') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Cotizaciones Relacionadas</h3>
        </div>
        <div class="p-6">
            <p class="text-gray-500 text-center py-8">Aquí se mostrarán las cotizaciones del cliente</p>
            <!-- Aquí puedes agregar la tabla o lista de cotizaciones cuando esté disponible -->
        </div>
    </div>
</div>
@endsection
