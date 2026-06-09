@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">Produtos</h1>
            <a href="{{ route('admin.products.create') }}" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">
                Novo Produto
            </a>
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800/40 dark:bg-green-900/20 dark:text-green-300">
                {{ session('success') }}
            </div>
        @endif

        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <form method="GET" action="{{ route('admin.products.index') }}" class="grid grid-cols-1 gap-3 lg:grid-cols-5">
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Buscar por nome ou SKU"
                    class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white lg:col-span-2"
                >

                <select id="tenant_filter" name="tenant_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="">Todos os tenants</option>
                    @foreach ($tenants as $tenant)
                        <option value="{{ $tenant->id }}" @selected($tenantId === (int) $tenant->id)>{{ $tenant->name }}</option>
                    @endforeach
                </select>

                <select id="section_filter" name="section_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="">Todas as seções</option>
                    @foreach ($sections as $section)
                        <option value="{{ $section->id }}" data-tenant-id="{{ $section->tenant_id }}" @selected($sectionId === (int) $section->id)>{{ $section->name }}</option>
                    @endforeach
                </select>

                <select id="category_filter" name="category_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="">Todas as categorias</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" data-tenant-id="{{ $category->tenant_id }}" data-section-id="{{ $category->section_id }}" @selected($categoryId === (int) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>

                <div class="lg:col-span-5 flex gap-2">
                    <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Filtrar</button>
                    <a href="{{ route('admin.products.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                        Limpar
                    </a>
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Nome</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Tenant</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Categoria</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Seção</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Preço</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Ativo</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Visível no app</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($products as $product)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $product->name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $tenantNames[$product->tenant_id] ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $product->category?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $product->section?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">R$ {{ number_format((float) $product->price, 2, ',', '.') }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $product->active ? 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-300' }}">
                                        {{ $product->active ? 'Sim' : 'Não' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $product->visible_in_app ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300' : 'bg-gray-200 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                                        {{ $product->visible_in_app ? 'Sim' : 'Não' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <div class="flex gap-2">
                                        <a href="{{ route('admin.products.show', $product) }}" class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Visualizar</a>
                                        <a href="{{ route('admin.products.edit', $product) }}" class="rounded-md bg-brand-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-brand-600">Editar</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Nenhum produto encontrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-100 px-4 py-3 dark:border-gray-800">
                {{ $products->links() }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const tenant = document.getElementById('tenant_filter');
            const section = document.getElementById('section_filter');
            const category = document.getElementById('category_filter');
            if (!tenant || !section || !category) return;

            const syncSections = () => {
                const tenantId = tenant.value;
                [...section.options].forEach((opt, idx) => {
                    if (idx === 0) return;
                    const match = !tenantId || opt.dataset.tenantId === tenantId;
                    opt.hidden = !match;
                    opt.disabled = !match;
                });
                if (section.selectedOptions[0]?.disabled) section.value = '';
            };

            const syncCategories = () => {
                const tenantId = tenant.value;
                const sectionId = section.value;
                [...category.options].forEach((opt, idx) => {
                    if (idx === 0) return;
                    const tenantMatch = !tenantId || opt.dataset.tenantId === tenantId;
                    const sectionMatch = !sectionId || opt.dataset.sectionId === sectionId;
                    const match = tenantMatch && sectionMatch;
                    opt.hidden = !match;
                    opt.disabled = !match;
                });
                if (category.selectedOptions[0]?.disabled) category.value = '';
            };

            tenant.addEventListener('change', () => {
                syncSections();
                syncCategories();
            });
            section.addEventListener('change', syncCategories);

            syncSections();
            syncCategories();
        })();
    </script>
@endpush
