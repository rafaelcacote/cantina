@extends('layouts.app')

@section('content')
    @php
        $inputClass = fn (string $field) => 'h-11 w-full rounded-lg border bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:text-white/90 '.($errors->has($field) ? 'border-error-500 dark:border-error-500' : 'border-gray-300 dark:border-gray-700');

        $statusFlow = ['pending', 'confirmed', 'preparing', 'ready', 'delivered'];
        $currentFlowIndex = array_search($order->status, $statusFlow, true);
        $isCancelled = $order->status === 'cancelled';
        $nextStatus = (! $isCancelled && $currentFlowIndex !== false && $currentFlowIndex < count($statusFlow) - 1)
            ? $statusFlow[$currentFlowIndex + 1]
            : null;

        $nextStatusLabels = [
            'confirmed' => 'Confirmar pedido',
            'preparing' => 'Iniciar preparo',
            'ready' => 'Marcar como pronto',
            'delivered' => 'Marcar como entregue',
        ];

        $statusBadgeClass = match ($order->status) {
            'delivered' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
            'cancelled' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
            'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
            default => 'bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-300',
        };

        $pinAlreadyProvided = $pinAlreadyProvided ?? false;
        $needsPin = $order->payment_mode === 'tab'
            && ! $pinAlreadyProvided
            && ! in_array($order->status, \App\Services\OrderService::COMMITTED_STATUSES, true);
        $nextNeedsPin = $needsPin && $nextStatus && in_array($nextStatus, \App\Services\OrderService::COMMITTED_STATUSES, true);

        $studentInitials = $order->student?->name
            ? collect(preg_split('/\s+/', trim($order->student->name)))
                ->filter()
                ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
                ->take(2)
                ->implode('')
            : '—';

        $completedPayment = $order->payments->firstWhere('status', 'completed');
    @endphp

    <style>
        [x-cloak] { display: none !important; }
    </style>

    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2.5">
                    <h1 class="flex items-center gap-2.5 text-2xl font-semibold text-gray-800 dark:text-white/90">
                        <span class="inline-flex size-8 items-center justify-center text-brand-500 dark:text-brand-400">
                            {!! \App\Helpers\MenuHelper::getIconSvg('tables') !!}
                        </span>
                        Pedido #{{ $order->id }}
                    </h1>
                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $statusBadgeClass }}">
                        {{ $statuses[$order->status] ?? $order->status }}
                    </span>
                </div>
                <p class="mt-1.5 text-sm text-gray-500 dark:text-gray-400">
                    {{ $order->student?->name ?? 'Sem aluno vinculado' }}
                    <span class="text-gray-300 dark:text-gray-600">·</span>
                    {{ $order->school?->name ?? 'Sem escola' }}
                    <span class="text-gray-300 dark:text-gray-600">·</span>
                    {{ $order->created_at?->format('d/m/Y H:i') ?? '-' }}
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('tenant.orders.index') }}"
                   class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-transparent dark:text-gray-300 dark:hover:bg-white/5">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Voltar
                </a>
                <a href="{{ route('tenant.orders.edit', $order) }}"
                   class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 text-sm font-medium text-white transition-colors hover:bg-brand-600">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M4 20h4l10.5-10.5a2.121 2.121 0 0 0-3-3L5 17v3Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M13.5 6.5l3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Editar
                </a>
                <button type="button"
                        title="Excluir"
                        data-name="Pedido #{{ $order->id }}{{ $order->student?->name ? ' ('.$order->student->name.')' : '' }}"
                        data-action="{{ route('tenant.orders.destroy', $order) }}"
                        @click="$dispatch('open-confirm-delete', {
                            name: $el.dataset.name,
                            action: $el.dataset.action,
                            title: 'Excluir pedido?'
                        })"
                        class="inline-flex size-10 items-center justify-center rounded-lg border border-error-300 bg-white text-error-600 transition-colors hover:bg-error-50 dark:border-error-500/40 dark:bg-transparent dark:text-error-400 dark:hover:bg-error-500/10">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M3 6h18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        <path d="M8 6V4.5A1.5 1.5 0 0 1 9.5 3h5A1.5 1.5 0 0 1 16 4.5V6M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M10 11v6M14 11v6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    <span class="sr-only">Excluir</span>
                </button>
            </div>
        </div>

        @if ($errors->any())
            <div class="rounded-xl border border-error-500 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/15 dark:text-error-400">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex flex-col gap-5 p-5 lg:flex-row lg:items-center lg:justify-between lg:px-6">
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Andamento</p>

                    @if ($isCancelled)
                        <p class="mt-3 text-sm font-medium text-error-600 dark:text-error-400">
                            Este pedido foi cancelado.
                        </p>
                    @else
                        <div class="mt-4 flex min-w-[20rem]">
                            @foreach ($statusFlow as $index => $key)
                                @php
                                    $reached = $currentFlowIndex !== false && $index <= $currentFlowIndex;
                                    $current = $order->status === $key;
                                    $lineDone = $currentFlowIndex !== false && $index < $currentFlowIndex;
                                    $prevDone = $currentFlowIndex !== false && $index <= $currentFlowIndex && $index > 0;
                                @endphp
                                <div class="flex min-w-0 flex-1 flex-col items-center">
                                    <div class="flex w-full items-center">
                                        <span @class([
                                            'h-0.5 flex-1',
                                            'bg-transparent' => $loop->first,
                                            'bg-brand-500' => ! $loop->first && $prevDone,
                                            'bg-gray-200 dark:bg-gray-700' => ! $loop->first && ! $prevDone,
                                        ])></span>
                                        <span @class([
                                            'flex size-7 shrink-0 items-center justify-center rounded-full text-xs font-semibold',
                                            'bg-brand-500 text-white' => $reached,
                                            'bg-gray-100 text-gray-400 dark:bg-white/10 dark:text-gray-500' => ! $reached,
                                            'ring-2 ring-brand-200 dark:ring-brand-500/40' => $current,
                                        ])>
                                            @if ($reached && ! $current)
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                    <path d="M5 12l5 5L20 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            @else
                                                {{ $index + 1 }}
                                            @endif
                                        </span>
                                        <span @class([
                                            'h-0.5 flex-1',
                                            'bg-transparent' => $loop->last,
                                            'bg-brand-500' => ! $loop->last && $lineDone,
                                            'bg-gray-200 dark:bg-gray-700' => ! $loop->last && ! $lineDone,
                                        ])></span>
                                    </div>
                                    <span @class([
                                        'mt-2 px-0.5 text-center text-[11px] leading-tight',
                                        'font-semibold text-gray-800 dark:text-white/90' => $current,
                                        'font-medium text-gray-600 dark:text-gray-300' => $reached && ! $current,
                                        'text-gray-400 dark:text-gray-500' => ! $reached,
                                    ])>
                                        {{ $statuses[$key] }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if (! $isCancelled)
                    <div class="flex shrink-0 flex-col gap-2 sm:items-end"
                         x-data="{ showPin: false, submitting: false }">
                        @if ($order->payment_mode === 'tab' && $pinAlreadyProvided)
                            <div class="flex items-start gap-2 rounded-xl border border-brand-200 bg-brand-50 px-3 py-2.5 text-sm text-brand-800 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-200">
                                <svg class="mt-0.5 size-4 shrink-0" width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M7 11V8a5 5 0 0 1 10 0v3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    <rect x="5" y="11" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.8"/>
                                    <path d="M12 15v2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                                <p>Pedido solicitado mediante inserção do PIN.</p>
                            </div>
                        @endif
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start">
                        @if ($nextStatus)
                            <form method="POST"
                                  action="{{ route('tenant.orders.status.update', $order) }}"
                                  class="flex flex-col gap-2"
                                  @submit="if (submitting) { $event.preventDefault(); return; } submitting = true">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="{{ $nextStatus }}">
                                <div x-show="showPin" x-cloak class="w-full rounded-xl border border-gray-200 bg-gray-50 p-3 sm:w-64 dark:border-gray-700 dark:bg-white/5">
                                    <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-300">
                                        PIN do aluno
                                    </label>
                                    <input type="password"
                                           name="student_pin"
                                           x-ref="nextPin"
                                           autocomplete="one-time-code"
                                           inputmode="numeric"
                                           maxlength="20"
                                           placeholder="••••"
                                           class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:bg-transparent dark:text-white/90">
                                </div>
                                @if ($nextNeedsPin)
                                    <button type="button"
                                            x-show="!showPin"
                                            @click="showPin = true; $nextTick(() => $refs.nextPin?.focus())"
                                            class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 text-sm font-medium text-white transition-colors hover:bg-brand-600">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        {{ $nextStatusLabels[$nextStatus] ?? 'Avançar status' }}
                                    </button>
                                @endif
                                <button type="submit"
                                        @if ($nextNeedsPin) x-show="showPin" x-cloak @endif
                                        class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 text-sm font-medium text-white transition-colors hover:bg-brand-600">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    {{ $nextNeedsPin ? 'Confirmar com PIN' : ($nextStatusLabels[$nextStatus] ?? 'Avançar status') }}
                                </button>
                            </form>
                        @endif

                        <form method="POST"
                              action="{{ route('tenant.orders.status.update', $order) }}"
                              onsubmit="return confirm('Cancelar este pedido? Se já estiver confirmado, estoque e pagamento serão estornados.')">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="cancelled">
                            <button type="submit"
                                    class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-transparent dark:text-gray-300 dark:hover:bg-white/5">
                                Cancelar pedido
                            </button>
                        </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="flex flex-col gap-6 xl:flex-row">
            <div class="min-w-0 flex-[1.7] space-y-6">
                @include('pages.tenant.orders.partials.items')

                @if ($order->notes)
                    <div class="rounded-2xl border border-amber-200 bg-amber-50/80 p-5 dark:border-amber-500/20 dark:bg-amber-500/10">
                        <h2 class="text-sm font-semibold text-amber-900 dark:text-amber-200">Observações do pedido</h2>
                        <p class="mt-2 whitespace-pre-line text-sm text-amber-900/80 dark:text-amber-100/80">{{ $order->notes }}</p>
                    </div>
                @endif
            </div>

            <aside class="min-w-0 flex-1 space-y-6">
                <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                        <h2 class="text-sm font-semibold text-gray-800 dark:text-white/90">Resumo financeiro</h2>
                    </div>
                    <div class="space-y-3 px-5 py-4">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Subtotal</span>
                            <span class="font-medium text-gray-800 dark:text-white/90">R$ {{ number_format((float) $order->total_amount, 2, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Desconto</span>
                            <span class="font-medium {{ (float) $order->discount_amount > 0 ? 'text-green-600 dark:text-green-400' : 'text-gray-800 dark:text-white/90' }}">
                                {{ (float) $order->discount_amount > 0 ? '− ' : '' }}R$ {{ number_format((float) $order->discount_amount, 2, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between border-t border-dashed border-gray-200 pt-3 dark:border-gray-800">
                            <span class="text-sm font-semibold text-gray-800 dark:text-white/90">Total</span>
                            <span class="text-xl font-semibold tracking-tight text-gray-900 dark:text-white">
                                R$ {{ number_format((float) $order->final_amount, 2, ',', '.') }}
                            </span>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 border-t border-gray-100 bg-gray-50 px-5 py-3 dark:border-gray-800 dark:bg-white/[0.02]">
                        @if ($order->payment_mode)
                            <span class="inline-flex rounded-full bg-white px-2.5 py-1 text-xs font-medium text-gray-700 ring-1 ring-gray-200 dark:bg-transparent dark:text-gray-300 dark:ring-gray-700">
                                {{ $paymentModes[$order->payment_mode] ?? $order->payment_mode }}
                            </span>
                        @endif
                        @if ($completedPayment)
                            <a href="{{ route('tenant.payments.show', $completedPayment) }}"
                               class="inline-flex rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700 hover:bg-green-200 dark:bg-green-900/30 dark:text-green-300">
                                Pagamento registrado
                            </a>
                        @elseif (! $isCancelled && in_array($order->status, \App\Services\OrderService::COMMITTED_STATUSES, true))
                            @if ($order->payment_mode === 'wallet')
                                <span class="inline-flex rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-300">Debitado da carteira</span>
                            @elseif ($order->payment_mode === 'tab')
                                <span class="inline-flex rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-300">Lançado no fiado</span>
                            @endif
                        @elseif ($order->payment_mode && ! $isCancelled)
                            <span class="inline-flex rounded-full bg-yellow-100 px-2.5 py-1 text-xs font-medium text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">
                                Processado ao confirmar
                            </span>
                        @endif
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                        <h2 class="text-sm font-semibold text-gray-800 dark:text-white/90">Quem pediu</h2>
                    </div>
                    <div class="space-y-4 p-5">
                        <div class="flex items-center gap-3">
                            @if ($order->student?->photoSrc())
                                <img src="{{ $order->student->photoSrc() }}"
                                     alt="{{ $order->student->name }}"
                                     class="size-11 rounded-full object-cover ring-1 ring-gray-200 dark:ring-gray-700">
                            @else
                                <span class="inline-flex size-11 items-center justify-center rounded-full bg-brand-50 text-sm font-semibold text-brand-700 dark:bg-brand-500/15 dark:text-brand-300">
                                    {{ $studentInitials }}
                                </span>
                            @endif
                            <div class="min-w-0">
                                @if ($order->student)
                                    <a href="{{ route('tenant.students.show', $order->student) }}"
                                       class="block truncate text-sm font-semibold text-gray-800 hover:text-brand-600 dark:text-white/90 dark:hover:text-brand-300">
                                        {{ $order->student->name }}
                                    </a>
                                @else
                                    <p class="text-sm font-semibold text-gray-800 dark:text-white/90">Sem aluno</p>
                                @endif
                                <p class="text-xs text-gray-500 dark:text-gray-400">Aluno</p>
                            </div>
                        </div>

                        <dl class="space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Escola</dt>
                                <dd class="text-right text-sm font-medium text-gray-800 dark:text-white/90">
                                    @if ($order->school)
                                        <a href="{{ route('tenant.schools.show', $order->school) }}" class="hover:text-brand-600 dark:hover:text-brand-300">
                                            {{ $order->school->name }}
                                        </a>
                                    @else
                                        —
                                    @endif
                                </dd>
                            </div>
                            <div class="flex items-start justify-between gap-3">
                                <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Responsável</dt>
                                <dd class="text-right text-sm font-medium text-gray-800 dark:text-white/90">
                                    @if ($order->parent)
                                        <a href="{{ route('tenant.parents.show', $order->parent) }}" class="hover:text-brand-600 dark:hover:text-brand-300">
                                            {{ $order->parent->name }}
                                        </a>
                                    @else
                                        —
                                    @endif
                                </dd>
                            </div>
                            <div class="flex items-start justify-between gap-3">
                                <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Registrado por</dt>
                                <dd class="text-right text-sm font-medium text-gray-800 dark:text-white/90">{{ $order->placedBy?->name ?? '—' }}</dd>
                            </div>
                            <div class="flex items-start justify-between gap-3">
                                <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Canal</dt>
                                <dd class="text-right text-sm font-medium text-gray-800 dark:text-white/90">{{ $channels[$order->order_channel] ?? $order->order_channel }}</dd>
                            </div>
                            <div class="flex items-start justify-between gap-3">
                                <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Tipo</dt>
                                <dd class="text-right text-sm font-medium text-gray-800 dark:text-white/90">{{ $types[$order->order_type] ?? $order->order_type }}</dd>
                            </div>
                            @if ($order->scheduled_for)
                                <div class="rounded-xl bg-brand-50 px-3 py-2.5 dark:bg-brand-500/10">
                                    <dt class="text-xs uppercase tracking-wide text-brand-700 dark:text-brand-300">Agendado para</dt>
                                    <dd class="mt-0.5 text-sm font-semibold text-brand-800 dark:text-brand-200">
                                        {{ $order->scheduled_for->format('d/m/Y H:i') }}
                                    </dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                        <h2 class="text-sm font-semibold text-gray-800 dark:text-white/90">Alterar para outro status</h2>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Use se precisar pular ou voltar uma etapa.</p>
                    </div>
                    <form method="POST"
                          action="{{ route('tenant.orders.status.update', $order) }}"
                          class="space-y-4 p-5"
                          x-data="{
                              status: @js(old('status', $order->status)),
                              original: @js($order->status),
                              needsPin: @js($needsPin),
                              committed: @js(\App\Services\OrderService::COMMITTED_STATUSES),
                              showPin: false,
                              submitting: false,
                              onChange() {
                                  this.showPin = this.needsPin && this.committed.includes(this.status) && this.status !== this.original;
                              },
                              submitForm() {
                                  if (this.submitting) return;
                                  this.submitting = true;
                                  this.$el.submit();
                              }
                          }"
                          novalidate>
                        @csrf
                        @method('PATCH')
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Status <span class="text-error-500">*</span>
                            </label>
                            <select name="status"
                                    x-model="status"
                                    @change="onChange()"
                                    class="{{ $inputClass('status') }}">
                                @foreach ($statuses as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('status')
                                <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div x-show="showPin" x-cloak>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                PIN do aluno
                                <span class="text-xs font-normal text-gray-500">(obrigatório ao confirmar fiado)</span>
                            </label>
                            <input type="password"
                                   name="student_pin"
                                   autocomplete="one-time-code"
                                   inputmode="numeric"
                                   maxlength="20"
                                   class="{{ $inputClass('student_pin') }}"
                                   placeholder="••••">
                            @error('student_pin')
                                <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit"
                                class="inline-flex h-10 w-full items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-transparent dark:text-gray-300 dark:hover:bg-white/5">
                            Aplicar status
                        </button>
                    </form>
                </div>
            </aside>
        </div>
    </div>
@endsection
