@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Registros de Acesso</h1>
    <p class="text-gray-600">Visualize todos os acessos registrados pelo sistema.</p>
</div>

<div class="p-6 bg-white rounded-lg shadow-sm">
    <!-- Filtros -->
    <div class="flex flex-wrap items-center justify-between mb-6">
        <div class="flex flex-wrap items-center space-x-4">
            <div>
                <label for="statusFilter" class="block mb-1 text-sm text-gray-700">Status:</label>
                <select id="statusFilter" class="px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Todos</option>
                    <option value="authorized">Autorizado</option>
                    <option value="unauthorized">Não Autorizado</option>
                    <option value="unknown">Desconhecido</option>
                </select>
            </div>
            <div>
                <label for="dateFilter" class="block mb-1 text-sm text-gray-700">Data:</label>
                <input type="date" id="dateFilter" class="px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
            </div>
        </div>

        <div class="mt-4 sm:mt-0">
            <button id="exportData" class="flex items-center px-4 py-2 font-medium text-white bg-green-600 rounded-md hover:bg-green-700">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Exportar CSV
            </button>
        </div>
    </div>

    <!-- Tabela de Logs -->
    <div class="overflow-hidden overflow-x-auto border border-gray-200 rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                        ID
                    </th>
                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                        Foto
                    </th>
                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                        Pessoa
                    </th>
                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                        Status
                    </th>
                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                        Data/Hora
                    </th>
                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                        Ações
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($logs as $log)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $log->id }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($log->photo_path)
                        <div class="flex-shrink-0 w-10 h-10">
                            <img class="object-cover w-10 h-10 rounded-full" src="{{ asset('storage/' . $log->photo_path) }}" alt="Foto do acesso">
                        </div>
                        @else
                        <div class="flex items-center justify-center w-10 h-10 bg-gray-200 rounded-full">
                            <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                            {{ $log->person_name ?? ($log->authorizedPerson->name ?? 'Desconhecido') }}
                        </div>
                        @if($log->authorizedPerson)
                        <div class="text-xs text-gray-500">
                            ID: {{ $log->authorizedPerson->id }}
                        </div>
                        @endif
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
                    <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                        <button class="text-indigo-600 hover:text-indigo-900 view-details" data-id="{{ $log->id }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Paginação -->
    <div class="mt-4">
        {{ $logs->links() }}
    </div>
</div>

<!-- Modal de detalhes -->
<div id="detailsModal" class="fixed inset-0 z-50 flex items-center justify-center hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>

    <div class="relative z-50 w-full max-w-md p-6 overflow-hidden bg-white rounded-lg shadow-xl">
        <div class="absolute top-0 right-0 pt-4 pr-4">
            <button type="button" id="closeModal" class="text-gray-400 bg-transparent hover:text-gray-500 focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <div class="text-center">
            <h3 class="mb-4 text-lg font-medium text-gray-900" id="modal-title">Detalhes do Acesso</h3>
            <div id="modalContent" class="mt-4">
                <div class="flex justify-center mb-4">
                    <img id="modalImage" class="object-cover w-32 h-32 rounded-full" src="" alt="Foto do acesso">
                </div>
                <div class="mb-4 text-left">
                    <p class="mb-2"><strong>ID:</strong> <span id="modalId"></span></p>
                    <p class="mb-2"><strong>Pessoa:</strong> <span id="modalName"></span></p>
                    <p class="mb-2"><strong>Status:</strong> <span id="modalStatus"></span></p>
                    <p class="mb-2"><strong>Data/Hora:</strong> <span id="modalTime"></span></p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elementos de filtro
    const statusFilter = document.getElementById('statusFilter');
    const dateFilter = document.getElementById('dateFilter');
    const exportBtn = document.getElementById('exportData');

    // Modal
    const detailsModal = document.getElementById('detailsModal');
    const closeModal = document.getElementById('closeModal');
    const modalImage = document.getElementById('modalImage');
    const modalId = document.getElementById('modalId');
    const modalName = document.getElementById('modalName');
    const modalStatus = document.getElementById('modalStatus');
    const modalTime = document.getElementById('modalTime');

    // Abrir modal com detalhes
    document.querySelectorAll('.view-details').forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            const id = row.querySelector('td:nth-child(1) div').textContent.trim();
            const imgSrc = row.querySelector('td:nth-child(2) img')?.src || '';
            const name = row.querySelector('td:nth-child(3) div:first-child').textContent.trim();

            const statusEl = row.querySelector('td:nth-child(4) span');
            const status = statusEl.textContent.trim();
            const statusClass = statusEl.className;

            const time = row.querySelector('td:nth-child(5)').textContent.trim();

            // Preencher modal
            modalId.textContent = id;
            modalName.textContent = name;
            modalStatus.textContent = status;
            modalStatus.className = statusClass;
            modalTime.textContent = time;

            if (imgSrc) {
                modalImage.src = imgSrc;
                modalImage.classList.remove('hidden');
            } else {
                modalImage.classList.add('hidden');
            }

            // Mostrar modal
            detailsModal.classList.remove('hidden');
        });
    });

    // Fechar modal
    closeModal.addEventListener('click', function() {
        detailsModal.classList.add('hidden');
    });

    // Fechar modal ao clicar fora
    window.addEventListener('click', function(event) {
        if (event.target === detailsModal) {
            detailsModal.classList.add('hidden');
        }
    });

    // Aplicar filtros
    function applyFilters() {
        const status = statusFilter.value;
        const date = dateFilter.value;

        let url = new URL(window.location.href);

        // Limpar parâmetros atuais
        url.searchParams.delete('status');
        url.searchParams.delete('date');

        // Adicionar novos parâmetros
        if (status) url.searchParams.append('status', status);
        if (date) url.searchParams.append('date', date);

        // Redirecionar com filtros
        window.location.href = url.toString();
    }

    // Eventos de filtro
    statusFilter.addEventListener('change', applyFilters);
    dateFilter.addEventListener('change', applyFilters);

    // Exportar dados
    exportBtn.addEventListener('click', function() {
        const status = statusFilter.value;
        const date = dateFilter.value;

        let url = new URL('{{ route('access.export') }}', window.location.origin);

        if (status) url.searchParams.append('status', status);
        if (date) url.searchParams.append('date', date);

        window.location.href = url.toString();
    });

    // Preencher filtros com valores da URL
    const params = new URLSearchParams(window.location.search);
    if (params.has('status')) statusFilter.value = params.get('status');
    if (params.has('date')) dateFilter.value = params.get('date');
});
</script>
@endpush