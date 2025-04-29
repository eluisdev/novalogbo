@extends('layouts.app')

@section('content')
    <div class="bg-gray-100 h-screen flex flex-col relative">
        <div class="relative z-20">
            <x-navbar user-name="{{ Auth::user()->username }}" user-role="Administrador" logo-path="images/logoNova.png" />

            <div class="md:hidden absolute top-20 left-7 z-30">
                <button id="mobile-menu-button" class="bg-gray-950 p-2 rounded shadow text-white">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>

        <div id="sidebar-backdrop" class="fixed inset-0 bg-gray-500/50 bg-opacity-90 z-10 hidden md:hidden"></div>

        <section class="flex flex-1 overflow-hidden">
            <div id="sidebar"
                class="w-64 bg-white border-r border-gray-200 transform transition-transform duration-300 ease-in-out fixed md:static top-0 left-0 h-full z-30 md:z-10 -translate-x-full md:translate-x-0 overflow-y-auto shadow-lg">
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
                            ['route' => 'users.index', 'text' => 'Usuarios', 'active' => request()->is('users*')],
                            [
                                'route' => 'customers.index',
                                'text' => 'Clientes',
                                'active' => request()->is('customers*'),
                            ],
                            [
                                'route' => 'quotations.index',
                                'text' => 'Cotizaciones',
                                'active' => request()->is('quotations*'),
                            ],
                            [
                                'route' => 'operations.index',
                                'text' => 'Operaciones',
                                'active' => request()->is('operations*'),
                            ],
                            [
                                'route' => '#',
                                'text' => 'Reportes',
                                'active' =>
                                    request()->is('reports/quotations*') || request()->is('reports/operations*'),
                                'children' => [
                                    [
                                        'route' => 'reports.quotations',
                                        'text' => 'Cotizaciones',
                                        'active' => request()->is('reports/quotations*'),
                                    ],
                                    [
                                        'route' => 'reports.operations',
                                        'text' => 'Operaciones',
                                        'active' => request()->is('reports/operations*'),
                                    ],
                                ],
                            ],
                            [
                                'route' => '#',
                                'text' => 'Ubicaciones',
                                'active' =>
                                    request()->is('continents*') ||
                                    request()->is('countries*') ||
                                    request()->is('cities*'),
                                'children' => [
                                    [
                                        'route' => 'continents.index',
                                        'text' => 'Continentes',
                                        'active' => request()->is('continents*'),
                                    ],
                                    [
                                        'route' => 'countries.index',
                                        'text' => 'Países',
                                        'active' => request()->is('countries*'),
                                    ],
                                    [
                                        'route' => 'cities.index',
                                        'text' => 'Ciudades',
                                        'active' => request()->is('cities*'),
                                    ],
                                ],
                            ],
                            [
                                'route' => '#',
                                'text' => 'Campos de cotización',
                                'active' =>
                                    request()->is('services*') ||
                                    request()->is('incoterms*') ||
                                    request()->is('costs*') ||
                                    request()->is('quantity_descriptions*') ||
                                    request()->is('exchange-rates*'),
                                'children' => [
                                    [
                                        'route' => 'services.index',
                                        'text' => 'Servicios',
                                        'active' => request()->is('services*'),
                                    ],
                                    [
                                        'route' => 'incoterms.index',
                                        'text' => 'Incoterms',
                                        'active' => request()->is('incoterms*'),
                                    ],
                                    [
                                        'route' => 'costs.index',
                                        'text' => 'Costos',
                                        'active' => request()->is('costs*'),
                                    ],
                                    [
                                        'route' => 'quantity_descriptions.index',
                                        'text' => 'Unidad de cantidad',
                                        'active' => request()->is('quantity_descriptions*'),
                                    ],
                                    [
                                        'route' => 'exchange-rates.index',
                                        'text' => 'Moneda',
                                        'active' => request()->is('exchange-rates*'),
                                    ],
                                ],
                            ],
                            ['route' => 'audits.index', 'text' => 'Historial', 'active' => request()->is('history*')],
                        ];
                    @endphp

                    @foreach ($menuItems as $item)
                        @if (isset($item['children']))
                            <div x-data="{
                                open: {{ $item['active'] ? 'true' : 'false' }},
                                isActive: {{ $item['active'] ? 'true' : 'false' }}
                            }" class="space-y-1">
                                <button @click="open = !open"
                                    class="flex items-center justify-between w-full px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200
                                    {{ $item['active'] ? 'bg-amber-500 text-white shadow-md' : 'text-gray-800 hover:bg-gray-100' }}">
                                    <span class="truncate">{{ $item['text'] }}</span>
                                    <svg :class="{ 'transform rotate-180': open }"
                                        class="w-4 h-4 transition-transform duration-200" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div x-show="open" x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 transform -translate-y-2"
                                    x-transition:enter-end="opacity-100 transform translate-y-0"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 transform translate-y-0"
                                    x-transition:leave-end="opacity-0 transform -translate-y-2"
                                    class="pl-4 space-y-1 overflow-hidden">
                                    @foreach ($item['children'] as $child)
                                        <a href="{{ route($child['route']) }}"
                                            class="flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-200
                                            {{ $child['active'] ? 'bg-amber-400 text-white shadow-md' : 'text-gray-800 hover:bg-gray-100' }}">
                                            <span class="truncate">{{ $child['text'] }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <a href="{{ $item['route'] !== '#' ? route($item['route']) : '#' }}"
                                class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200
                                {{ $item['active'] ? 'bg-amber-500 text-white shadow-md' : 'text-gray-800 hover:bg-gray-100' }}">
                                <span class="truncate">{{ $item['text'] }}</span>
                            </a>
                        @endif
                    @endforeach
                </nav>
            </div>

            <!-- CONTENIDO PRINCIPAL -->
            <div class="flex-1 bg-gradient-to-b from-[#29617a] to-[#163a54] py-6 overflow-y-auto max-h-full">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    @yield('dashboard-option')
                </div>
            </div>
        </section>
    </div>

    <!-- Script para el menú móvil -->
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

    <!-- AlpineJS para el menú desplegable -->
    <script src="//unpkg.com/alpinejs" defer></script>
@endsection
