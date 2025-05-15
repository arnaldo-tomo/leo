@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex flex-wrap items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Pessoas Autorizadas</h1>
            <p class="text-gray-600">Gerencie as pessoas com acesso autorizado ao sistema.</p>
        </div>
        <a href="{{ route('authorized.create') }}" class="px-4 py-2 mt-4 text-white transition duration-150 bg-indigo-600 rounded-md hover:bg-indigo-700 sm:mt-0">
            <svg class="inline-block w-5 h-5 mr-1 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Adicionar Pessoa
        </a>
    </div>
</div>

<div class="p-6 bg-white rounded-lg shadow-sm">
    @if($persons->isEmpty())
    <div class="p-4 text-center bg-gray-100 rounded-lg">
        <p class="text-gray-600">Nenhuma pessoa autorizada cadastrada.</p>
        <a href="{{ route('authorized.create') }}" class="inline-block px-4 py-2 mt-2 text-white bg-indigo-600 rounded-md hover:bg-indigo-700">
            Adicionar Pessoa
        </a>
    </div>
    @else
    <!-- Lista de Pessoas -->
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
        @foreach($persons as $person)
        <div class="overflow-hidden bg-white border rounded-lg shadow-sm">
            <div class="flex items-center justify-center p-4 bg-gradient-to-r from-indigo-500 to-purple-600">
                @if($person->photo_path)
                <img src="{{ asset('storage/' . $person->photo_path) }}" alt="{{ $person->name }}" class="object-cover w-32 h-32 rounded-full">
                @else
                <div class="flex items-center justify-center w-32 h-32 text-white bg-indigo-300 rounded-full">
                    <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                @endif
            </div>
            <div class="p-4">
                <h3 class="mb-1 text-lg font-semibold text-gray-900">{{ $person->name }}</h3>
                <p class="mb-3 text-sm text-gray-500">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold leading-none rounded-full
                        {{ $person->active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ $person->active ? 'Ativo' : 'Inativo' }}
                    </span>
                    <span class="inline-flex px-2 py-1 ml-2 text-xs font-semibold leading-none rounded-full
                        {{ $person->access_level === 'admin' ? 'bg-purple-100 text-purple-800' :
                           ($person->access_level === 'restricted' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800') }}">
                        {{ $person->access_level === 'admin' ? 'Admin' :
                           ($person->access_level === 'restricted' ? 'Restrito' : 'Padrão') }}
                    </span>
                </p>

                @if($person->notes)
                <p class="mb-3 text-sm text-gray-600 truncate">{{ $person->notes }}</p>
                @endif

                <p class="text-xs text-gray-500">Adicionado em: {{ $person->created_at->format('d/m/Y H:i') }}</p>

                <!-- Ações -->
                <div class="flex items-center justify-between mt-4 space-x-2">
                    <a href="{{ route('authorized.edit', $person) }}" class="flex items-center justify-center flex-1 px-2 py-2 text-sm font-medium text-indigo-600 transition duration-150 bg-indigo-100 rounded-md hover:bg-indigo-200">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Editar
                    </a>

                    <form action="{{ route('authorized.destroy', $person) }}" method="POST" class="flex-1 delete-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="flex items-center justify-center w-full px-2 py-2 text-sm font-medium text-red-600 transition duration-150 bg-red-100 rounded-md hover:bg-red-200">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Remover
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Confirmação de exclusão
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const name = this.closest('div.overflow-hidden').querySelector('h3').textContent;

            if (confirm(`Tem certeza que deseja remover ${name} da lista de pessoas autorizadas?`)) {
                this.submit();
            }
        });
    });
});
</script>
@endpush