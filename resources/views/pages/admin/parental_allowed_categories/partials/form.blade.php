@php($item = $item ?? null)
<div class="grid grid-cols-1 gap-6 md:grid-cols-3">
    <div>
        <label for="tenant_id" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tenant *</label>
        <select id="tenant_id" name="tenant_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            <option value="">Selecione</option>
            @foreach ($tenants as $tenant)
                <option value="{{ $tenant->id }}" @selected(old('tenant_id', $item?->tenant_id) == $tenant->id)>{{ $tenant->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="parental_control_id" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Controle Parental *</label>
        <select id="parental_control_id" name="parental_control_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            <option value="">Selecione</option>
            @foreach ($controls as $control)
                <option value="{{ $control->id }}" data-tenant-id="{{ $control->tenant_id }}" @selected(old('parental_control_id', $item?->parental_control_id) == $control->id)>#{{ $control->id }} - {{ $control->student?->name ?? 'Aluno' }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="category_id" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Categoria *</label>
        <select id="category_id" name="category_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            <option value="">Selecione</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" data-tenant-id="{{ $category->tenant_id }}" @selected(old('category_id', $item?->category_id) == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
    </div>
</div>
