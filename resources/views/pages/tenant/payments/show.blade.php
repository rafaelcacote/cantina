@extends('layouts.app')

@section('content')
    @php
        $statusBadge = match ($payment->status) {
            'completed' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
            'failed' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
            'cancelled' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
            default => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
        };
    @endphp

    <div class="mx-auto max-w-5xl space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="flex items-center gap-2.5 text-2xl font-semibold text-gray-800 dark:text-white/90">
                    <span class="inline-flex size-8 items-center justify-center text-brand-500 dark:text-brand-400">
                        {!! \App\Helpers\MenuHelper::getIconSvg('charts') !!}
                    </span>
                    Detalhes do Pagamento
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Visualize os dados do pagamento registrado.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('tenant.payments.index') }}"
                   class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-transparent dark:text-gray-300 dark:hover:bg-white/5">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Voltar
                </a>
                <a href="{{ route('tenant.payments.edit', $payment) }}"
                   class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 text-sm font-medium text-white transition-colors hover:bg-brand-600">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M4 20h4l10.5-10.5a2.121 2.121 0 0 0-3-3L5 17v3Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M13.5 6.5l3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Editar
                </a>
                <form method="POST" action="{{ route('tenant.payments.destroy', $payment) }}" onsubmit="return confirm('Excluir este pagamento?')">
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
                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $payment->student?->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Responsável</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $payment->parent?->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Pedido</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">
                        @if ($payment->order_id)
                            <a href="{{ route('tenant.orders.show', $payment->order_id) }}" class="text-brand-500 hover:underline">#{{ $payment->order_id }}</a>
                        @else
                            -
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Lançamento de fiado</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">
                        @if ($payment->tab_entry_id)
                            <a href="{{ route('tenant.tab-entries.show', $payment->tab_entry_id) }}" class="text-brand-500 hover:underline">#{{ $payment->tab_entry_id }}</a>
                        @else
                            -
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Valor</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">R$ {{ number_format((float) $payment->amount, 2, ',', '.') }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Método</dt>
                    <dd class="mt-1">
                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                            {{ $methods[$payment->payment_method] ?? $payment->payment_method }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</dt>
                    <dd class="mt-1">
                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $statusBadge }}">
                            {{ $statuses[$payment->status] ?? $payment->status }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Referência</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $payment->reference ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Pago em</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $payment->paid_at?->format('d/m/Y H:i') ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Criado por</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $payment->creator?->name ?? '-' }}</dd>
                </div>
            </dl>
        </div>
    </div>
@endsection
