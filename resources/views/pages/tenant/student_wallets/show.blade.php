@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-5xl space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="flex items-center gap-2.5 text-2xl font-semibold text-gray-800 dark:text-white/90">
                    <span class="inline-flex size-8 items-center justify-center text-brand-500 dark:text-brand-400">
                        {!! \App\Helpers\MenuHelper::getIconSvg('charts') !!}
                    </span>
                    Detalhes da Carteira
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Visualize o saldo e o extrato da carteira.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('tenant.student-wallets.index') }}"
                   class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-transparent dark:text-gray-300 dark:hover:bg-white/5">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Voltar
                </a>
                <a href="{{ route('tenant.student-wallets.edit', $wallet) }}"
                   class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 text-sm font-medium text-white transition-colors hover:bg-brand-600">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M4 20h4l10.5-10.5a2.121 2.121 0 0 0-3-3L5 17v3Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M13.5 6.5l3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Editar
                </a>
                <form method="POST" action="{{ route('tenant.student-wallets.destroy', $wallet) }}" onsubmit="return confirm('Excluir este registro?')">
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
                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $wallet->student?->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Saldo</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">R$ {{ number_format((float) $wallet->balance, 2, ',', '.') }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Limite de crédito</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">R$ {{ number_format((float) $wallet->credit_limit, 2, ',', '.') }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Permite negativo</dt>
                    <dd class="mt-1">
                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $wallet->allow_negative_balance ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                            {{ $wallet->allow_negative_balance ? 'Sim' : 'Não' }}
                        </span>
                    </dd>
                </div>
            </dl>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Extrato</h2>
                <a href="{{ route('tenant.wallet-transactions.index', ['student_id' => $wallet->student_id]) }}"
                   class="text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">
                    Ver todos
                </a>
            </div>
            <div class="overflow-x-auto p-4 pt-0">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead>
                    <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        <th class="px-4 py-3">Tipo</th>
                        <th class="px-4 py-3">Valor</th>
                        <th class="px-4 py-3">Saldo depois</th>
                        <th class="px-4 py-3">Data</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($wallet->transactions as $transaction)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $transactionTypes[$transaction->transaction_type] ?? $transaction->transaction_type }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">R$ {{ number_format((float) $transaction->amount, 2, ',', '.') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">R$ {{ number_format((float) $transaction->balance_after, 2, ',', '.') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $transaction->created_at?->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                Nenhuma movimentação registrada.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
