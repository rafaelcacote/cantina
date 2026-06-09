@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Produtos</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Gerencie os produtos do seu tenant.</p>
            </div>
            <a href="{{ route('tenant.products.create') }}"
               class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                Novo Produto
            </a>
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-900/40 dark:bg-green-900/20 dark:text-green-300">
                {{ session('success') }}
            </div>
        @endif

        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <form method="GET" action="{{ route('tenant.products.index') }}" class="mb-4 grid grid-cols-1 gap-3 lg:grid-cols-4">
                <input type="text"
                       name="search"
                       value="{{ $search }}"
                       placeholder="Buscar por nome ou SKU..."
                       class="h-11 rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90 lg:col-span-2">

                <select id="section_filter" name="section_id"
                        class="h-11 rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
                    <option value="">Todas as seções</option>
                    @foreach($sections as $section)
                        <option value="{{ $section->id }}" @selected((int) $sectionId === (int) $section->id)>{{ $section->name }}</option>
                    @endforeach
                </select>

                <select id="category_filter" name="category_id"
                        class="h-11 rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
                    <option value="">Todas as categorias</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" data-section-id="{{ $category->section_id }}" @selected((int) $categoryId === (int) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>

                <div class="lg:col-span-4 flex justify-end gap-2">
                    <button type="submit"
                            class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
                        Filtrar
                    </button>
                    <a href="{{ route('tenant.products.index') }}"
                       class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
                        Limpar
                    </a>
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead>
                    <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        <th class="px-4 py-3">Nome</th>
                        <th class="px-4 py-3">Seção</th>
                        <th class="px-4 py-3">Categoria</th>
                        <th class="px-4 py-3">Preço</th>
                        <th class="px-4 py-3">Ativo</th>
                        <th class="px-4 py-3">Visível no app</th>
                        <th class="px-4 py-3 text-right">Ações</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($products as $product)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-800 dark:text-white/90">{{ $product->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $product->section?->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $product->category?->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">R$ {{ number_format((float) $product->price, 2, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $product->active ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' }}">
                                    {{ $product->active ? 'Sim' : 'Não' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $product->visible_in_app ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                                    {{ $product->visible_in_app ? 'Sim' : 'Não' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('tenant.products.show', $product) }}" class="rounded-md px-3 py-1.5 text-xs font-medium text-brand-600 hover:bg-brand-50 dark:text-brand-400 dark:hover:bg-white/5">Visualizar</a>
                                    <a href="{{ route('tenant.products.edit', $product) }}" class="rounded-md px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5">Editar</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                Nenhum produto encontrado.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $products->links() }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const section = document.getElementById('section_filter');
            const category = document.getElementById('category_filter');
            if (!section || !category) return;

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

            section.addEventListener('change', syncCategories);
            syncCategories();
        })();
    </script>
@endpush
