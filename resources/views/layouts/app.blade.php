<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Sistema de Controle de Acesso') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Estilos -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Face-API.js -->
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @stack('styles')
</head>
<body class="font-sans antialiased text-gray-900 bg-gray-50">
    <div x-data="{ sidebarOpen: false }" class="flex h-screen overflow-hidden">
        <!-- Sidebar/Navigation -->
        <div :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}"
             class="fixed inset-y-0 left-0 z-30 w-64 transition-transform duration-300 transform bg-gradient-to-br from-indigo-800 to-purple-800 md:translate-x-0 md:relative">
            <div class="flex flex-col h-full">
                <!-- Logo -->
                <div class="flex items-center justify-center h-16 px-4 py-6">
                    <h1 class="text-xl font-semibold text-white truncate">
                        FaceAccess Control
                    </h1>
                </div>

                <!-- Navigation -->
                <div class="flex-grow mt-5 overflow-y-auto">
                    <nav class="px-2 space-y-1">
                        <a href="{{ route('dashboard') }}"
                           class="flex items-center px-4 py-3 text-white transition duration-150 ease-in-out rounded-lg
                                  hover:bg-indigo-700 {{ request()->routeIs('dashboard') ? 'bg-indigo-700' : '' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            Dashboard
                        </a>

                        <a href="{{ route('access.monitor') }}"
                           class="flex items-center px-4 py-3 text-white transition duration-150 ease-in-out rounded-lg
                                  hover:bg-indigo-700 {{ request()->routeIs('access.monitor') ? 'bg-indigo-700' : '' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            Monitoramento
                        </a>

                        <a href="{{ route('access.logs') }}"
                           class="flex items-center px-4 py-3 text-white transition duration-150 ease-in-out rounded-lg
                                  hover:bg-indigo-700 {{ request()->routeIs('access.logs') ? 'bg-indigo-700' : '' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            Registros de Acesso
                        </a>

                        <a href="{{ route('authorized.index') }}"
                           class="flex items-center px-4 py-3 text-white transition duration-150 ease-in-out rounded-lg
                                  hover:bg-indigo-700 {{ request()->routeIs('authorized.*') ? 'bg-indigo-700' : '' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            Pessoas Autorizadas
                        </a>
                    </nav>
                </div>

                <!-- Profile -->
                <div class="px-4 py-4 border-t border-indigo-700">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 12a5 5 0 110-10 5 5 0 010 10zm0 2a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-white">Administrador</p>
                            <p class="text-xs text-indigo-200">Sistema de Controle</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex flex-col flex-1 w-0 overflow-hidden">
            <!-- Mobile Header -->
            <div class="relative z-10 flex items-center justify-between flex-shrink-0 h-16 px-4 bg-white border-b border-gray-200 md:hidden">
                <button @click="sidebarOpen = !sidebarOpen"
                        class="text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500 md:hidden">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <div class="flex items-center">
                    <h1 class="text-lg font-semibold text-gray-800">FaceAccess Control</h1>
                </div>
            </div>

            <!-- Page Content -->
            <main class="relative flex-1 overflow-y-auto focus:outline-none">
                <div class="py-6">
                    <div class="px-4 mx-auto sm:px-6 md:px-8">
                        @if (session('success'))
                            <div class="p-4 mb-4 text-green-700 bg-green-100 border border-green-400 rounded" role="alert">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="p-4 mb-4 text-red-700 bg-red-100 border border-red-400 rounded" role="alert">
                                {{ session('error') }}
                            </div>
                        @endif

                        @yield('content')
                    </div>
                </div>
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>