<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Novalogbo Auth</title>
        @vite("resources/css/app.css")
        @vite(['resources/js/app.js'])
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="bg-gray-300/70 h-dvh flex flex-col">
        <nav class="bg-white flex justify-between p-2 px-8 text-sm border-b-2">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logoNova.png') }}" alt="logo" class="w-12"/>
                <span class="font-bold text-green-700">Operador</span>
            </div>
            <div class="flex items-center gap-3">
                <select class="px-4 py-2 border rounded-lg text-gray-700 text-xs w-28" onchange="if (this.value) window.location.href = this.value;">
                    <option value="">Opciones</option>
                    <option value="{{ route('users.edit') }}" {{ request()->url() == route('users.edit') ? 'selected' : '' }}>Editar datos</option>
                    <option value="{{ route('users.index') }}">Cerrar sesión</option>
                </select>
                <p>Hola: <span class="font-bold text-blue-950">Enrique</span></p>
            </div>
        </nav>
    
        <section class="flex flex-1 h-full min-h-0">
            <div class="w-72 bg-[#dc8727] h-full overflow-y-scroll py-4 border-b-cyan-600">
                <p class="flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="30" height="30" viewBox="0 0 48 48">
                        <path fill="#FFA000" d="M40,12H22l-4-4H8c-2.2,0-4,1.8-4,4v8h40v-4C44,13.8,42.2,12,40,12z"></path>
                        <path fill="#FFCA28" d="M40,12H8c-2.2,0-4,1.8-4,4v20c0,2.2,1.8,4,4,4h32c2.2,0,4-1.8,4-4V16C44,13.8,42.2,12,40,12z"></path>
                    </svg>
                    <span class="font-semibold">Aplicaciones</span>
                </p>
                <ul class="text-center space-y-2 mt-3">
                    <li class="flex justify-between px-5">Clientes<span class="font-semibold">5</span></li>
                    <li class="flex justify-between px-5">Cotizaciones<span class="font-semibold">5</span></li>
                </ul>
            </div>
    
            <div class="h-full flex-1 bg-gradient-to-b from-[#29617a] to-[#163a54] overflow-y-auto p-5">
                @yield("dashboard-option")
            </div>
        </section>
    </div>
</body>
</html>