@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-5xl space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Editar Produto</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Atualize os dados do produto.</p>
            </div>
            <a href="{{ route('tenant.products.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">
                Voltar
            </a>
        </div>

        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-300">
                Verifique os campos do formulário.
            </div>
        @endif

        <form method="POST" action="{{ route('tenant.products.update', $product) }}" class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            @csrf
            @method('PUT')
            @include('pages.tenant.products.partials.form', [
                'product' => $product,
                'sections' => $sections,
                'categories' => $categories,
                'productTypes' => $productTypes,
                'saleTypes' => $saleTypes,
            ])

            <div class="mt-8 flex justify-end gap-3">
                <a href="{{ route('tenant.products.show', $product) }}" class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
                    Cancelar
                </a>
                <button type="submit" class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                    Atualizar Produto
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
    </script>
@endpush
