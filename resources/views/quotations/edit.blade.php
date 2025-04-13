@if (Auth::user()->role_id == '1')
    @php $layout = 'layouts.admin'; @endphp
@else
    @php $layout = 'layouts.operator'; @endphp
@endif

@extends($layout)

@section('dashboard-option')
    <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
        <div
            class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white rounded-xl shadow-sm p-3 mb-6 border border-gray-200">
            <h2 class="text-xl font-black text-gray-800">
                <span class="text-[#0B628D]">Editar cotizacion</span>
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('quotations.index') }}"
                    class="flex items-center justify-center px-4 py-2 bg-[#0B628D] hover:bg-[#19262c] text-white text-sm font-medium rounded-lg transition-colors duration-200 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Volver a la lista
                </a>
            </div>
        </div>

        <div class="w-full mx-auto">
            <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200 mb-6">
                <form action="{{ route('quotations.update', $quotation_data['formData']['id']) }}" method="POST"
                    id="quotationForm">
                    @csrf
                    @method('PUT')
                    @if ($errors->any())
                        <div class="bg-red-100 text-red-700 p-4 rounded-md">
                            <ul class="list-disc pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @csrf
                    @include('quotations.partials.basic-info')
                    @include('quotations.partials.products')
                    @include('quotations.partials.services')
                    @include('quotations.partials.costs')
                    @include('quotations.partials.actions')
                </form>
            </div>
        </div>

        <x-quotations.modal-customer />
        <x-quotations.modal-quotation-description />
        <x-quotations.modal-preview />
    </div>
@endsection
