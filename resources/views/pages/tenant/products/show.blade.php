@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-5xl space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="flex items-center gap-2.5 text-2xl font-semibold text-gray-800 dark:text-white/90">
                    <span class="inline-flex size-8 items-center justify-center text-brand-500 dark:text-brand-400">
                        {!! \App\Helpers\MenuHelper::getIconSvg('forms') !!}
                    </span>
                    Detalhes do Produto
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Visualize os dados completos do produto.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('tenant.products.index') }}"
                   class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-transparent dark:text-gray-300 dark:hover:bg-white/5">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Voltar
                </a>
                @if ($product->stock_controlled && $product->stock)
                    <a href="{{ route('tenant.stocks.show', $product->stock) }}"
                       class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-transparent dark:text-gray-300 dark:hover:bg-white/5">
                        Ver estoque
                    </a>
                @endif
                <a href="{{ route('tenant.products.edit', $product) }}"
                   class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 text-sm font-medium text-white transition-colors hover:bg-brand-600">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M4 20h4l10.5-10.5a2.121 2.121 0 0 0-3-3L5 17v3Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M13.5 6.5l3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Editar
                </a>
                <button type="button"
                        data-name="{{ $product->name }}"
                        data-action="{{ route('tenant.products.destroy', $product) }}"
                        @click="$dispatch('open-confirm-delete', {
                            name: $el.dataset.name,
                            action: $el.dataset.action,
                            title: 'Excluir produto?'
                        })"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-error-300 bg-white px-4 text-sm font-medium text-error-600 transition-colors hover:bg-error-50 dark:border-error-500/40 dark:bg-transparent dark:text-error-400 dark:hover:bg-error-500/10">
                    Excluir
                </button>
            </div>
        </div>


        @if ($errors->any())
            <div class="rounded-xl border border-error-500 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/15 dark:text-error-400">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Nome</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $product->name }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Seção</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $product->section?->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Categoria</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $product->category?->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Preço</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">R$ {{ number_format((float) $product->price, 2, ',', '.') }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Custo</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $product->cost_price !== null ? 'R$ '.number_format((float) $product->cost_price, 2, ',', '.') : '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Tipo de produto</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $productTypes[$product->product_type] ?? $product->product_type }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Tipo de venda</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $saleTypes[$product->sale_type] ?? $product->sale_type }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">SKU</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $product->sku ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Código de barras</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $product->barcode ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Alerta mínimo de estoque</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $product->minimum_stock_alert }}</dd>
                </div>
                @if ($product->stock_controlled)
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Quantidade em estoque</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $product->stock?->quantity ?? 0 }}</dd>
                    </div>
                @endif
                <div class="sm:col-span-2">
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Imagem</dt>
                    <dd class="mt-2">
                        @if ($product->imageSrc())
                            <img src="{{ $product->imageSrc() }}"
                                 alt="Imagem de {{ $product->name }}"
                                 class="rounded-lg border border-gray-200 object-cover dark:border-gray-700"
                                 style="width:96px;height:96px;object-fit:cover;">
                        @else
                            <span class="text-sm font-medium text-gray-800 dark:text-white/90">-</span>
                        @endif
                    </dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Descrição</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $product->description ?: '-' }}</dd>
                </div>
            </dl>

            <div class="mt-6 flex flex-wrap gap-3">
                @php
                    $flags = [
                        ['label' => 'Status', 'value' => $product->active, 'yes' => 'Ativo', 'no' => 'Inativo'],
                        ['label' => 'Visível no app', 'value' => $product->visible_in_app],
                        ['label' => 'Pedido customizado', 'value' => $product->allow_custom_request],
                        ['label' => 'Exige preparo', 'value' => $product->requires_preparation],
                        ['label' => 'Controla estoque', 'value' => $product->stock_controlled],
                    ];
                @endphp
                @foreach ($flags as $flag)
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $flag['label'] }}</p>
                        <span class="mt-1.5 inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $flag['value'] ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                            {{ $flag['value'] ? ($flag['yes'] ?? 'Sim') : ($flag['no'] ?? 'Não') }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
