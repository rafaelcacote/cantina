@php
    $payment = $payment ?? null;
    $inputClass = fn (string $field) => 'h-11 w-full rounded-lg border bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:text-white/90 '.($errors->has($field) ? 'border-error-500 dark:border-error-500' : 'border-gray-300 dark:border-gray-700');
@endphp

<div class="flex w-full flex-row gap-3">
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Aluno
        </label>
        <select name="student_id" class="{{ $inputClass('student_id') }}">
            <option value="">Selecione</option>
            @foreach ($students as $student)
                <option value="{{ $student->id }}" @selected((string) old('student_id', $payment?->student_id) === (string) $student->id)>{{ $student->name }}</option>
            @endforeach
        </select>
        @error('student_id')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Responsável
        </label>
        <select name="parent_id" class="{{ $inputClass('parent_id') }}">
            <option value="">Selecione</option>
            @foreach ($parents as $parent)
                <option value="{{ $parent->id }}" @selected((string) old('parent_id', $payment?->parent_id) === (string) $parent->id)>{{ $parent->name }}</option>
            @endforeach
        </select>
        @error('parent_id')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="flex w-full flex-row gap-3">
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Pedido
        </label>
        <select name="order_id" class="{{ $inputClass('order_id') }}">
            <option value="">Nenhum</option>
            @foreach ($orders ?? [] as $order)
                <option value="{{ $order->id }}" @selected((string) old('order_id', $payment?->order_id) === (string) $order->id)>
                    #{{ $order->id }} — R$ {{ number_format((float) $order->final_amount, 2, ',', '.') }} ({{ $order->status }})
                </option>
            @endforeach
        </select>
        @error('order_id')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Lançamento de fiado
        </label>
        <select name="tab_entry_id" class="{{ $inputClass('tab_entry_id') }}">
            <option value="">Nenhum</option>
            @foreach ($tabEntries ?? [] as $entry)
                <option value="{{ $entry->id }}" @selected((string) old('tab_entry_id', $payment?->tab_entry_id) === (string) $entry->id)>
                    #{{ $entry->id }} — {{ $entry->student?->name ?? 'Aluno' }} — R$ {{ number_format((float) $entry->amount, 2, ',', '.') }}
                </option>
            @endforeach
        </select>
        @error('tab_entry_id')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="flex w-full flex-row gap-3">
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Valor <span class="text-error-500">*</span>
        </label>
        <input type="number" name="amount" min="0" step="0.01" value="{{ old('amount', $payment?->amount) }}" required class="{{ $inputClass('amount') }}">
        @error('amount')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Método <span class="text-error-500">*</span>
        </label>
        <select name="payment_method" required class="{{ $inputClass('payment_method') }}">
            @foreach ($methods as $key => $label)
                <option value="{{ $key }}" @selected(old('payment_method', $payment?->payment_method) === $key)>{{ $label }}</option>
            @endforeach
        </select>
        @error('payment_method')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="flex w-full flex-row gap-3">
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Status <span class="text-error-500">*</span>
        </label>
        <select name="status" required class="{{ $inputClass('status') }}">
            @foreach ($statuses as $key => $label)
                <option value="{{ $key }}" @selected(old('status', $payment?->status ?? 'pending') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Pago em
        </label>
        <input type="datetime-local" name="paid_at" value="{{ old('paid_at', $payment?->paid_at?->format('Y-m-d\TH:i')) }}" class="{{ $inputClass('paid_at') }}">
        @error('paid_at')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="flex w-full flex-row gap-3">
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Referência
        </label>
        <input type="text" name="reference" value="{{ old('reference', $payment?->reference) }}" class="{{ $inputClass('reference') }}">
        @error('reference')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Criado por
        </label>
        <select name="created_by" class="{{ $inputClass('created_by') }}">
            <option value="">Selecione</option>
            @foreach ($users as $user)
                <option value="{{ $user->id }}" @selected((string) old('created_by', $payment?->created_by ?? auth()->id()) === (string) $user->id)>{{ $user->name }}</option>
            @endforeach
        </select>
        @error('created_by')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
</div>
