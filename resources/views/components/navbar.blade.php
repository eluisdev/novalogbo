<nav class="bg-white shadow-sm border-b border-gray-200 px-6 py-3">
    <div class="flex justify-between items-center">

        <div class="flex items-center space-x-4">
            <a href="{{ $userRole === 'Administrador' ? route('admin.dashboard') : route('operator.dashboard') }}" class="flex items-center">
                <img src="{{ asset($logoPath) }}" alt="Logo" class="h-10 w-auto transition-transform hover:scale-105">
            </a>
            <span class="hidden md:inline-block px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                {{ $userRole }}
            </span>
        </div>

        <div class="flex items-center space-x-4">
            <p class="hidden sm:block text-gray-600 text-sm">
                Hola, <span class="font-medium text-blue-800">{{ $userName }}</span>
            </p>
            
            <div class="relative">
                <select 
                    id="user-options"
                    class="appearance-none bg-white border border-gray-300 rounded-lg pl-4 pr-8 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all cursor-pointer"
                    onchange="handleUserOption(this)"
                >
                    <option value="" disabled selected>Opciones</option>
                    <option value="{{ route('profile.show') }}" class="py-1">Ver perfil</option>
                    <option value="{{ route('profile.edit') }}" class="py-1">Editar datos</option>
                    <option value="logout" class="font-bold">Cerrar sesión</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
        @csrf
    </form>
</nav>

<script>
    function handleUserOption(select) {
        if (select.value === 'logout') {
            document.getElementById('logout-form').submit();
        } else if (select.value) {
            window.location.href = select.value;
        }
        select.value = ''; // Resetear el select después de la selección
    }
</script>