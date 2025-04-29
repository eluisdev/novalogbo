@extends('layouts.app')

@section('content')
    <div class="bg-gray-100 h-screen flex flex-col relative">
        <div class="relative z-20">
            <x-navbar user-name="{{ Auth::user()->username }}" user-role="Operador" logo-path="images/logoNova.png" />

            <!-- Botón de menú móvil -->
            <div class="md:hidden absolute top-20 left-7 z-30">
                <button id="mobile-menu-button" class="bg-gray-950 p-2 rounded shadow text-white">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Backdrop para sidebar móvil -->
        <div id="sidebar-backdrop" class="fixed inset-0 bg-gray-500/50 bg-opacity-90 z-10 hidden md:hidden"></div>

        <section class="flex flex-1 overflow-hidden">
            <!-- SIDEBAR -->
            <div id="sidebar"
                class="w-64 bg-white border-r border-gray-200 transform transition-transform duration-300 ease-in-out
                       fixed md:static top-0 left-0 min-h-full z-30 md:z-10
                       -translate-x-full md:translate-x-0 overflow-y-auto shadow-lg">
                <div class="p-4 border-b border-gray-200">
                    <div class="flex items-center gap-3 text-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-amber-500" viewBox="0 0 48 48">
                            <path fill="#FFA000" d="M40,12H22l-4-4H8c-2.2,0-4,1.8-4,4v8h40v-4C44,13.8,42.2,12,40,12z">
                            </path>
                            <path fill="#FFCA28"
                                d="M40,12H8c-2.2,0-4,1.8-4,4v20c0,2.2,1.8,4,4,4h32c2.2,0,4-1.8,4-4V16C44,13.8,42.2,12,40,12z">
                            </path>
                        </svg>
                        <span class="font-bold text-lg">Aplicaciones</span>
                    </div>
                </div>

                <nav class="flex-1 p-4 py-5 space-y-2">
                    @php
                        $menuItems = [
                            [
                                'route' => 'customers.index',
                                'text' => 'Clientes',
                                'active' => request()->is('customers*'),
                            ],
                            [
                                'route' => 'quotations.index',
                                'text' => 'Cotizaciones',
                                'active' => request()->is('quotation*'),
                            ],
                            [
                                'route' => 'operations.index',
                                'text' => 'Operaciones',
                                'active' => request()->is('operations*'),
                            ],
                        ];
                    @endphp

                    @foreach ($menuItems as $item)
                        <a href="{{ $item['route'] !== '#' ? route($item['route']) : '#' }}"
                            class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200
                                  {{ $item['active'] ? 'bg-amber-500 text-white shadow-md' : 'text-gray-800 hover:bg-gray-100 ' }}">
                            <span class="truncate">{{ $item['text'] }}</span>
                        </a>
                    @endforeach
                </nav>
            </div>

            <div class="flex-1 bg-gradient-to-b from-[#29617a] to-[#163a54] py-6 overflow-y-auto max-h-full">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full">
                    @yield('dashboard-option')
                </div>
            </div>
        </section>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');
        const button = document.getElementById('mobile-menu-button');

        button.addEventListener('click', function() {
            sidebar.classList.toggle('-translate-x-full');
            sidebar.classList.toggle('translate-x-0');
            backdrop.classList.toggle('hidden');
        });

        backdrop.addEventListener('click', function() {
            sidebar.classList.add('-translate-x-full');
            sidebar.classList.remove('translate-x-0');
            backdrop.classList.add('hidden');
        });
    </script>
@endsection
