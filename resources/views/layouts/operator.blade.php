@extends('layouts.app')

@section('content')
    <div class="bg-gray-100 flex flex-col h-screen flex-1">
        <x-navbar 
            user-name="{{ Auth::user()->username }}" 
            user-role="Operador" 
            logo-path="images/logoNova.png" 
        />

        <section class="flex flex-1 h-full overflow-hidden">
            <div class="w-64 bg-white border-r border-gray-200 h-full overflow-y-auto">
                <div class="p-4 border-b border-gray-200">
                    <div class="flex items-center justify-start gap-3 text-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-amber-500" viewBox="0 0 48 48">
                            <path fill="#FFA000" d="M40,12H22l-4-4H8c-2.2,0-4,1.8-4,4v8h40v-4C44,13.8,42.2,12,40,12z"></path>
                            <path fill="#FFCA28" d="M40,12H8c-2.2,0-4,1.8-4,4v20c0,2.2,1.8,4,4,4h32c2.2,0,4-1.8,4-4V16C44,13.8,42.2,12,40,12z"></path>
                        </svg>
                        <span class="font-bold text-lg">Aplicaciones</span>
                    </div>
                </div>
                <nav class="flex-1 p-4 space-y-2">
                    @php
                        $menuItems = [
                            ['route' => 'customers.index', 'text' => 'Clientes', 'active' => request()->is('customers*')],
                            ['route' => '#', 'text' => 'Cotizaciones', 'active' => request()->is('quotes*')],
                            ['route' => '#', 'text' => 'Historial', 'active' => request()->is('history*')],
                        ];
                    @endphp

                    @foreach($menuItems as $item)
                        <a href="{{ $item['route'] !== '#' ? route($item['route']) : '#' }}" 
                           class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200
                                  {{ $item['active'] ? 'bg-amber-500 text-white shadow-md' : 'text-gray-800 hover:bg-gray-100 ' }}">
                            <span class="truncate">{{ $item['text'] }}</span>
                        </a>
                    @endforeach
                </nav>
            </div>

            <div class="flex-1 bg-gradient-to-b from-[#29617a] to-[#163a54] py-6 overflow-y-auto">
                <div class="max-w-7xl mx-auto">
                    @yield('dashboard-option')
                </div>
            </div>
        </section>
    </div>
@endsection