@php
    $product = $product ?? null;
@endphp

<div class="grid grid-cols-1 gap-6 md:grid-cols-3">
    <div>
        <label for="tenant_id" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tenant *</label>
        <select id="tenant_id" name="tenant_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            <option value="">Selecione</option>
            @foreach ($tenants as $tenant)
                <option value="{{ $tenant->id }}" @selected(old('tenant_id', $product?->tenant_id) == $tenant->id)>
                    {{ $tenant->name }}
                </option>
            @endforeach
        </select>
        @error('tenant_id') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="section_id" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Seção *</label>
        <select id="section_id" name="section_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            <option value="">Selecione</option>
            @foreach ($sections as $section)
                <option value="{{ $section->id }}" data-tenant-id="{{ $section->tenant_id }}" @selected(old('section_id', $product?->section_id) == $section->id)>
                    {{ $section->name }}
                </option>
            @endforeach
        </select>
        @error('section_id') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="category_id" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Categoria *</label>
        <select id="category_id" name="category_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            <option value="">Selecione</option>
            @foreach ($categories as $category)
                <option
                    value="{{ $category->id }}"
                    data-tenant-id="{{ $category->tenant_id }}"
                    data-section-id="{{ $category->section_id }}"
                    @selected(old('category_id', $product?->category_id) == $category->id)
                >
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        @error('category_id') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div class="md:col-span-2">
        <label for="name" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nome *</label>
        <input id="name" name="name" type="text" value="{{ old('name', $product?->name) }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
        @error('name') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="sku" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">SKU</label>
        <input id="sku" name="sku" type="text" value="{{ old('sku', $product?->sku) }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
        @error('sku') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div class="md:col-span-3">
        <label for="description" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Descrição</label>
        <textarea id="description" name="description" rows="3" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">{{ old('description', $product?->description) }}</textarea>
        @error('description') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="barcode" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Código de barras</label>
        <input id="barcode" name="barcode" type="text" value="{{ old('barcode', $product?->barcode) }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
        @error('barcode') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="product_type" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tipo do produto *</label>
        <select id="product_type" name="product_type" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            @foreach ($productTypes as $key => $label)
                <option value="{{ $key }}" @selected(old('product_type', $product?->product_type ?? 'resale') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        @error('product_type') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="sale_type" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tipo de venda *</label>
        <select id="sale_type" name="sale_type" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            @foreach ($saleTypes as $key => $label)
                <option value="{{ $key }}" @selected(old('sale_type', $product?->sale_type ?? 'unit') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        @error('sale_type') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="price" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Preço *</label>
        <input id="price" name="price" type="number" step="0.01" min="0" value="{{ old('price', $product?->price) }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
        @error('price') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="cost_price" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Custo</label>
        <input id="cost_price" name="cost_price" type="number" step="0.01" min="0" value="{{ old('cost_price', $product?->cost_price) }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
        @error('cost_price') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="minimum_stock_alert" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Alerta mínimo de estoque *</label>
        <input id="minimum_stock_alert" name="minimum_stock_alert" type="number" min="0" value="{{ old('minimum_stock_alert', $product?->minimum_stock_alert ?? 5) }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
        @error('minimum_stock_alert') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div class="md:col-span-3">
        <label for="image_url" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">URL da imagem</label>
        <input id="image_url" name="image_url" type="text" value="{{ old('image_url', $product?->image_url) }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
        @error('image_url') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
    </div>
</div>

<div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
    @php
        $checkboxes = [
            'active' => ['label' => 'Ativo', 'default' => true],
            'visible_in_app' => ['label' => 'Visível no app', 'default' => true],
            'allow_custom_request' => ['label' => 'Permite pedido customizado', 'default' => false],
            'requires_preparation' => ['label' => 'Exige preparo', 'default' => false],
            'stock_controlled' => ['label' => 'Controla estoque', 'default' => true],
        ];
    @endphp

    @foreach ($checkboxes as $field => $meta)
        <label class="inline-flex items-center gap-3 rounded-lg border border-gray-200 px-4 py-3 dark:border-gray-800">
            <input type="checkbox" name="{{ $field }}" value="1" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500"
                @checked(old($field, $product?->{$field} ?? $meta['default']))>
            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $meta['label'] }}</span>
        </label>
    @endforeach
</div>
