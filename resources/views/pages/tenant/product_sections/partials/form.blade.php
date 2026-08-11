@php
    $section = $section ?? null;
@endphp

<div class="flex w-full flex-row gap-3">
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Nome <span class="text-error-500">*</span>
        </label>
        <input type="text" name="name" value="{{ old('name', $section?->name) }}" required
               class="h-11 w-full rounded-lg border bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:text-white/90 {{ $errors->has('name') ? 'border-error-500 dark:border-error-500' : 'border-gray-300 dark:border-gray-700' }}">
        @error('name')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Slug</label>
        <input type="text" name="slug" value="{{ old('slug', $section?->slug) }}"
               placeholder="ex.: lanches"
               class="h-11 w-full rounded-lg border bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:text-white/90 {{ $errors->has('slug') ? 'border-error-500 dark:border-error-500' : 'border-gray-300 dark:border-gray-700' }}">
        @error('slug')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
</div>

<div>
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Descrição</label>
    <textarea name="description" rows="4"
              class="w-full rounded-lg border bg-transparent px-4 py-3 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:text-white/90 {{ $errors->has('description') ? 'border-error-500 dark:border-error-500' : 'border-gray-300 dark:border-gray-700' }}">{{ old('description', $section?->description) }}</textarea>
    @error('description')
        <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
    @enderror
</div>

<div>
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
        Status <span class="text-error-500">*</span>
    </label>
    <select name="active"
            class="h-11 w-full rounded-lg border bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:text-white/90 {{ $errors->has('active') ? 'border-error-500 dark:border-error-500' : 'border-gray-300 dark:border-gray-700' }}">
        <option value="1" @selected(old('active', (string) (int) ($section?->active ?? 1)) === '1')>Ativo</option>
        <option value="0" @selected(old('active', (string) (int) ($section?->active ?? 1)) === '0')>Inativo</option>
    </select>
    @error('active')
        <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
    @enderror
</div>
