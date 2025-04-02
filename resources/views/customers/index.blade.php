@if (Auth::user()->role->description === 'admin')
    @php $layout = 'layouts.admin'; @endphp
@else
    @php $layout = 'layouts.operator'; @endphp
@endif

@extends($layout)


@section('dashboard-option')

    <div class="relative overflow-x-auto max-w-6xl mx-auto">
        <div class="flex items-center justify-between bg-white my-5 p-2 px-4 rounded-full border-2 shadow-2xl">
            <h2 class="text-xl font-black text-yellow-700">Clientes</h2>
            <a href="{{ route('customers.create') }}"
                class="bg-[#0B628D] hover:bg-[#2d4652] text-white rounded-sm p-2 text-sm font-semibold hover:cursor-pointer">Crear
                cliente</a>
        </div>
        <table class="w-full text-sm text-left shadow-2xl border-2">
            <thead class="bg-[#F8931E] border-b-[1.5px]">
                <tr>
                    <th scope="col" class="px-6 py-3">
                        Nombre completo
                    </th>
                    <th scope="col" class="px-6 py-3">
                        CI / NIT
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Correo electronico
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Telefono
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Celular
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Direccion
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Departamento
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Opciones
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white">
                @if (count($customers) === 0)
                    <tr>
                        <td colspan="100%" class="p-3 text-center">
                            No hay clientes
                        </td>
                    </tr>
                @else
                    @foreach ($customers as $customer)
                        <tr class="">
                            <th scope="row" class="px-6 py-4 font-medium text-gray-900">
                                {{ $customer->name }}
                            </th>
                            <td class="px-6 py-4">
                                {{ $customer->NIT }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $customer->email }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $customer->phone }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $customer->cellphone }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $customer->address }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $customer->department }}
                            </td>
                            <td class="p-2 flex items-center justify-center">
                                <a href="{{ route('customers.edit', $customer->NIT) }}"
                                    class="bg-yellow-500 hover:bg-yellow-700 w-8 h-8 rounded-full flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor"
                                        class="mx-auto w-5 h-5 hover:cursor-pointer">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                    </svg>
                                </a>
                                <x-delete-button route="customers.destroy" :id="$customer->NIT" />
                                <a href="{{ route('customers.show', $customer->NIT) }}"
                                    class="bg-green-500 hover:bg-green-700 w-8 h-8 rounded-full flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto w-5 h-5 hover:cursor-pointer"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
@endsection
