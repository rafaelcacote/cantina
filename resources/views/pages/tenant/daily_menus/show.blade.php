@extends('layouts.app')

@section('content')
    @php
        $inputClass = fn (string $field) => 'h-11 w-full rounded-lg border bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:text-white/90 '.($errors->has($field) ? 'border-error-500 dark:border-error-500' : 'border-gray-300 dark:border-gray-700');
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="flex items-center gap-2.5 text-2xl font-semibold text-gray-800 dark:text-white/90">
                    <span class="inline-flex size-8 items-center justify-center text-brand-500 dark:text-brand-400">
                        {!! \App\Helpers\MenuHelper::getIconSvg('calendar') !!}
                    </span>
                    Detalhes do Cardápio
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Visualize e gerencie os produtos do cardápio.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('tenant.daily-menus.index') }}"
                   class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-transparent dark:text-gray-300 dark:hover:bg-white/5">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Voltar
                </a>
                <a href="{{ route('tenant.daily-menus.edit', $dailyMenu) }}"
                   class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 text-sm font-medium text-white transition-colors hover:bg-brand-600">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M4 20h4l10.5-10.5a2.121 2.121 0 0 0-3-3L5 17v3Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M13.5 6.5l3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Editar
                </a>
                <form method="POST" action="{{ route('tenant.daily-menus.destroy', $dailyMenu) }}" onsubmit="return confirm('Excluir este registro?')">
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
                Verifique os campos do formulário de itens.
            </div>
        @endif

        <div class="flex flex-col gap-6 xl:flex-row">
            <div class="min-w-0 flex-[2] rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Dados do cardápio</h2>
                <dl class="mt-5 grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Escola</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $dailyMenu->school?->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Data</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $dailyMenu->menu_date?->format('d/m/Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Título</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $dailyMenu->title ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</dt>
                        <dd class="mt-1">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $dailyMenu->active ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                                {{ $dailyMenu->active ? 'Ativo' : 'Inativo' }}
                            </span>
                        </dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Descrição</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $dailyMenu->description ?: '-' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="min-w-0 flex-1 overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Adicionar produto</h2>
                </div>
                <form method="POST" action="{{ route('tenant.daily-menus.items.store', $dailyMenu) }}" class="space-y-4 p-6" novalidate>
                    @csrf
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Produto <span class="text-error-500">*</span>
                        </label>
                        <select name="product_id" required class="{{ $inputClass('product_id') }}">
                            <option value="">Selecione</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" @selected((string) old('product_id') === (string) $product->id)>
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('product_id')
                            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-row gap-3">
                        <div class="min-w-0 flex-1">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Qtd planejada</label>
                            <input type="number" name="planned_quantity" min="0" value="{{ old('planned_quantity') }}" class="{{ $inputClass('planned_quantity') }}">
                        </div>
                        <div class="min-w-0 flex-1">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Qtd disponível</label>
                            <input type="number" name="available_quantity" min="0" value="{{ old('available_quantity') }}" class="{{ $inputClass('available_quantity') }}">
                        </div>
                    </div>

                    <div class="flex flex-row gap-3">
                        <div class="min-w-0 flex-1">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Preço sobrescrito</label>
                            <input type="number" name="price_override" step="0.01" min="0" value="{{ old('price_override') }}" class="{{ $inputClass('price_override') }}">
                        </div>
                        <div class="min-w-0 flex-1">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Ordem</label>
                            <input type="number" name="sort_order" min="0" value="{{ old('sort_order', 0) }}" class="{{ $inputClass('sort_order') }}">
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Status <span class="text-error-500">*</span>
                        </label>
                        <select name="active" class="{{ $inputClass('active') }}">
                            <option value="1" @selected(old('active', '1') === '1')>Ativo</option>
                            <option value="0" @selected(old('active', '1') === '0')>Inativo</option>
                        </select>
                    </div>

                    <button type="submit"
                            class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 text-sm font-medium text-white transition-colors hover:bg-brand-600">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        Adicionar item
                    </button>
                </form>
            </div>
        </div>

        @include('pages.tenant.daily_menus.partials.items')
    </div>
@endsection
