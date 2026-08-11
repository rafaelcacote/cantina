@php
    $wallet = $wallet ?? null;
    $inputClass = fn (string $field) => 'h-11 w-full rounded-lg border bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:text-white/90 '.($errors->has($field) ? 'border-error-500 dark:border-error-500' : 'border-gray-300 dark:border-gray-700');
@endphp

<div>
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
        Aluno <span class="text-error-500">*</span>
    </label>
    <select name="student_id" required class="{{ $inputClass('student_id') }}">
        <option value="">Selecione</option>
        @foreach ($students as $student)
            <option value="{{ $student->id }}" @selected((string) old('student_id', $wallet?->student_id) === (string) $student->id)>{{ $student->name }}</option>
        @endforeach
    </select>
    @error('student_id')
        <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
    @enderror
</div>

<div class="flex w-full flex-row gap-3">
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Saldo <span class="text-error-500">*</span>
        </label>
        <input type="number" name="balance" step="0.01" value="{{ old('balance', $wallet?->balance ?? 0) }}" required class="{{ $inputClass('balance') }}">
        @error('balance')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Limite de crédito <span class="text-error-500">*</span>
        </label>
        <input type="number" name="credit_limit" step="0.01" min="0" value="{{ old('credit_limit', $wallet?->credit_limit ?? 0) }}" required class="{{ $inputClass('credit_limit') }}">
        @error('credit_limit')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
</div>

<div>
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
        Permitir saldo negativo <span class="text-error-500">*</span>
    </label>
    <select name="allow_negative_balance" class="{{ $inputClass('allow_negative_balance') }}">
        <option value="1" @selected(old('allow_negative_balance', (string) (int) ($wallet?->allow_negative_balance ?? 0)) === '1')>Sim</option>
        <option value="0" @selected(old('allow_negative_balance', (string) (int) ($wallet?->allow_negative_balance ?? 0)) === '0')>Não</option>
    </select>
    @error('allow_negative_balance')
        <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
    @enderror
</div>
