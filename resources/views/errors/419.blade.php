<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 - Página no encontrada</title>
    <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .bg-404 {
            background: linear-gradient(135deg, #6b7280 0%, #9ca3af 100%);
        }
    </style>
</head>

<body class="bg-404 h-screen flex items-center justify-center">
    <div class="text-center p-8 bg-white rounded-lg shadow-xl max-w-md mx-4">

        <img src="{{ asset('images/logoNova.png') }}" alt="Logo de la app" class="w-28 mx-auto">
        <h1 class="text-6xl font-bold text-gray-800 mb-4">419</h1>
        <h2 class="text-2xl font-semibold text-gray-700 mb-6">Oops! Página expirada </h2>
        <p class="text-gray-600 mb-8">
            Vuelve a iniciar sesion
        </p>
        <a href="/auth/login"
            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">Volver hacia atras</a>
    </div>
</body>

</html>
