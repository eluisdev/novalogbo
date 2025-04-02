<nav class="bg-white flex justify-between p-2 px-8 text-sm border-b-2">
    <div class="flex items-center gap-3">
        <img src="{{ asset($logoPath) }}" alt="logo" class="w-12" />
        <span class="font-bold text-yellow-700">{{ $userRole }}</span>
    </div>
    <div class="flex items-center gap-3">
        <select class="px-4 py-2 border rounded-lg text-gray-700 text-xs w-28" id="user-options"
            onchange="handleUserOption(this)">
            <option value="">Opciones</option>
            <option value="{{ route('profile.edit', Auth::user()->id) }}">Editar datos</option>
            <option value="logout">Cerrar sesión</option>
        </select>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
        <p>Hola: <span class="font-bold text-blue-950">{{ $userName }}</span></p>
    </div>
</nav>

<script>
    document.getElementById('user-options').addEventListener('change', function() {
        if (this.value === 'logout') {
            document.getElementById('logout-form').submit();
        } else if (this.value) {
            window.location.href = this.value;
        }
    });
</script>
