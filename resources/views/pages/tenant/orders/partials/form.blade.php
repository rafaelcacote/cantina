@php
    $order = $order ?? null;
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
                <option value="{{ $school->id }}" @selected((string) old('school_id', $order?->school_id) === (string) $school->id)>{{ $school->name }}</option>
            @endforeach
        </select>
        @error('school_id')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Usuário responsável</label>
        <select name="placed_by_user_id" class="{{ $inputClass('placed_by_user_id') }}">
            <option value="">Selecione</option>
            @foreach ($users as $user)
                <option value="{{ $user->id }}" @selected((string) old('placed_by_user_id', $order?->placed_by_user_id ?? auth()->id()) === (string) $user->id)>
                    {{ $user->name }} ({{ $user->email }})
                </option>
            @endforeach
        </select>
        @error('placed_by_user_id')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="flex w-full flex-row gap-3">
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Aluno</label>
        <select name="student_id" class="{{ $inputClass('student_id') }}">
            <option value="">Selecione</option>
            @foreach ($students as $student)
                <option value="{{ $student->id }}" @selected((string) old('student_id', $order?->student_id) === (string) $student->id)>{{ $student->name }}</option>
            @endforeach
        </select>
        @error('student_id')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Responsável</label>
        <select name="parent_id" class="{{ $inputClass('parent_id') }}">
            <option value="">Selecione</option>
            @foreach ($parents as $parent)
                <option value="{{ $parent->id }}" @selected((string) old('parent_id', $order?->parent_id) === (string) $parent->id)>{{ $parent->name }}</option>
            @endforeach
        </select>
        @error('parent_id')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Agendado para</label>
        <input type="datetime-local" name="scheduled_for" value="{{ old('scheduled_for', $order?->scheduled_for?->format('Y-m-d\TH:i')) }}" class="{{ $inputClass('scheduled_for') }}">
        @error('scheduled_for')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="flex w-full flex-row gap-3">
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Canal <span class="text-error-500">*</span>
        </label>
        <select name="order_channel" class="{{ $inputClass('order_channel') }}">
            @foreach ($channels as $key => $label)
                <option value="{{ $key }}" @selected(old('order_channel', $order?->order_channel ?? 'cashier') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        @error('order_channel')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Tipo <span class="text-error-500">*</span>
        </label>
        <select name="order_type" class="{{ $inputClass('order_type') }}">
            @foreach ($types as $key => $label)
                <option value="{{ $key }}" @selected(old('order_type', $order?->order_type ?? 'immediate') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        @error('order_type')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Status <span class="text-error-500">*</span>
        </label>
        <select name="status" class="{{ $inputClass('status') }}">
            @foreach ($statuses as $key => $label)
                <option value="{{ $key }}" @selected(old('status', $order?->status ?? 'pending') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Pagamento</label>
        <select name="payment_mode" class="{{ $inputClass('payment_mode') }}">
            <option value="">Selecione</option>
            @foreach ($paymentModes as $key => $label)
                <option value="{{ $key }}" @selected(old('payment_mode', $order?->payment_mode) === $key)>{{ $label }}</option>
            @endforeach
        </select>
        @error('payment_mode')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="flex w-full flex-row gap-3">
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Total</label>
        <input type="number" name="total_amount" min="0" step="0.01" value="{{ old('total_amount', $order?->total_amount ?? 0) }}" class="{{ $inputClass('total_amount') }}">
        @error('total_amount')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Desconto</label>
        <input type="number" name="discount_amount" min="0" step="0.01" value="{{ old('discount_amount', $order?->discount_amount ?? 0) }}" class="{{ $inputClass('discount_amount') }}">
        @error('discount_amount')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
</div>

<div>
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Observações</label>
    <textarea name="notes" rows="3"
              class="w-full rounded-lg border bg-transparent px-4 py-3 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:text-white/90 {{ $errors->has('notes') ? 'border-error-500 dark:border-error-500' : 'border-gray-300 dark:border-gray-700' }}">{{ old('notes', $order?->notes) }}</textarea>
    @error('notes')
        <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
    @enderror
</div>
