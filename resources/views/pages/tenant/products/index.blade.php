@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="flex items-center gap-2.5 text-2xl font-semibold text-gray-800 dark:text-white/90">
                    <span class="inline-flex size-8 items-center justify-center text-brand-500 dark:text-brand-400">
                        {!! \App\Helpers\MenuHelper::getIconSvg('forms') !!}
                    </span>
                    Produtos
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Gerencie os produtos do seu tenant.</p>
            </div>
            <a href="{{ route('tenant.products.create') }}"
               class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Novo Produto
            </a>
        </div>

        @if ($errors->any())
            <div class="rounded-xl border border-error-500 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/15 dark:text-error-400">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <form method="GET" action="{{ route('tenant.products.index') }}" class="mb-4 flex w-full flex-row gap-3">
                <input type="text"
                       name="search"
                       value="{{ $search }}"
                       placeholder="Buscar por nome ou SKU..."
                       class="h-11 min-w-0 flex-[2] basis-0 rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">

                <select id="section_filter" name="section_id"
                        class="h-11 min-w-0 flex-1 basis-0 rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
                    <option value="">Todas as seções</option>
                    @foreach($sections as $section)
                        <option value="{{ $section->id }}" @selected((int) $sectionId === (int) $section->id)>{{ $section->name }}</option>
                    @endforeach
                </select>

                <select id="category_filter" name="category_id"
                        class="h-11 min-w-0 flex-1 basis-0 rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
                    <option value="">Todas as categorias</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" data-section-id="{{ $category->section_id }}" @selected((int) $categoryId === (int) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>

                <button type="submit"
                        class="inline-flex h-11 shrink-0 items-center justify-center rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
                    Filtrar
                </button>
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead>
                    <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        <th class="px-4 py-3">Produto</th>
                        <th class="px-4 py-3">Seção</th>
                        <th class="hidden px-4 py-3 lg:table-cell">Categoria</th>
                        <th class="px-4 py-3">Preço</th>
                        <th class="hidden px-4 py-3 sm:table-cell">Status</th>
                        <th class="hidden px-4 py-3 md:table-cell">App</th>
                        <th class="px-4 py-3 text-right">Ações</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($products as $product)
                        <tr class="transition-colors hover:bg-gray-50/80 dark:hover:bg-white/[0.02]">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="relative size-12 shrink-0 overflow-hidden rounded-xl border border-gray-200 bg-gray-50 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                                        @if ($product->imageSrc())
                                            <img src="{{ $product->imageSrc() }}"
                                                 alt=""
                                                 loading="lazy"
                                                 class="size-full object-cover">
                                        @else
                                            <div class="flex size-full items-center justify-center text-gray-400 dark:text-gray-500">
                                                <svg class="size-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                    <path d="M4 7.5A2.5 2.5 0 016.5 5h11A2.5 2.5 0 0120 7.5v9a2.5 2.5 0 01-2.5 2.5h-11A2.5 2.5 0 014 16.5v-9z" stroke="currentColor" stroke-width="1.5"/>
                                                    <path d="M8.5 13.5l2.2-2.2a1 1 0 011.4 0L15 14.2l1.3-1.3a1 1 0 011.4 0l1.8 1.8M9 9.5h.01" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </div>
                                        @endif
                                        @unless ($product->active)
                                            <span class="absolute inset-0 bg-gray-900/35" aria-hidden="true"></span>
                                        @endunless
                                    </div>
                                    <div class="min-w-0">
                                        <a href="{{ route('tenant.products.show', $product) }}"
                                           class="block truncate text-sm font-medium text-gray-800 transition-colors hover:text-brand-600 dark:text-white/90 dark:hover:text-brand-400">
                                            {{ $product->name }}
                                        </a>
                                        <div class="mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-0.5 text-xs text-gray-500 dark:text-gray-400">
                                            @if ($product->sku)
                                                <span class="truncate">{{ $product->sku }}</span>
                                            @endif
                                            <span class="lg:hidden">{{ $product->category?->name ?? 'Sem categoria' }}</span>
                                            <span class="sm:hidden">
                                                <span class="inline-flex rounded-full px-2 py-0.5 text-[11px] font-medium {{ $product->active ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300' }}">
                                                    {{ $product->active ? 'Ativo' : 'Inativo' }}
                                                </span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $product->section?->name ?? '-' }}</td>
                            <td class="hidden px-4 py-3 text-sm text-gray-600 dark:text-gray-300 lg:table-cell">{{ $product->category?->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm font-medium tabular-nums text-gray-800 dark:text-white/90">R$ {{ number_format((float) $product->price, 2, ',', '.') }}</td>
                            <td class="hidden px-4 py-3 sm:table-cell">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $product->active ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                                    {{ $product->active ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td class="hidden px-4 py-3 md:table-cell">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $product->visible_in_app ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                                    {{ $product->visible_in_app ? 'Sim' : 'Não' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('tenant.products.show', $product) }}"
                                       title="Visualizar"
                                       class="inline-flex size-10 items-center justify-center rounded-lg text-brand-500 transition-colors hover:bg-brand-50 hover:text-brand-700 dark:text-brand-400 dark:hover:bg-white/5 dark:hover:text-brand-300">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <path d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12s-3.75 6.75-9.75 6.75S2.25 12 2.25 12Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <circle cx="12" cy="12" r="2.75" stroke="currentColor" stroke-width="1.5"/>
                                        </svg>
                                        <span class="sr-only">Visualizar</span>
                                    </a>
                                    <a href="{{ route('tenant.products.edit', $product) }}"
                                       title="Editar"
                                       class="inline-flex size-10 items-center justify-center rounded-lg text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <path d="M4 20h4l10.5-10.5a2.121 2.121 0 0 0-3-3L5 17v3Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M13.5 6.5l3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        <span class="sr-only">Editar</span>
                                    </a>
                                    <a href="{{ route('tenant.products.duplicate', $product) }}"
                                       title="Duplicar"
                                       class="inline-flex size-10 items-center justify-center rounded-lg text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <rect x="9" y="9" width="11" height="11" rx="2" stroke="currentColor" stroke-width="1.5"/>
                                            <path d="M5 15V6a2 2 0 0 1 2-2h9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        <span class="sr-only">Duplicar</span>
                                    </a>
                                    <button type="button"
                                            title="Excluir"
                                            data-name="{{ $product->name }}"
                                            data-action="{{ route('tenant.products.destroy', $product) }}"
                                            @click="$dispatch('open-confirm-delete', {
                                                name: $el.dataset.name,
                                                action: $el.dataset.action,
                                                title: 'Excluir produto?'
                                            })"
                                            class="inline-flex size-10 items-center justify-center rounded-lg text-gray-500 transition-colors hover:bg-error-50 hover:text-error-600 dark:text-gray-400 dark:hover:bg-error-500/10 dark:hover:text-error-400">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <path d="M3 6h18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                            <path d="M8 6V4.5A1.5 1.5 0 0 1 9.5 3h5A1.5 1.5 0 0 1 16 4.5V6M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M10 11v6M14 11v6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                        </svg>
                                        <span class="sr-only">Excluir</span>
                                    </button>
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
