<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detecção de Pessoas</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <nav class="p-4 text-white bg-blue-600 shadow-md">
            <div class="container flex justify-between mx-auto">
                <div class="text-lg font-semibold">Detecção de Pessoas</div>
            </div>
        </nav>

        <main class="container p-4 mx-auto">
            @yield('content')
        </main>
    </div>

    @yield('scripts')
</body>
</html>