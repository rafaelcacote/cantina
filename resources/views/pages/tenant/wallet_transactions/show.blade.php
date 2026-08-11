@extends('layouts.app')

@section('content')
    @php
        $typeBadge = match ($transaction->transaction_type) {
            'debit' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
            'refund' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
            'adjustment' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
            default => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
        };
    @endphp

    <div class="mx-auto max-w-5xl space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="flex items-center gap-2.5 text-2xl font-semibold text-gray-800 dark:text-white/90">
                    <span class="inline-flex size-8 items-center justify-center text-brand-500 dark:text-brand-400">
                        {!! \App\Helpers\MenuHelper::getIconSvg('charts') !!}
                    </span>
                    Detalhes da Transação
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Visualize os dados da movimentação da carteira.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('tenant.wallet-transactions.index') }}"
                   class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-transparent dark:text-gray-300 dark:hover:bg-white/5">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Voltar
                </a>
                @if ($transaction->wallet)
                    <a href="{{ route('tenant.student-wallets.show', $transaction->wallet) }}"
                       class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 text-sm font-medium text-white transition-colors hover:bg-brand-600">
                        Ver Carteira
                    </a>
                @endif
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Aluno</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $transaction->student?->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Tipo</dt>
                    <dd class="mt-1">
                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $typeBadge }}">
                            {{ $transactionTypes[$transaction->transaction_type] ?? $transaction->transaction_type }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Valor</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">R$ {{ number_format((float) $transaction->amount, 2, ',', '.') }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Data</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $transaction->created_at?->format('d/m/Y H:i') ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Saldo antes</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">R$ {{ number_format((float) $transaction->balance_before, 2, ',', '.') }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Saldo depois</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">R$ {{ number_format((float) $transaction->balance_after, 2, ',', '.') }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Referência</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">
                        {{ $transaction->reference_type ?: '-' }}{{ $transaction->reference_id ? ' #'.$transaction->reference_id : '' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Criado por</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $transaction->creator?->name ?? '-' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Descrição</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $transaction->description ?: '-' }}</dd>
                </div>
            </dl>
        </div>
    </div>
@endsection
