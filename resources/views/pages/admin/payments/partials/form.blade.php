@php($payment = $payment ?? null)
<div class="grid grid-cols-1 gap-6 md:grid-cols-3">
    <div>
        <label for="tenant_id" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tenant *</label>
        <select id="tenant_id" name="tenant_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            <option value="">Selecione</option>
            @foreach ($tenants as $tenant)
                <option value="{{ $tenant->id }}" @selected(old('tenant_id', $payment?->tenant_id) == $tenant->id)>{{ $tenant->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="student_id" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Aluno</label>
        <select id="student_id" name="student_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            <option value="">Selecione</option>
            @foreach ($students as $student)
                <option value="{{ $student->id }}" data-tenant-id="{{ $student->tenant_id }}" @selected(old('student_id', $payment?->student_id) == $student->id)>{{ $student->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="parent_id" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Responsável</label>
        <select id="parent_id" name="parent_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            <option value="">Selecione</option>
            @foreach ($parents as $parent)
                <option value="{{ $parent->id }}" data-tenant-id="{{ $parent->tenant_id }}" @selected(old('parent_id', $payment?->parent_id) == $parent->id)>{{ $parent->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="amount" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Valor *</label>
        <input id="amount" name="amount" type="number" min="0" step="0.01" value="{{ old('amount', $payment?->amount) }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
    </div>
    <div>
        <label for="payment_method" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Método *</label>
        <select id="payment_method" name="payment_method" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            @foreach ($methods as $key => $label)
                <option value="{{ $key }}" @selected(old('payment_method', $payment?->payment_method) === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="status" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Status *</label>
        <select id="status" name="status" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            @foreach ($statuses as $key => $label)
                <option value="{{ $key }}" @selected(old('status', $payment?->status ?? 'pending') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="paid_at" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Pago em</label>
        <input id="paid_at" name="paid_at" type="datetime-local" value="{{ old('paid_at', $payment?->paid_at?->format('Y-m-d\TH:i')) }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
    </div>
    <div>
        <label for="reference" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Referência</label>
        <input id="reference" name="reference" type="text" value="{{ old('reference', $payment?->reference) }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
    </div>
    <div>
        <label for="created_by" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Criado por</label>
        <select id="created_by" name="created_by" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            <option value="">Selecione</option>
            @foreach ($users as $user)
                <option value="{{ $user->id }}" data-tenant-id="{{ $user->tenant_id }}" @selected(old('created_by', $payment?->created_by) == $user->id)>{{ $user->name }}</option>
            @endforeach
        </select>
    </div>
</div>
