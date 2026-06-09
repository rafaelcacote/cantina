@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Vínculos</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Relacionamento entre alunos e responsáveis.</p>
            </div>
            <a href="{{ route('tenant.student-parents.create') }}"
               class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                Novo Vínculo
            </a>
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-900/40 dark:bg-green-900/20 dark:text-green-300">{{ session('success') }}</div>
        @endif

        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <form method="GET" action="{{ route('tenant.student-parents.index') }}" class="flex flex-wrap gap-3">
                <input type="text" name="search" value="{{ $search }}" placeholder="Buscar aluno ou responsável..."
                       class="h-11 min-w-[240px] flex-1 rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90">
                <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Filtrar</button>
                <a href="{{ route('tenant.student-parents.index') }}"
                   class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Limpar</a>
            </form>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead>
                    <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        <th class="px-4 py-3">Aluno</th>
                        <th class="px-4 py-3">Responsável</th>
                        <th class="px-4 py-3">Relação</th>
                        <th class="px-4 py-3">Principal</th>
                        <th class="px-4 py-3">Resp. financeiro</th>
                        <th class="px-4 py-3 text-right">Ações</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($links as $link)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-800 dark:text-white/90">{{ $link->student?->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $link->parent?->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $link->relationship_type ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $link->is_primary ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">{{ $link->is_primary ? 'Sim' : 'Não' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $link->financial_responsible ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">{{ $link->financial_responsible ? 'Sim' : 'Não' }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('tenant.student-parents.show', $link) }}" class="rounded-md px-3 py-1.5 text-xs font-medium text-brand-600 hover:bg-brand-50 dark:text-brand-400 dark:hover:bg-white/5">Visualizar</a>
                                    <a href="{{ route('tenant.student-parents.edit', $link) }}" class="rounded-md px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5">Editar</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Nenhum vínculo encontrado.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $links->links() }}</div>
        </div>
    </div>
@endsection
