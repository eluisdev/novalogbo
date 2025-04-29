@extends('layouts.app')

@section('content')
    <div class="min-h-screen flex flex-col bg-gradient-to-t from-[#052f43] to-[#032c40]">
        <x-navbar user-name="{{ Auth::user()->username }}" user-role="Operador" logo-path="images/logoNova.png" />

        <div class="flex-grow flex flex-col justify-center"> <!-- Contenedor flexible añadido -->
            <h1 class="text-4xl font-extrabold text-center mb-12 text-amber-300">Aplicaciones</h1>
            <ul class="flex gap-10 justify-center items-center max-w-6xl mx-auto flex-wrap px-4">

                <li
                    class="p-4 md:p-6 rounded-full hover:bg-[#131f24] transition-colors duration-150 ease-in text-white hover:text-amber-500">
                    <a href="{{ route('customers.index') }}" class="flex flex-col items-center">
                        <div class="p-3 md:p-4 rounded-full mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="h-10 md:h-12 w-10 md:w-12">
                                <path fill="currentColor"
                                    d="M96 128a128 128 0 1 0 256 0A128 128 0 1 0 96 128zm94.5 200.2l18.6 31L175.8 483.1l-36-146.9c-2-8.1-9.8-13.4-17.9-11.3C51.9 342.4 0 405.8 0 481.3c0 17 13.8 30.7 30.7 30.7l131.7 0c0 0 0 0 .1 0l5.5 0 112 0 5.5 0c0 0 0 0 .1 0l131.7 0c17 0 30.7-13.8 30.7-30.7c0-75.5-51.9-138.9-121.9-156.4c-8.1-2-15.9 3.3-17.9 11.3l-36 146.9L238.9 359.2l18.6-31c6.4-10.7-1.3-24.2-13.7-24.2L224 304l-19.7 0c-12.4 0-20.1 13.6-13.7 24.2z" />
                            </svg>
                        </div>
                        <p class="text-center font-medium text-sm md:text-base">Clientes</p>
                    </a>
                </li>

                <!-- Cotizaciones -->
                <li
                    class="p-4 md:p-6 rounded-full hover:bg-[#131f24] transition-colors duration-150 ease-in text-white hover:text-amber-500">
                    <a href="{{ route('quotations.index') }}" class="flex flex-col items-center">
                        <div class="p-3 md:p-4 rounded-full mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="h-10 md:h-12 w-10 md:w-12">
                                <path fill="currentColor"
                                    d="M512 80c0 18-14.3 34.6-38.4 48c-29.1 16.1-72.5 27.5-122.3 30.9c-3.7-1.8-7.4-3.5-11.3-5C300.6 137.4 248.2 128 192 128c-8.3 0-16.4 .2-24.5 .6l-1.1-.6C142.3 114.6 128 98 128 80c0-44.2 86-80 192-80S512 35.8 512 80zM160.7 161.1c10.2-.7 20.7-1.1 31.3-1.1c62.2 0 117.4 12.3 152.5 31.4C369.3 204.9 384 221.7 384 240c0 4-.7 7.9-2.1 11.7c-4.6 13.2-17 25.3-35 35.5c0 0 0 0 0 0c-.1 .1-.3 .1-.4 .2c0 0 0 0 0 0s0 0 0 0c-.3 .2-.6 .3-.9 .5c-35 19.4-90.8 32-153.6 32c-59.6 0-112.9-11.3-148.2-29.1c-1.9-.9-3.7-1.9-5.5-2.9C14.3 274.6 0 258 0 256l0-48c0-34.8 53.4-64.5 128-75.4c10.5-1.5 21.4-2.7 32.7-3.5zM416 240c0-21.9-10.6-39.9-24.1-53.4c28.3-4.4 54.2-11.4 76.2-20.5c16.3-6.8 31.5-15.2 43.9-25.5l0 35.4c0 19.3-16.5 37.1-43.8 50.9c-14.6 7.4-32.4 13.7-52.4 18.5c.1-1.8 .2-3.5 .2-5.3zm-32 96c0 18-14.3 34.6-38.4 48c-1.8 1-3.6 1.9-5.5 2.9C304.9 404.7 251.6 416 192 416c-62.8 0-118.6-12.6-153.6-32C14.3 370.6 0 354 0 336l0-35.4c12.5 10.3 27.6 18.7 43.9 25.5C83.4 342.6 135.8 352 192 352s108.6-9.4 148.1-25.9c7.8-3.2 15.3-6.9 22.4-10.9c6.1-3.4 11.8-7.2 17.2-11.2c1.5-1.1 2.9-2.3 4.3-3.4l0 3.4 0 5.7 0 26.3zm32 0l0-32 0-25.9c19-4.2 36.5-9.5 52.1-16c16.3-6.8 31.5-15.2 43.9-25.5l0 35.4c0 10.5-5 21-14.9 30.9c-16.3 16.3-45 29.7-81.3 38.4c.1-1.7 .2-3.5 .2-5.3zM192 448c56.2 0 108.6-9.4 148.1-25.9c16.3-6.8 31.5-15.2 43.9-25.5l0 35.4c0 44.2-86 80-192 80S0 476.2 0 432l0-35.4c12.5 10.3 27.6 18.7 43.9 25.5C83.4 438.6 135.8 448 192 448z" />
                            </svg>
                        </div>
                        <p class="text-center font-medium text-sm md:text-base">Cotizaciones</p>
                    </a>
                </li>

                <!-- Operaciones -->
                <li
                    class="p-4 md:p-6 rounded-full hover:bg-[#131f24] transition-colors duration-150 ease-in text-white hover:text-amber-500">
                    <a href="{{ route('operations.index') }}" class="flex flex-col items-center">
                        <div class="p-3 md:p-4 rounded-full mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="h-10 md:h-12 w-10 md:w-12">
                                <circle cx="256" cy="256" r="120" fill="none" stroke="currentColor"
                                    stroke-width="40" />
                                <circle cx="256" cy="256" r="60" fill="currentColor" />
                                <g stroke="currentColor" stroke-width="40" stroke-linecap="square">
                                    <line x1="256" y1="60" x2="256" y2="140" />
                                    <line x1="371" y1="141" x2="316" y2="196" />
                                    <line x1="452" y1="256" x2="372" y2="256" />
                                    <line x1="371" y1="371" x2="316" y2="316" />
                                    <line x1="256" y1="452" x2="256" y2="372" />
                                    <line x1="141" y1="371" x2="196" y2="316" />
                                    <line x1="60" y1="256" x2="140" y2="256" />
                                    <line x1="141" y1="141" x2="196" y2="196" />
                                </g>
                            </svg>
                        </div>
                        <p class="text-center font-medium text-sm md:text-base">Operaciones</p>
                    </a>
                </li>
            </ul>
        </div>
    @endsection
