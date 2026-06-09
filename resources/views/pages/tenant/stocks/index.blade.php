@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Estoque</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Gerencie o estoque dos produtos do seu tenant.</p>
            </div>
            <a href="{{ route('tenant.stock-movements.index') }}"
               class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
                Ver Movimentações
            </a>
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-900/40 dark:bg-green-900/20 dark:text-green-300">
                {{ session('success') }}
            </div>
        @endif

        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <form method="GET" action="{{ route('tenant.stocks.index') }}" class="grid grid-cols-1 gap-3 lg:grid-cols-5">
                <select id="section_filter" name="section_id"
                        class="h-11 rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
                    <option value="">Todas as seções</option>
                    @foreach ($sections as $section)
                        <option value="{{ $section->id }}" @selected($sectionId === (int) $section->id)>{{ $section->name }}</option>
                    @endforeach
                </select>

                <select id="category_filter" name="category_id"
                        class="h-11 rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
                    <option value="">Todas as categorias</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" data-section-id="{{ $category->section_id }}" @selected($categoryId === (int) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>

                <select id="product_filter" name="product_id"
                        class="h-11 rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90 lg:col-span-2">
                    <option value="">Todos os produtos</option>
                    @foreach ($products as $product)
                        <option
                            value="{{ $product->id }}"
                            data-section-id="{{ $product->section_id }}"
                            data-category-id="{{ $product->category_id }}"
                            @selected($productId === (int) $product->id)
                        >
                            {{ $product->name }}
                        </option>
                    @endforeach
                </select>

                <label class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-700 dark:border-gray-700 dark:text-gray-300">
                    <input type="checkbox" name="low_stock" value="1" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" @checked($lowStock)>
                    Apenas estoque baixo
                </label>

                <div class="lg:col-span-5 flex justify-end gap-2">
                    <button type="submit"
                            class="inline-flex h-11 items-center justify-center rounded-lg bg-brand-500 px-4 text-sm font-medium text-white hover:bg-brand-600">
                        Filtrar
                    </button>
                    <a href="{{ route('tenant.stocks.index') }}"
                       class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
                        Limpar
                    </a>
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            <th class="px-4 py-3">Produto</th>
                            <th class="px-4 py-3">Seção</th>
                            <th class="px-4 py-3">Categoria</th>
                            <th class="px-4 py-3">Qtd Atual</th>
                            <th class="px-4 py-3">Reservada</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($stocks as $stock)
                            @php
                                $product = $stock->product;
                                $isLow = $product ? $stock->quantity <= $product->minimum_stock_alert : false;
                            @endphp
                            <tr>
                                <td class="px-4 py-3 text-sm font-medium text-gray-800 dark:text-white/90">{{ $product?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $product?->section?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $product?->category?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-800 dark:text-white/90">{{ $stock->quantity }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $stock->reserved_quantity }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $isLow ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' : 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' }}">
                                        {{ $isLow ? 'Baixo' : 'OK' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('tenant.stocks.show', $stock) }}" class="rounded-md px-3 py-1.5 text-xs font-medium text-brand-600 hover:bg-brand-50 dark:text-brand-400 dark:hover:bg-white/5">Visualizar</a>
                                        <a href="{{ route('tenant.stocks.edit', $stock) }}" class="rounded-md px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5">Editar</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Nenhum registro de estoque encontrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-100 px-4 py-3 dark:border-gray-800">
                {{ $stocks->links() }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const section = document.getElementById('section_filter');
            const category = document.getElementById('category_filter');
            const product = document.getElementById('product_filter');
            if (!section || !category || !product) return;

            const syncCategories = () => {
                const sectionId = section.value;
                [...category.options].forEach((opt, idx) => {
                    if (idx === 0) return;
                    const match = !sectionId || opt.dataset.sectionId === sectionId;
                    opt.hidden = !match;
                    opt.disabled = !match;
                });
                if (category.selectedOptions[0]?.disabled) category.value = '';
            };

            const syncProducts = () => {
                const sectionId = section.value;
                const categoryId = category.value;
                [...product.options].forEach((opt, idx) => {
                    if (idx === 0) return;
                    const sectionMatch = !sectionId || opt.dataset.sectionId === sectionId;
                    const categoryMatch = !categoryId || opt.dataset.categoryId === categoryId;
                    const match = sectionMatch && categoryMatch;
                    opt.hidden = !match;
                    opt.disabled = !match;
                });
                if (product.selectedOptions[0]?.disabled) product.value = '';
            };

            section.addEventListener('change', () => {
                syncCategories();
                syncProducts();
            });
            category.addEventListener('change', syncProducts);

            syncCategories();
            syncProducts();
        })();
    </script>
@endpush
