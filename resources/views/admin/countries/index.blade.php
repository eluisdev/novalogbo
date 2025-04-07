@extends("layouts.admin")

@section("dashboard-option")
<div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white rounded-xl shadow-sm p-3 mb-6 border border-gray-200">
        <div class="flex items-center gap-6">
            <h2 class="text-xl font-black text-gray-800">
                <span class="text-[#0B628D]">Países</span>
            </h2>
            
            <!-- Campo de búsqueda -->
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text" id="searchInput"
                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-[#0B628D] focus:border-[#0B628D] sm:text-sm"
                    placeholder="Buscar país...">
            </div>
        </div>
        
        <div class="flex space-x-2">
            <a href="{{ route('countries.trashed') }}" 
               class="flex items-center justify-center px-4 py-2 bg-[#0b8d41] hover:bg-[#588498] text-white text-sm font-medium rounded-lg transition-colors duration-200 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
                Recuperar país
            </a>
            <a href="{{ route('countries.create') }}" 
               class="flex items-center justify-center px-4 py-2 bg-[#0B628D] hover:bg-[#19262c] text-white text-sm font-medium rounded-lg transition-colors duration-200 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Crear país
            </a>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Nombre
                        </th>
                        {{-- <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Continente
                        </th> --}}
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody id="countriesTableBody" class="bg-white divide-y divide-gray-200">
                    @if (count($countries) === 0)
                        <tr>
                            <td colspan="2" class="px-6 py-4 text-center text-sm text-gray-500">
                                No hay países registrados
                            </td>
                        </tr>
                    @else
                        @foreach ($countries as $country)
                        <tr class="country-row hover:bg-gray-50 transition-colors duration-150"
                            data-name="{{ strtolower($country->name) }}"
                            @if(isset($country->continent)) 
                                data-continent="{{ strtolower($country->continent->name) }}"
                            @endif>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $country->name }}</div>
                            </td>
                            {{-- <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $country->continent->name }}</div>
                            </td> --}}
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-center space-x-2">
                                    <a href="{{ route('countries.edit', $country->id) }}" 
                                       class="text-yellow-600 hover:text-yellow-900 p-1 rounded-full hover:bg-yellow-50 transition-colors duration-200"
                                       title="Editar">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    <x-delete-button route="countries.destroy" :id="$country->id" />
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
    const countryRows = document.querySelectorAll('.country-row');
    const noResultsRow = document.createElement('tr');
    noResultsRow.innerHTML = '<td colspan="2" class="px-6 py-4 text-center text-sm text-gray-500">No se encontraron resultados</td>';
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        let hasResults = false;
        
        countryRows.forEach(row => {
            const name = row.getAttribute('data-name');
            const continent = row.getAttribute('data-continent') || '';
            
            if (name.includes(searchTerm) || 
                continent.includes(searchTerm)) {
                row.style.display = '';
                hasResults = true;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Mostrar mensaje si no hay resultados
        const tableBody = document.getElementById('countriesTableBody');
        const existingNoResults = tableBody.querySelector('.no-results');
        
        if (!hasResults && countryRows.length > 0) {
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