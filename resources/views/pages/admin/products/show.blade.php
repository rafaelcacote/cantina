@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">Detalhes do Produto</h1>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.products.edit', $product) }}" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">
                    Editar
                </a>
                <a href="{{ route('admin.products.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                    Voltar
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800/40 dark:bg-green-900/20 dark:text-green-300">
                {{ session('success') }}
            </div>
        @endif

        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <dl class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div><dt class="text-xs uppercase text-gray-500">Nome</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $product->name }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Tenant</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $tenantName ?? '-' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Seção</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $product->section?->name ?? '-' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Categoria</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $product->category?->name ?? '-' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Preço</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">R$ {{ number_format((float) $product->price, 2, ',', '.') }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Custo</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $product->cost_price !== null ? 'R$ '.number_format((float) $product->cost_price, 2, ',', '.') : '-' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Tipo de produto</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $product->product_type }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Tipo de venda</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $product->sale_type }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">SKU</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $product->sku ?: '-' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Código de barras</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $product->barcode ?: '-' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Alerta mínimo de estoque</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $product->minimum_stock_alert }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">URL da imagem</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200 break-all">{{ $product->image_url ?: '-' }}</dd></div>
                <div class="md:col-span-2"><dt class="text-xs uppercase text-gray-500">Descrição</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $product->description ?: '-' }}</dd></div>
            </dl>

            <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @php
                    $badges = [
                        ['label' => 'Ativo', 'value' => $product->active],
                        ['label' => 'Visível no app', 'value' => $product->visible_in_app],
                        ['label' => 'Pedido customizado', 'value' => $product->allow_custom_request],
                        ['label' => 'Exige preparo', 'value' => $product->requires_preparation],
                        ['label' => 'Controla estoque', 'value' => $product->stock_controlled],
                    ];
                @endphp
                @foreach ($badges as $badge)
                    <div class="rounded-lg border border-gray-200 px-4 py-3 dark:border-gray-800">
                        <p class="text-xs uppercase text-gray-500">{{ $badge['label'] }}</p>
                        <span class="mt-2 inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $badge['value'] ? 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-300' }}">
                            {{ $badge['value'] ? 'Sim' : 'Não' }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
