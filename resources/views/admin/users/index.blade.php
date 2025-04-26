@extends('layouts.admin')

@section('dashboard-option')
    <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
        <div
            class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white rounded-xl shadow-sm p-3 mb-6 border border-gray-200">
            <div class="flex sm:flex-row flex-col items-center gap-6">
                <h2 class="text-xl font-black text-gray-800">
                    <span class="text-[#0B628D]">Usuarios</span>
                </h2>

                <!-- Campo de búsqueda -->
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" id="searchInput"
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-[#0B628D] focus:border-[#0B628D] sm:text-sm"
                        placeholder="Buscar usuario...">
                </div>
            </div>

            <div class="flex space-x-2">
                <a href="{{ route('users.create') }}"
                    class="flex items-center justify-center px-4 py-2 bg-[#0B628D] hover:bg-[#19262c] text-white text-sm font-medium rounded-lg transition-colors duration-200 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Crear Usuario
                </a>
            </div>
        </div>

        <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nombre completo
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Correo electrónico
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Teléfono
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Usuario
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Rol
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody" class="bg-white divide-y divide-gray-200">
                        @if (count($users) === 0)
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                    No hay usuarios registrados
                                </td>
                            </tr>
                        @else
                            @foreach ($users as $user)
                                <tr class="user-row hover:bg-gray-50 transition-colors duration-150"
                                    data-name="{{ strtolower($user->name . ' ' . $user->surname) }}"
                                    data-email="{{ strtolower($user->email) }}" data-phone="{{ strtolower($user->phone) }}"
                                    data-username="{{ strtolower($user->username) }}"
                                    data-role="{{ strtolower($user->role_id == '1' ? 'administrador' : 'operador') }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="">
                                                <div class="text-sm font-medium text-gray-900">{{ $user->name }}
                                                    {{ $user->surname }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $user->email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $user->phone }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $user->username }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $user->role_id == 1
                                    ? 'bg-green-100 text-green-800'
                                    : ($user->role_id == 2
                                        ? 'bg-purple-100 text-purple-800'
                                        : 'bg-blue-100 text-blue-800') }}">
                                            {{ $user->role_id == 1 ? 'Administrador' : ($user->role_id == 2 ? 'Comercial' : 'Operador') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-center space-x-2">
                                            <a href="{{ route('users.show', $user->id) }}"
                                                class="text-blue-600 hover:text-blue-900 p-1 rounded-full hover:bg-blue-50 transition-colors duration-200"
                                                title="Ver detalle">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>
                                            <a href="{{ route('users.edit', $user->id) }}"
                                                class="text-yellow-600 hover:text-yellow-900 p-1 rounded-full hover:bg-yellow-50 transition-colors duration-200"
                                                title="Editar">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>
                                            <x-delete-button route="users.destroy" :id="$user->id" />
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const userRows = document.querySelectorAll('.user-row');
            const noResultsRow = document.createElement('tr');
            noResultsRow.innerHTML =
                '<td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No se encontraron resultados</td>';

            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                let hasResults = false;

                userRows.forEach(row => {
                    const name = row.getAttribute('data-name');
                    const email = row.getAttribute('data-email');
                    const phone = row.getAttribute('data-phone');
                    const username = row.getAttribute('data-username');
                    const role = row.getAttribute('data-role');

                    if (name.includes(searchTerm) ||
                        email.includes(searchTerm) ||
                        phone.includes(searchTerm) ||
                        username.includes(searchTerm) ||
                        role.includes(searchTerm)) {
                        row.style.display = '';
                        hasResults = true;
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Mostrar mensaje si no hay resultados
                const tableBody = document.getElementById('usersTableBody');
                const existingNoResults = tableBody.querySelector('.no-results');

                if (!hasResults && userRows.length > 0) {
                    if (!existingNoResults) {
                        noResultsRow.classList.add('no-results');
                        tableBody.appendChild(noResultsRow);
                    }
                } else {
                    if (existingNoResults) {
                        tableBody.removeChild(existingNoResults);
                    }
                }
            });
        });
    </script>
@endsection
