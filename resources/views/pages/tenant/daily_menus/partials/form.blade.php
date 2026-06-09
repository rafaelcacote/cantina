@php
    $dailyMenu = $dailyMenu ?? null;
@endphp

<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    <div>
        <label for="school_id" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Escola *</label>
        <select id="school_id" name="school_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            <option value="">Selecione</option>
            @foreach ($schools as $school)
                <option value="{{ $school->id }}" @selected(old('school_id', $dailyMenu?->school_id) == $school->id)>
                    {{ $school->name }}
                </option>
            @endforeach
        </select>
        @error('school_id') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="menu_date" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Data do cardápio *</label>
        <input id="menu_date" name="menu_date" type="date" value="{{ old('menu_date', $dailyMenu?->menu_date?->format('Y-m-d')) }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
        @error('menu_date') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="title" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Título</label>
        <input id="title" name="title" type="text" value="{{ old('title', $dailyMenu?->title) }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
        @error('title') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div class="md:col-span-2">
        <label for="description" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Descrição</label>
        <textarea id="description" name="description" rows="4" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">{{ old('description', $dailyMenu?->description) }}</textarea>
        @error('description') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
    </div>
</div>

<div class="mt-6">
    <label class="inline-flex items-center gap-3 rounded-lg border border-gray-200 px-4 py-3 dark:border-gray-800">
        <input type="checkbox" name="active" value="1" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500"
            @checked(old('active', $dailyMenu?->active ?? true))>
        <span class="text-sm text-gray-700 dark:text-gray-300">Cardápio ativo</span>
    </label>
</div>
