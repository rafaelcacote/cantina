@extends('layouts.app')

@section('content')
    @php
        $badge = match ($topup->status) {
            'approved' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
            'rejected' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
            'pending_review' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
            default => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
        };
    @endphp

    <div class="mx-auto max-w-5xl space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="flex items-center gap-2.5 text-2xl font-semibold text-gray-800 dark:text-white/90">
                    <span class="inline-flex size-8 items-center justify-center text-brand-500 dark:text-brand-400">
                        {!! \App\Helpers\MenuHelper::getIconSvg('charts') !!}
                    </span>
                    Recarga #{{ $topup->code }}
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Confira o Pix e o comprovante antes de creditar a carteira.
                </p>
            </div>
            <a href="{{ route('tenant.wallet-topups.index') }}"
               class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-transparent dark:text-gray-300">
                Voltar
            </a>
        </div>

        @if ($errors->any())
            <div class="rounded-xl border border-error-500 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/15 dark:text-error-400">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <dl class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs uppercase text-gray-500">Aluno</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $topup->student?->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-gray-500">Responsável</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $topup->parent?->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-gray-500">Valor</dt>
                        <dd class="mt-1 text-sm font-semibold text-gray-800 dark:text-white/90">{{ $topup->formattedAmount() }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-gray-500">Status</dt>
                        <dd class="mt-1">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $badge }}">
                                {{ $statuses[$topup->status] ?? $topup->status }}
                            </span>
                        </dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs uppercase text-gray-500">Chave Pix usada</dt>
                        <dd class="mt-1 break-all text-sm font-medium text-gray-800 dark:text-white/90">{{ $topup->pix_key }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-gray-500">Solicitado em</dt>
                        <dd class="mt-1 text-sm text-gray-800 dark:text-white/90">{{ $topup->created_at?->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-gray-500">Conferido por</dt>
                        <dd class="mt-1 text-sm text-gray-800 dark:text-white/90">{{ $topup->reviewer?->name ?? '-' }}</dd>
                    </div>
                    @if ($topup->rejection_reason)
                        <div class="sm:col-span-2">
                            <dt class="text-xs uppercase text-gray-500">Motivo da recusa</dt>
                            <dd class="mt-1 text-sm text-gray-800 dark:text-white/90">{{ $topup->rejection_reason }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Comprovante</p>
                @if ($topup->receiptSrc())
                    <a href="{{ $topup->receiptSrc() }}" target="_blank" rel="noopener" class="mt-3 block">
                        <img src="{{ $topup->receiptSrc() }}" alt="Comprovante" class="max-h-96 w-full rounded-xl object-contain bg-gray-50 dark:bg-white/5">
                    </a>
                @else
                    <p class="mt-3 text-sm text-gray-500">O responsável ainda não enviou o comprovante.</p>
                @endif
            </div>
        </div>

        @if ($topup->canReview())
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <form method="POST" action="{{ route('tenant.wallet-topups.approve', $topup) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                                class="inline-flex h-11 items-center rounded-lg bg-brand-500 px-5 text-sm font-medium text-white hover:bg-brand-600"
                                onclick="return confirm('Creditar {{ $topup->formattedAmount() }} na carteira de {{ $topup->student?->name }}?')">
                            Aprovar e creditar
                        </button>
                    </form>

                    <form method="POST" action="{{ route('tenant.wallet-topups.reject', $topup) }}" class="min-w-0 flex-1 sm:max-w-md">
                        @csrf
                        @method('PATCH')
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Recusar</label>
                        <div class="flex gap-2">
                            <input type="text"
                                   name="rejection_reason"
                                   value="{{ old('rejection_reason') }}"
                                   required
                                   placeholder="Motivo da recusa"
                                   class="h-11 min-w-0 flex-1 rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90">
                            <button type="submit"
                                    class="inline-flex h-11 items-center rounded-lg border border-error-300 px-4 text-sm font-medium text-error-600 hover:bg-error-50 dark:border-error-500/40 dark:text-error-400">
                                Recusar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
@endsection
