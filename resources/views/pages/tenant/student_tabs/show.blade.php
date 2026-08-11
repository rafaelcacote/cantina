@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-5xl space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="flex items-center gap-2.5 text-2xl font-semibold text-gray-800 dark:text-white/90">
                    <span class="inline-flex size-8 items-center justify-center text-brand-500 dark:text-brand-400">
                        {!! \App\Helpers\MenuHelper::getIconSvg('charts') !!}
                    </span>
                    Detalhes do Fiado
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Visualize a conta de fiado e os lançamentos.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('tenant.student-tabs.index') }}"
                   class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-transparent dark:text-gray-300 dark:hover:bg-white/5">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Voltar
                </a>
                <a href="{{ route('tenant.student-tabs.edit', $tab) }}"
                   class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 text-sm font-medium text-white transition-colors hover:bg-brand-600">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M4 20h4l10.5-10.5a2.121 2.121 0 0 0-3-3L5 17v3Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M13.5 6.5l3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Editar
                </a>
                <form method="POST" action="{{ route('tenant.student-tabs.destroy', $tab) }}" onsubmit="return confirm('Excluir este registro?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-error-300 bg-white px-4 text-sm font-medium text-error-600 transition-colors hover:bg-error-50 dark:border-error-500/40 dark:bg-transparent dark:text-error-400 dark:hover:bg-error-500/10">
                        Excluir
                    </button>
                </form>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Aluno</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $tab->student?->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Saldo em aberto</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">R$ {{ number_format((float) $tab->current_balance, 2, ',', '.') }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Ciclo</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $cycleTypes[$tab->billing_cycle_type] ?? $tab->billing_cycle_type }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Dia de vencimento</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $tab->due_day ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</dt>
                    <dd class="mt-1">
                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $tab->active ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' }}">
                            {{ $tab->active ? 'Ativo' : 'Inativo' }}
                        </span>
                    </dd>
                </div>
            </dl>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Lançamentos</h2>
                <a href="{{ route('tenant.tab-entries.index', ['student_id' => $tab->student_id]) }}"
                   class="text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">
                    Ver todos
                </a>
            </div>
            <div class="overflow-x-auto p-4 pt-0">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead>
                    <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        <th class="px-4 py-3">Valor</th>
                        <th class="px-4 py-3">Data</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Descrição</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($tab->entries as $entry)
                        @php
                            $entryBadge = match ($entry->status) {
                                'paid' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                                'cancelled' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
                                default => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                            };
                        @endphp
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">R$ {{ number_format((float) $entry->amount, 2, ',', '.') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $entry->entry_date?->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $entryBadge }}">
                                    {{ $entryStatuses[$entry->status] ?? $entry->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $entry->description ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                Nenhum lançamento registrado.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
