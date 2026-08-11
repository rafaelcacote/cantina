@php
    $product = $product ?? null;

    $boolValue = function (string $field, bool $default) use ($product): string {
        return (string) (int) old($field, $product?->{$field} ?? $default);
    };

    $inputClass = fn (string $field) => 'h-11 w-full rounded-lg border bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:text-white/90 '.($errors->has($field) ? 'border-error-500 dark:border-error-500' : 'border-gray-300 dark:border-gray-700');
@endphp

<div class="flex w-full flex-row gap-3">
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Seção <span class="text-error-500">*</span>
        </label>
        <select id="section_id" name="section_id" required class="{{ $inputClass('section_id') }}">
            <option value="">Selecione</option>
            @foreach ($sections as $section)
                <option value="{{ $section->id }}" @selected((string) old('section_id', $product?->section_id) === (string) $section->id)>
                    {{ $section->name }}
                </option>
            @endforeach
        </select>
        @error('section_id')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Categoria <span class="text-error-500">*</span>
        </label>
        <select id="category_id" name="category_id" required class="{{ $inputClass('category_id') }}">
            <option value="">Selecione</option>
            @foreach ($categories as $category)
                <option
                    value="{{ $category->id }}"
                    data-section-id="{{ $category->section_id }}"
                    @selected((string) old('category_id', $product?->category_id) === (string) $category->id)
                >
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        @error('category_id')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
</div>

<div>
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
        Nome <span class="text-error-500">*</span>
    </label>
    <input type="text" id="product-name" name="name" value="{{ old('name', $product?->name) }}" required class="{{ $inputClass('name') }}">
    @error('name')
        <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
    @enderror
</div>

<div class="flex w-full flex-row gap-3">
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">SKU</label>
        <input type="text" id="product-sku" name="sku" value="{{ old('sku', $product?->sku) }}" class="{{ $inputClass('sku') }}" autocomplete="off">
        @if (! $product)
            <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Sugerido a partir do nome. Você pode alterar.</p>
        @endif
        @error('sku')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Código de barras</label>
        <input type="text" name="barcode" value="{{ old('barcode', $product?->barcode) }}" class="{{ $inputClass('barcode') }}">
        @error('barcode')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
</div>

<div>
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Descrição</label>
    <textarea name="description" rows="3"
              class="w-full rounded-lg border bg-transparent px-4 py-3 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:text-white/90 {{ $errors->has('description') ? 'border-error-500 dark:border-error-500' : 'border-gray-300 dark:border-gray-700' }}">{{ old('description', $product?->description) }}</textarea>
    @error('description')
        <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
    @enderror
</div>

<div class="flex w-full flex-row gap-3">
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Tipo do produto <span class="text-error-500">*</span>
        </label>
        <select name="product_type" class="{{ $inputClass('product_type') }}">
            @foreach ($productTypes as $key => $label)
                <option value="{{ $key }}" @selected(old('product_type', $product?->product_type ?? 'resale') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        @error('product_type')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Tipo de venda <span class="text-error-500">*</span>
        </label>
        <select name="sale_type" class="{{ $inputClass('sale_type') }}">
            @foreach ($saleTypes as $key => $label)
                <option value="{{ $key }}" @selected(old('sale_type', $product?->sale_type ?? 'unit') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        @error('sale_type')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="flex w-full flex-row gap-3">
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Preço <span class="text-error-500">*</span>
        </label>
        <input type="number" name="price" step="0.01" min="0" value="{{ old('price', $product?->price) }}" required class="{{ $inputClass('price') }}">
        @error('price')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Custo</label>
        <input type="number" name="cost_price" step="0.01" min="0" value="{{ old('cost_price', $product?->cost_price) }}" class="{{ $inputClass('cost_price') }}">
        @error('cost_price')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Alerta mínimo de estoque <span class="text-error-500">*</span>
        </label>
        <input type="number" name="minimum_stock_alert" min="0" value="{{ old('minimum_stock_alert', $product?->minimum_stock_alert ?? 5) }}" required class="{{ $inputClass('minimum_stock_alert') }}">
        @error('minimum_stock_alert')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
</div>

<div>
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Imagem</label>
    <div class="flex items-center gap-3">
        <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800">
            @if ($product?->imageSrc())
                <img id="product-image-preview" src="{{ $product->imageSrc() }}" alt="Imagem do produto" class="h-full w-full object-cover">
            @else
                <img id="product-image-preview" src="" alt="" class="hidden h-full w-full object-cover">
                <svg id="product-image-placeholder" class="h-7 w-7 text-gray-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M4 7.5A2.5 2.5 0 016.5 5h11A2.5 2.5 0 0120 7.5v9a2.5 2.5 0 01-2.5 2.5h-11A2.5 2.5 0 014 16.5v-9z" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M8.5 13.5l2.2-2.2a1 1 0 011.4 0L15 14.2l1.3-1.3a1 1 0 011.4 0l1.8 1.8M9 9.5h.01" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            @endif
        </div>
        <div class="min-w-0 flex-1">
            <input type="file"
                   name="image"
                   id="product-image-input"
                   accept="image/jpeg,image/png,image/webp,image/gif"
                   class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-brand-600 hover:file:bg-brand-100 dark:text-gray-300 dark:file:bg-brand-500/15 dark:file:text-brand-400 {{ $errors->has('image') ? 'rounded-lg border border-error-500 p-1' : '' }}">
            <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">JPG, PNG, WEBP ou GIF até 2 MB.</p>
            @error('image')
                <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const input = document.getElementById('product-image-input');
                const preview = document.getElementById('product-image-preview');
                const placeholder = document.getElementById('product-image-placeholder');

                if (!input || !preview) {
                    return;
                }

                input.addEventListener('change', () => {
                    const file = input.files?.[0];
                    if (!file) {
                        return;
                    }

                    const url = URL.createObjectURL(file);
                    preview.src = url;
                    preview.classList.remove('hidden');
                    placeholder?.classList.add('hidden');
                });
            });
        </script>
    @endpush
@endonce

<div class="flex w-full flex-row gap-3">
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Status <span class="text-error-500">*</span>
        </label>
        <select name="active" class="{{ $inputClass('active') }}">
            <option value="1" @selected($boolValue('active', true) === '1')>Ativo</option>
            <option value="0" @selected($boolValue('active', true) === '0')>Inativo</option>
        </select>
        @error('active')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Visível no app <span class="text-error-500">*</span>
        </label>
        <select name="visible_in_app" class="{{ $inputClass('visible_in_app') }}">
            <option value="1" @selected($boolValue('visible_in_app', true) === '1')>Sim</option>
            <option value="0" @selected($boolValue('visible_in_app', true) === '0')>Não</option>
        </select>
        @error('visible_in_app')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Controla estoque <span class="text-error-500">*</span>
        </label>
        <select name="stock_controlled" class="{{ $inputClass('stock_controlled') }}">
            <option value="1" @selected($boolValue('stock_controlled', true) === '1')>Sim</option>
            <option value="0" @selected($boolValue('stock_controlled', true) === '0')>Não</option>
        </select>
        @error('stock_controlled')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="flex w-full flex-row gap-3">
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Pedido customizado <span class="text-error-500">*</span>
        </label>
        <select name="allow_custom_request" class="{{ $inputClass('allow_custom_request') }}">
            <option value="1" @selected($boolValue('allow_custom_request', false) === '1')>Sim</option>
            <option value="0" @selected($boolValue('allow_custom_request', false) === '0')>Não</option>
        </select>
        @error('allow_custom_request')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Exige preparo <span class="text-error-500">*</span>
        </label>
        <select name="requires_preparation" class="{{ $inputClass('requires_preparation') }}">
            <option value="1" @selected($boolValue('requires_preparation', false) === '1')>Sim</option>
            <option value="0" @selected($boolValue('requires_preparation', false) === '0')>Não</option>
        </select>
        @error('requires_preparation')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
</div>
