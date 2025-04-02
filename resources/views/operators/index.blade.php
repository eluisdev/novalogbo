
@extends("layouts.operator")

@section("dashboard-option")

<div class="relative overflow-x-auto max-w-6xl mx-auto">
    <div class="flex items-center justify-between bg-white my-5 p-2 px-4 rounded-full border-2 shadow-2xl">
        <h2 class="text-xl font-black text-yellow-700">Usuarios</h2>
        <a href="{{ route("users.create") }}" class="bg-[#0B628D] hover:bg-[#2d4652] text-white rounded-sm p-2 text-sm font-semibold hover:cursor-pointer">Crear Usuario</a>
    </div>
    <table class="w-full text-sm text-left shadow-2xl border-2">
        <thead class="bg-[#F8931E] border-b-[1.5px]">
            <tr>
                <th scope="col" class="px-6 py-3">
                    Nombre completo
                </th>
                <th scope="col" class="px-6 py-3">
                    Correo electronico
                </th>
                <th scope="col" class="px-6 py-3">
                    Telefono
                </th>
                <th scope="col" class="px-6 py-3">
                    Usuario
                </th>
                <th scope="col" class="px-6 py-3">
                    Rol
                </th>
                <th scope="col" class="px-6 py-3">
                    Opciones
                </th>
            </tr>
        </thead>
        <tbody class="bg-white">
            <tr class="">
                <th scope="row" class="px-6 py-4 font-medium text-gray-900">
                    Magic Mouse 2
                </th>
                <td class="px-6 py-4">
                    Black
                </td>
                <td class="px-6 py-4">
                    Accessories
                </td>
                <td class="px-6 py-4">
                    $99
                </td>
                <td class="px-6 py-4">
                    Admin
                </td>
                <td class="p-2 flex items-center justify-center">
                    <a href="{{ route("users.edit") }}" class="bg-yellow-500 hover:bg-yellow-700 w-8 h-8 rounded-full flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mx-auto w-5 h-5 hover:cursor-pointer">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                        </svg>
                    </a>
                    <form method="POST" action="{{ route('users.destroy', "1") }}" id="delete-form">
                      @csrf
                      @method('DELETE')
                    <button 
                    onclick="confirmDelete()"
                        type="button"
                        class="bg-red-500 hover:bg-red-700 w-8 h-8 rounded-full mx-1 hover:cursor-pointer" 
                        >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mx-auto w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                    </button>
                </form>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<script>
    function confirmDelete(userId) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción no se puede deshacer.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(`delete-form`).submit();
            }
        });
    }
</script>
@endsection
