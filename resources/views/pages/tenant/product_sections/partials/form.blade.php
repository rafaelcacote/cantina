@php
    $section = $section ?? null;
@endphp

<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    <div>
        <label for="name" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nome *</label>
        <input id="name" name="name" type="text" value="{{ old('name', $section?->name) }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
        @error('name') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="slug" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Slug</label>
        <input id="slug" name="slug" type="text" value="{{ old('slug', $section?->slug) }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
        @error('slug') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div class="md:col-span-2">
        <label for="description" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Descrição</label>
        <textarea id="description" name="description" rows="4" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">{{ old('description', $section?->description) }}</textarea>
        @error('description') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
    </div>
</div>

<div class="mt-6">
    <label class="inline-flex items-center gap-3 rounded-lg border border-gray-200 px-4 py-3 dark:border-gray-800">
        <input type="checkbox" name="active" value="1" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" @checked(old('active', $section?->active ?? true))>
        <span class="text-sm text-gray-700 dark:text-gray-300">Seção ativa</span>
    </label>
</div>
