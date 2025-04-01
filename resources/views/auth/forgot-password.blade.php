@extends("layouts.auth")

@section("auth-action")

    <div class="w-full max-w-md mx-auto p-7 bg-gray-50 rounded-lg shadow-2xl space-y-3">
        <img src="{{ asset('images/logoNova.png') }}" alt="Logo de la app" class="max-w-32 mx-auto">
        <p>Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña de forma segura.</p>
        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 text-red-700 rounded">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="block font-medium text-gray-900">Correro electronico</label>
                <input 
                    type="text" 
                    id="email" 
                    name="email"
                    placeholder="example@example.xyz"
                    value="{{ old('name') }}"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    required 
                    autofocus
                >
            </div>
            <button 
                type="submit" 
                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gradient-to-r from-[#0e71a2] to-[#074665] hover:from-[#084665] hover:to-[#06364e] transition-colors duration-200 hover:cursor-pointer mt-6"
                >
                Reestablecer contraseña
            </button>
            
        </form> 
    </div>
    <div class="text-center text-sm text-gray-600 mt-5">
        ¿Ya tienes cuenta? <a href="{{ route('login') }}" class="text-center text-sm text-gray-700 mt-5 font-semibold">Inicia Sesión</a>
    </div>
@endsection