@extends('layouts.app')

@section('content')
    @php
        $isLow = $stock->product && $stock->quantity <= $stock->product->minimum_stock_alert;
        $inputClass = fn (string $field) => 'h-11 w-full rounded-lg border bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:text-white/90 '.($errors->has($field) ? 'border-error-500 dark:border-error-500' : 'border-gray-300 dark:border-gray-700');
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="flex items-center gap-2.5 text-2xl font-semibold text-gray-800 dark:text-white/90">
                    <span class="inline-flex size-8 items-center justify-center text-brand-500 dark:text-brand-400">
                        {!! \App\Helpers\MenuHelper::getIconSvg('tables') !!}
                    </span>
                    Detalhes do Estoque
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Visualize e ajuste o estoque do produto.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('tenant.stocks.index') }}"
                   class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-transparent dark:text-gray-300 dark:hover:bg-white/5">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Voltar
                </a>
                <a href="{{ route('tenant.stocks.edit', $stock) }}"
                   class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 text-sm font-medium text-white transition-colors hover:bg-brand-600">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M4 20h4l10.5-10.5a2.121 2.121 0 0 0-3-3L5 17v3Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M13.5 6.5l3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Editar
                </a>
                <form method="POST" action="{{ route('tenant.stocks.destroy', $stock) }}" onsubmit="return confirm('Excluir este registro?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-error-300 bg-white px-4 text-sm font-medium text-error-600 transition-colors hover:bg-error-50 dark:border-error-500/40 dark:bg-transparent dark:text-error-400 dark:hover:bg-error-500/10">
                        Excluir
                    </button>
                </form>
            </div>
        </div>

        @if ($errors->any())
            <div class="rounded-xl border border-error-500 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/15 dark:text-error-400">
                Verifique os campos do ajuste rápido.
            </div>
        @endif

        <div class="flex flex-col gap-6 xl:flex-row">
            <div class="min-w-0 flex-[2] rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Dados do produto</h2>
                <dl class="mt-5 grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Produto</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $stock->product?->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Seção</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $stock->product?->section?->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Categoria</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $stock->product?->category?->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Estoque atual</dt>
                        <dd class="mt-1 text-sm font-semibold text-gray-800 dark:text-white/90">{{ $stock->quantity }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Reservado</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $stock->reserved_quantity }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Mínimo configurado</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $stock->product?->minimum_stock_alert ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</dt>
                        <dd class="mt-1">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $isLow ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' : 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' }}">
                                {{ $isLow ? 'Estoque baixo' : 'Normal' }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="min-w-0 flex-1 overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Ajuste rápido</h2>
                </div>
                <form method="POST" action="{{ route('tenant.stocks.adjust', $stock) }}" class="space-y-4 p-6" novalidate>
                    @csrf
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Tipo <span class="text-error-500">*</span>
                        </label>
                        <select name="movement_type" class="{{ $inputClass('movement_type') }}">
                            @foreach ($movementTypes as $key => $label)
                                <option value="{{ $key }}" @selected(old('movement_type') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('movement_type')
                            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Quantidade <span class="text-error-500">*</span>
                        </label>
                        <input type="number" name="quantity" min="0" value="{{ old('quantity') }}" required class="{{ $inputClass('quantity') }}">
                        @error('quantity')
                            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Descrição</label>
                        <textarea name="description" rows="3"
                                  class="w-full rounded-lg border bg-transparent px-4 py-3 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:text-white/90 {{ $errors->has('description') ? 'border-error-500 dark:border-error-500' : 'border-gray-300 dark:border-gray-700' }}">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                            class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 text-sm font-medium text-white transition-colors hover:bg-brand-600">
                        Aplicar ajuste
                    </button>
                </form>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">Últimas movimentações</h3>
                <a href="{{ route('tenant.stock-movements.index', ['product_id' => $stock->product_id]) }}"
                   class="text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">
                    Ver todas
                </a>
            </div>
            <div class="overflow-x-auto p-4 pt-0">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead>
                    <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        <th class="px-4 py-3">Tipo</th>
                        <th class="px-4 py-3">Qtd</th>
                        <th class="px-4 py-3">Anterior</th>
                        <th class="px-4 py-3">Nova</th>
                        <th class="px-4 py-3">Descrição</th>
                        <th class="px-4 py-3">Usuário</th>
                        <th class="px-4 py-3">Data</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($movements as $movement)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $movementTypes[$movement->movement_type] ?? $movement->movement_type }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $movement->quantity }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $movement->previous_quantity }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $movement->new_quantity }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $movement->description ?: '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $movement->creator?->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $movement->created_at?->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                Sem movimentações recentes para este produto.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
