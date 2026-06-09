@php
    $order = $order ?? null;
@endphp

<div class="grid grid-cols-1 gap-6 md:grid-cols-3">
    <div>
        <label for="school_id" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Escola *</label>
        <select id="school_id" name="school_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            <option value="">Selecione</option>
            @foreach ($schools as $school)
                <option value="{{ $school->id }}" @selected(old('school_id', $order?->school_id) == $school->id)>{{ $school->name }}</option>
            @endforeach
        </select>
        @error('school_id') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="placed_by_user_id" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Usuário responsável</label>
        <select id="placed_by_user_id" name="placed_by_user_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            <option value="">Selecione</option>
            @foreach ($users as $user)
                <option value="{{ $user->id }}" @selected(old('placed_by_user_id', $order?->placed_by_user_id ?? auth()->id()) == $user->id)>{{ $user->name }} ({{ $user->email }})</option>
            @endforeach
        </select>
        @error('placed_by_user_id') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="student_id" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Aluno</label>
        <select id="student_id" name="student_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            <option value="">Selecione</option>
            @foreach ($students as $student)
                <option value="{{ $student->id }}" @selected(old('student_id', $order?->student_id) == $student->id)>{{ $student->name }}</option>
            @endforeach
        </select>
        @error('student_id') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="parent_id" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Responsável</label>
        <select id="parent_id" name="parent_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            <option value="">Selecione</option>
            @foreach ($parents as $parent)
                <option value="{{ $parent->id }}" @selected(old('parent_id', $order?->parent_id) == $parent->id)>{{ $parent->name }}</option>
            @endforeach
        </select>
        @error('parent_id') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="scheduled_for" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Agendado para</label>
        <input id="scheduled_for" name="scheduled_for" type="datetime-local" value="{{ old('scheduled_for', $order?->scheduled_for?->format('Y-m-d\TH:i')) }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
        @error('scheduled_for') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="order_channel" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Canal *</label>
        <select id="order_channel" name="order_channel" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            @foreach ($channels as $key => $label)
                <option value="{{ $key }}" @selected(old('order_channel', $order?->order_channel ?? 'cashier') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="order_type" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tipo *</label>
        <select id="order_type" name="order_type" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            @foreach ($types as $key => $label)
                <option value="{{ $key }}" @selected(old('order_type', $order?->order_type ?? 'immediate') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="status" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Status *</label>
        <select id="status" name="status" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            @foreach ($statuses as $key => $label)
                <option value="{{ $key }}" @selected(old('status', $order?->status ?? 'pending') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="payment_mode" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Pagamento</label>
        <select id="payment_mode" name="payment_mode" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            <option value="">Selecione</option>
            @foreach ($paymentModes as $key => $label)
                <option value="{{ $key }}" @selected(old('payment_mode', $order?->payment_mode) === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="total_amount" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Total</label>
        <input id="total_amount" name="total_amount" type="number" min="0" step="0.01" value="{{ old('total_amount', $order?->total_amount ?? 0) }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
        @error('total_amount') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="discount_amount" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Desconto</label>
        <input id="discount_amount" name="discount_amount" type="number" min="0" step="0.01" value="{{ old('discount_amount', $order?->discount_amount ?? 0) }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
        @error('discount_amount') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div class="md:col-span-3">
        <label for="notes" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Observações</label>
        <textarea id="notes" name="notes" rows="3" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">{{ old('notes', $order?->notes) }}</textarea>
        @error('notes') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
    </div>
</div>
