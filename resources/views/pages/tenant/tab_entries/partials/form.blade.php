@php
    $entry = $entry ?? null;
    $inputClass = fn (string $field) => 'h-11 w-full rounded-lg border bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:text-white/90 '.($errors->has($field) ? 'border-error-500 dark:border-error-500' : 'border-gray-300 dark:border-gray-700');
@endphp

<div class="flex w-full flex-row gap-3">
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Aluno <span class="text-error-500">*</span>
        </label>
        <select id="student_id" name="student_id" required class="{{ $inputClass('student_id') }}">
            <option value="">Selecione</option>
            @foreach ($students as $student)
                <option value="{{ $student->id }}" @selected((string) old('student_id', $entry?->student_id) === (string) $student->id)>{{ $student->name }}</option>
            @endforeach
        </select>
        @error('student_id')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Conta de fiado <span class="text-error-500">*</span>
        </label>
        <select id="student_tab_id" name="student_tab_id" required class="{{ $inputClass('student_tab_id') }}">
            <option value="">Selecione</option>
            @foreach ($studentTabs as $tab)
                <option value="{{ $tab->id }}" data-student-id="{{ $tab->student_id }}" @selected((string) old('student_tab_id', $entry?->student_tab_id) === (string) $tab->id)>
                    #{{ $tab->id }} — {{ $tab->student?->name ?? 'Aluno '.$tab->student_id }}
                </option>
            @endforeach
        </select>
        @error('student_tab_id')
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
            <option value="">Selecione</option>
            @foreach ($orders as $order)
                <option value="{{ $order->id }}" @selected((string) old('order_id', $entry?->order_id) === (string) $order->id)>#{{ $order->id }}</option>
            @endforeach
        </select>
        @error('order_id')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Valor <span class="text-error-500">*</span>
        </label>
        <input type="number" name="amount" min="0" step="0.01" value="{{ old('amount', $entry?->amount) }}" required class="{{ $inputClass('amount') }}">
        @error('amount')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="flex w-full flex-row gap-3">
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Data <span class="text-error-500">*</span>
        </label>
        <input type="date" name="entry_date" value="{{ old('entry_date', $entry?->entry_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required class="{{ $inputClass('entry_date') }}">
        @error('entry_date')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Status <span class="text-error-500">*</span>
        </label>
        <select name="status" required class="{{ $inputClass('status') }}">
            @foreach ($statuses as $key => $label)
                <option value="{{ $key }}" @selected(old('status', $entry?->status ?? 'open') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="flex w-full flex-row gap-3">
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Autorizado por PIN <span class="text-error-500">*</span>
        </label>
        <select name="authorized_by_pin" class="{{ $inputClass('authorized_by_pin') }}">
            <option value="0" @selected(old('authorized_by_pin', (string) (int) ($entry?->authorized_by_pin ?? 0)) === '0')>Não</option>
            <option value="1" @selected(old('authorized_by_pin', (string) (int) ($entry?->authorized_by_pin ?? 0)) === '1')>Sim</option>
        </select>
        @error('authorized_by_pin')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Método autorização
        </label>
        <select name="authorization_method" class="{{ $inputClass('authorization_method') }}">
            <option value="">Selecione</option>
            @foreach ($authorizationMethods as $key => $label)
                <option value="{{ $key }}" @selected(old('authorization_method', $entry?->authorization_method) === $key)>{{ $label }}</option>
            @endforeach
        </select>
        @error('authorization_method')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="flex w-full flex-row gap-3">
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Autorizado em
        </label>
        <input type="datetime-local" name="authorized_at" value="{{ old('authorized_at', $entry?->authorized_at?->format('Y-m-d\TH:i')) }}" class="{{ $inputClass('authorized_at') }}">
        @error('authorized_at')
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
                <option value="{{ $user->id }}" @selected((string) old('created_by', $entry?->created_by ?? auth()->id()) === (string) $user->id)>{{ $user->name }}</option>
            @endforeach
        </select>
        @error('created_by')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
</div>

<div>
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
        Descrição
    </label>
    <input type="text" name="description" value="{{ old('description', $entry?->description) }}" class="{{ $inputClass('description') }}">
    @error('description')
        <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
    @enderror
</div>
