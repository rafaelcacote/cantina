@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="flex items-center gap-2.5 text-2xl font-semibold text-gray-800 dark:text-white/90">
                    <span class="inline-flex size-8 items-center justify-center text-brand-500 dark:text-brand-400">
                        {!! \App\Helpers\MenuHelper::getIconSvg('forms') !!}
                    </span>
                    Novo Produto
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Cadastre um produto para o seu tenant.</p>
            </div>
            <a href="{{ route('tenant.products.index') }}"
               class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-transparent dark:text-gray-300 dark:hover:bg-white/5">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Voltar
            </a>
        </div>

        @if ($errors->any())
            <div class="rounded-xl border border-error-500 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/15 dark:text-error-400">
                Verifique os campos obrigatórios destacados abaixo.
            </div>
        @endif

        <form method="POST" action="{{ route('tenant.products.store') }}" enctype="multipart/form-data" class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]" novalidate>
            @csrf
            <div class="space-y-5 p-6">
                @include('pages.tenant.products.partials.form', [
                    'product' => null,
                    'sections' => $sections,
                    'categories' => $categories,
                    'productTypes' => $productTypes,
                    'saleTypes' => $saleTypes,
                ])
            </div>

            <div class="flex flex-col-reverse gap-2 border-t border-gray-200 bg-gray-50 px-6 py-4 sm:flex-row sm:items-center sm:justify-end dark:border-gray-800 dark:bg-white/[0.02]">
                <a href="{{ route('tenant.products.index') }}"
                   class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-transparent dark:text-gray-300 dark:hover:bg-white/5">
                    Cancelar
                </a>
                <button type="submit"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 text-sm font-medium text-white transition-colors hover:bg-brand-600">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    Salvar Produto
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const section = document.getElementById('section_id');
            const category = document.getElementById('category_id');
            if (!section || !category) return;

            const syncCategories = () => {
                const sectionId = section.value;
                [...category.options].forEach((opt, idx) => {
                    if (idx === 0) return;
                    const match = !!sectionId && opt.dataset.sectionId === sectionId;
                    opt.hidden = !match;
                    opt.disabled = !match;
                });
                if (!sectionId || category.selectedOptions[0]?.disabled) category.value = '';
            };

            section.addEventListener('change', syncCategories);
            syncCategories();
        })();

        (function () {
            const nameInput = document.getElementById('product-name');
            const skuInput = document.getElementById('product-sku');
            if (!nameInput || !skuInput) return;

            const stopWords = new Set(['DE', 'DA', 'DO', 'DAS', 'DOS', 'E', 'EM', 'NA', 'NO', 'NAS', 'NOS', 'A', 'O', 'AS', 'OS', 'UM', 'UMA', 'COM', 'PARA', 'POR']);

            const suggestSku = (value) => {
                const words = value
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .toUpperCase()
                    .replace(/[^A-Z0-9]+/g, ' ')
                    .trim()
                    .split(/\s+/)
                    .filter(Boolean);

                const meaningful = words.filter((word) => !stopWords.has(word));
                const parts = meaningful.length ? meaningful : words;

                return parts.join('-').slice(0, 100);
            };

            let locked = skuInput.value !== '' && skuInput.value !== suggestSku(nameInput.value);

            const syncSku = () => {
                if (locked) return;
                skuInput.value = suggestSku(nameInput.value);
            };

            nameInput.addEventListener('input', syncSku);
            skuInput.addEventListener('input', () => {
                locked = skuInput.value.trim() !== '' && skuInput.value !== suggestSku(nameInput.value);
            });

            if (!locked && nameInput.value && !skuInput.value) {
                syncSku();
            }
        })();
    </script>
@endpush
