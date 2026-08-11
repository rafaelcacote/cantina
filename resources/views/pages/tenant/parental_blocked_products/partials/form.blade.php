@php($item = $item ?? null)
<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    <div>
        <label for="parental_control_id" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Controle Parental *</label>
        <select id="parental_control_id" name="parental_control_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            <option value="">Selecione</option>
            @foreach ($controls as $control)
                <option value="{{ $control->id }}" @selected(old('parental_control_id', $item?->parental_control_id) == $control->id)>#{{ $control->id }} - {{ $control->student?->name ?? 'Aluno' }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="product_id" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Produto *</label>
        <select id="product_id" name="product_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            <option value="">Selecione</option>
            @foreach ($products as $product)
                <option value="{{ $product->id }}" @selected(old('product_id', $item?->product_id) == $product->id)>{{ $product->name }}</option>
            @endforeach
        </select>
    </div>
</div>
