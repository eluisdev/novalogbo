
@extends("layouts.admin")

@section("dashboard-option")

<div class="relative overflow-x-auto max-w-4xl mx-auto">
    <div class="flex items-center justify-between bg-white my-5 p-2 px-4 rounded-full border-2 shadow-2xl">
        <h2 class="text-xl font-black text-yellow-700">Ciudades</h2>
        <a href="{{ route("cities.create") }}" class="bg-[#0B628D] hover:bg-[#2d4652] text-white rounded-sm p-2 text-sm font-semibold hover:cursor-pointer">Crear ciudad</a>
    </div>
    <table class="w-full text-sm text-left shadow-2xl border-2">
        <thead class="bg-[#F8931E] border-b-[1.5px]">
            <tr>
                <th scope="col" class="px-6 py-3 text-center">
                    Nombre
                </th>
                <th scope="col" class="px-6 py-3 text-center">
                    Opciones
                </th>
            </tr>
        </thead>
        <tbody class="bg-white">
            @foreach ($cities as $city)
            <tr class="">
                <th scope="row" class="px-6 py-4 font-medium text-gray-900 text-center">
                    {{ $city->name }}
                </th>
                <td class="p-2 flex items-center justify-center">
                    <a href="{{ route("cities.edit", $city->id) }}" class="bg-yellow-500 hover:bg-yellow-700 w-8 h-8 rounded-full flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mx-auto w-5 h-5 hover:cursor-pointer">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                        </svg>
                    </a>
                    <x-delete-button route="cities.destroy" :id="$city->id" />
                   
                </td>
            @endforeach
            </tr>
        </tbody>
    </table>
</div>
@endsection
