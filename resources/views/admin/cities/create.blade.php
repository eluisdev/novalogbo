@extends('layouts.admin')

@section('dashboard-option')
    <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
        <div
            class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white rounded-xl shadow-sm p-3 mb-6 border border-gray-200">
            <h2 class="text-xl font-black text-gray-800">
                <span class="text-[#0B628D]">Crear ciudad</span>
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('cities.index') }}"
                    class="bg-[#0B628D] hover:bg-[#2d4652] text-white rounded-sm p-2 text-sm font-semibold hover:cursor-pointer">
                    Volver inicio
                </a>
            </div>
        </div>

        @include('admin.cities.partials.form')
    </div>
@endsection
