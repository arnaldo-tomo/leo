@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
    <p class="text-gray-600">Visão geral do sistema de controle de acesso.</p>
</div>

<div class="grid grid-cols-1 gap-6 mb-6 sm:grid-cols-2 lg:grid-cols-4">
    <!-- Card: Total de Pessoas -->
    <div class="p-6 bg-white rounded-lg shadow-sm">
        <div class="flex items-center">
            <div class="p-3 bg-indigo-100 rounded-full">
                <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Total de Pessoas</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_authorized'] }}</p>
            </div>
        </div>
    </div>

    <!-- Card: Pessoas Ativas -->
    <div class="p-6 bg-white rounded-lg shadow-sm">
        <div class="flex items-center">
            <div class="p-3 bg-green-100 rounded-full">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Pessoas Ativas</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $stats['active_authorized'] }}</p>
            </div>
        </div>
    </div>

    <!-- Card: Acessos Hoje -->
    <div class="p-6 bg-white rounded-lg shadow-sm">
        <div class="flex items-center">
            <div class="p-3 bg-blue-100 rounded-full">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Acessos Hoje</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $stats['access_today'] }}</p>
            </div>
        </div>
    </div>

    <!-- Card: Tentativas Não Autorizadas -->
    <div class="p-6 bg-white rounded-lg shadow-sm">
        <div class="flex items-center">
            <div class="p-3 bg-red-100 rounded-full">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Não Autorizados</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $stats['unauthorized_attempts'] }}</p>
            </div>
        </div>
    </div>

    <div class="p-6 bg-white rounded-lg shadow-sm">
        <div class="flex items-center">
            <div class="p-3 bg-red-100 rounded-full">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Desconhecido</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $stats['unknown_attempts'] }}</p>
            </div>
        </div>
    </div>

    <div class="p-6 bg-white rounded-lg shadow-sm">
        <div class="flex items-center">
            <div class="p-3 bg-red-100 rounded-full">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Autorizado</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $stats['authorized_attempts'] }}</p>
            </div>
        </div>
    </div>

</div>

<!-- Gráfico de Acessos -->
<div class="grid grid-cols-1 gap-6 mb-6 lg:grid-cols-2">
    <div class="p-6 bg-white rounded-lg shadow-sm">
        <h2 class="mb-4 text-lg font-semibold text-gray-700">Histórico de Acessos (7 dias)</h2>
        <div style="height: 300px;">
            <canvas id="accessChart"></canvas>
        </div>
    </div>

    <!-- Logs Recentes -->
    <div class="p-6 bg-white rounded-lg shadow-sm">
        <h2 class="mb-4 text-lg font-semibold text-gray-700">Acessos Recentes</h2>
        <div class="overflow-hidden overflow-x-auto border border-gray-200 rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Pessoa</th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Status</th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Data/Hora</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($recentLogs as $log)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                @if($log->photo_path)
                                <div class="flex-shrink-0 w-10 h-10">
                                    <img class="object-cover w-10 h-10 rounded-full" src="{{ asset('storage/' . $log->photo_path) }}" alt="Foto">
                                </div>
                                @endif
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $log->person_name ?? ($log->authorizedPerson->name ?? 'Desconhecido') }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 text-xs font-semibold leading-5 rounded-full
                                {{ $log->status === 'authorized' ? 'bg-green-100 text-green-800' :
                                   ($log->status === 'unauthorized' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                {{ $log->status === 'authorized' ? 'Autorizado' :
                                   ($log->status === 'unauthorized' ? 'Não Autorizado' : 'Desconhecido') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                            {{ $log->access_time->format('d/m/Y H:i:s') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- JS para o gráfico -->
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('accessChart').getContext('2d');

        // Extrair dados para o gráfico
        const dates = @json(array_keys($dailyStats->toArray()));

        // Preparar os dados
        const authorizedData = [];
        const unauthorizedData = [];
        const unknownData = [];

        dates.forEach(date => {
            let dayStats = @json($dailyStats);
            let authorizedCount = 0;
            let unauthorizedCount = 0;
            let unknownCount = 0;

            if (dayStats[date]) {
                dayStats[date].forEach(stat => {
                    if (stat.status === 'authorized') authorizedCount = stat.count;
                    else if (stat.status === 'unauthorized') unauthorizedCount = stat.count;
                    else unknownCount = stat.count;
                });
            }

            authorizedData.push(authorizedCount);
            unauthorizedData.push(unauthorizedCount);
            unknownData.push(unknownCount);
        });

        // Formatar as datas para exibição
        const formattedDates = dates.map(date => {
            const parts = date.split('-');
            return `${parts[2]}/${parts[1]}`;
        });

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: formattedDates,
                datasets: [{
                    label: 'Autorizados',
                    data: authorizedData,
                    backgroundColor: 'rgba(52, 211, 153, 0.8)',
                    borderColor: 'rgba(52, 211, 153, 1)',
                    borderWidth: 1
                }, {
                    label: 'Não Autorizados',
                    data: unauthorizedData,
                    backgroundColor: 'rgba(248, 113, 113, 0.8)',
                    borderColor: 'rgba(248, 113, 113, 1)',
                    borderWidth: 1
                }, {
                    label: 'Desconhecidos',
                    data: unknownData,
                    backgroundColor: 'rgba(251, 191, 36, 0.8)',
                    borderColor: 'rgba(251, 191, 36, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    });
</script>
@endpush
@endsection