@extends('layouts.app')

@section('content')
    <div class="bg-gray-300/70">
        <section class="min-h-screen flex flex-col items-center justify-center p-4">
            @yield('auth-action')
        </section>
    </div>
@endsection