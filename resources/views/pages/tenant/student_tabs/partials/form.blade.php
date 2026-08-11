@php
    $tab = $tab ?? null;
    $inputClass = fn (string $field) => 'h-11 w-full rounded-lg border bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:text-white/90 '.($errors->has($field) ? 'border-error-500 dark:border-error-500' : 'border-gray-300 dark:border-gray-700');
@endphp

<div>
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
        Aluno <span class="text-error-500">*</span>
    </label>
    <select name="student_id" required class="{{ $inputClass('student_id') }}">
        <option value="">Selecione</option>
        @foreach ($students as $student)
            <option value="{{ $student->id }}" @selected((string) old('student_id', $tab?->student_id) === (string) $student->id)>{{ $student->name }}</option>
        @endforeach
    </select>
    @error('student_id')
        <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
    @enderror
</div>

<div class="flex w-full flex-row gap-3">
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Saldo atual <span class="text-error-500">*</span>
        </label>
        <input type="number" name="current_balance" min="0" step="0.01" value="{{ old('current_balance', $tab?->current_balance ?? 0) }}" required class="{{ $inputClass('current_balance') }}">
        @error('current_balance')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Status <span class="text-error-500">*</span>
        </label>
        <select name="active" class="{{ $inputClass('active') }}">
            <option value="1" @selected(old('active', (string) (int) ($tab?->active ?? 1)) === '1')>Ativo</option>
            <option value="0" @selected(old('active', (string) (int) ($tab?->active ?? 1)) === '0')>Inativo</option>
        </select>
        @error('active')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="flex w-full flex-row gap-3">
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Ciclo <span class="text-error-500">*</span>
        </label>
        <select name="billing_cycle_type" required class="{{ $inputClass('billing_cycle_type') }}">
            @foreach ($cycleTypes as $key => $label)
                <option value="{{ $key }}" @selected(old('billing_cycle_type', $tab?->billing_cycle_type ?? 'monthly') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        @error('billing_cycle_type')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Dia de vencimento
        </label>
        <input type="number" name="due_day" min="1" max="31" value="{{ old('due_day', $tab?->due_day) }}" class="{{ $inputClass('due_day') }}">
        @error('due_day')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
</div>
