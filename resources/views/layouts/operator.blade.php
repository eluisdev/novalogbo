@extends('layouts.app')

@section('content')

    <div class="bg-gray-300/70 h-dvh flex flex-col">
        <x-navbar 
            user-name="{{ Auth::user()->username}}" 
            user-role="Operador" 
            logo-path="images/logoNova.png" 
        />
        <section class="flex flex-1 h-full min-h-0">
            <div class="w-72 bg-[#dc8727] h-full overflow-y-auto py-4 border-b-cyan-600">
                <p class="flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="30" height="30" viewBox="0 0 48 48">
                        <path fill="#FFA000" d="M40,12H22l-4-4H8c-2.2,0-4,1.8-4,4v8h40v-4C44,13.8,42.2,12,40,12z">
                        </path>
                        <path fill="#FFCA28"
                            d="M40,12H8c-2.2,0-4,1.8-4,4v20c0,2.2,1.8,4,4,4h32c2.2,0,4-1.8,4-4V16C44,13.8,42.2,12,40,12z">
                        </path>
                    </svg>
                    <span class="font-semibold">Aplicaciones</span>
                </p>
                <ul class="flex flex-col items-center gap-2 mt-4 px-2 rounded-lg">
                    <li class="w-full">
                        <a href="{{ route('customers.index') }}" 
                           class="block p-2 w-3/4 mx-auto text-center rounded-md shadow transition
                                  {{ request()->is('customers') || request()->is('customers/*') ? 'bg-[#2D4652] text-white' : 'bg-white text-gray-700 hover:bg-[#2D4652] hover:text-white' }}">
                            Clientes
                        </a>
                    </li>
                    <li class="w-full">
                        <a
                           class="block p-2 w-3/4 mx-auto text-center rounded-md shadow transition
                                  {{ request()->is('quotes') || request()->is('quotes/*') ? 'bg-[#2D4652] text-white' : 'bg-white text-gray-700 hover:bg-[#2D4652] hover:text-white' }}">
                            Cotizaciones
                        </a>
                    </li>
                    <li class="w-full">
                        <a
                           class="block p-2 w-3/4 mx-auto text-center rounded-md shadow transition
                                  {{ request()->is('quotation-sectors') || request()->is('quotation-sectors/*') ? 'bg-[#2D4652] text-white' : 'bg-white text-gray-700 hover:bg-[#2D4652] hover:text-white' }}">
                            Sector cotización
                        </a>
                    </li>
                    <li class="w-full">
                        <a
                           class="block p-2 w-3/4 mx-auto text-center rounded-md shadow transition
                                  {{ request()->is('history') || request()->is('history/*') ? 'bg-[#2D4652] text-white' : 'bg-white text-gray-700 hover:bg-[#2D4652] hover:text-white' }}">
                            Historial
                        </a>
                    </li>
                </ul>
            </div>

            <div class="h-full flex-1 bg-gradient-to-b from-[#29617a] to-[#163a54] overflow-y-auto p-5">
                @yield('dashboard-option')
            </div>
        </section>
    </div>
    </body>

    </html>
