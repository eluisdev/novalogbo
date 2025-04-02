@extends("layouts.admin")

@section("dashboard-option")

<div class="relative overflow-x-auto max-w-6xl mx-auto">
    <div class="flex items-center justify-between bg-white my-5 p-2 px-4 rounded-full">
        <h2 class="text-xl font-black text-yellow-700">Crear incoterm</h2>
        <a href="{{ route("incoterms.index") }}" class="bg-[#0B628D] hover:bg-[#2d4652] text-white rounded-sm p-2 text-sm font-semibold hover:cursor-pointer">Volver inicio</a>
    </div>

    @include('admin.incoterms.partials.form')

</div>

@endsection