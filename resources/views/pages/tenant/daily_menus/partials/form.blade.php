@php
    $dailyMenu = $dailyMenu ?? null;
    $inputClass = fn (string $field) => 'h-11 w-full rounded-lg border bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:text-white/90 '.($errors->has($field) ? 'border-error-500 dark:border-error-500' : 'border-gray-300 dark:border-gray-700');
@endphp

<div class="flex w-full flex-row gap-3">
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Escola <span class="text-error-500">*</span>
        </label>
        <select name="school_id" required class="{{ $inputClass('school_id') }}">
            <option value="">Selecione</option>
            @foreach ($schools as $school)
                <option value="{{ $school->id }}" @selected((string) old('school_id', $dailyMenu?->school_id) === (string) $school->id)>
                    {{ $school->name }}
                </option>
            @endforeach
        </select>
        @error('school_id')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Data do cardápio <span class="text-error-500">*</span>
        </label>
        <input type="date" name="menu_date" value="{{ old('menu_date', $dailyMenu?->menu_date?->format('Y-m-d')) }}" required class="{{ $inputClass('menu_date') }}">
        @error('menu_date')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="flex w-full flex-row gap-3">
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Título</label>
        <input type="text" name="title" value="{{ old('title', $dailyMenu?->title) }}" class="{{ $inputClass('title') }}">
        @error('title')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Status <span class="text-error-500">*</span>
        </label>
        <select name="active" class="{{ $inputClass('active') }}">
            <option value="1" @selected(old('active', (string) (int) ($dailyMenu?->active ?? 1)) === '1')>Ativo</option>
            <option value="0" @selected(old('active', (string) (int) ($dailyMenu?->active ?? 1)) === '0')>Inativo</option>
        </select>
        @error('active')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
</div>

<div>
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Descrição</label>
    <textarea name="description" rows="4"
              class="w-full rounded-lg border bg-transparent px-4 py-3 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:text-white/90 {{ $errors->has('description') ? 'border-error-500 dark:border-error-500' : 'border-gray-300 dark:border-gray-700' }}">{{ old('description', $dailyMenu?->description) }}</textarea>
    @error('description')
        <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
    @enderror
</div>
