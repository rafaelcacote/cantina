@php($order = $order ?? null)
<div class="grid grid-cols-1 gap-6 md:grid-cols-3">
    <div>
        <label for="tenant_id" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tenant *</label>
        <select id="tenant_id" name="tenant_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            <option value="">Selecione</option>
            @foreach ($tenants as $tenant)
                <option value="{{ $tenant->id }}" @selected(old('tenant_id', $order?->tenant_id) == $tenant->id)>{{ $tenant->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="school_id" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Escola *</label>
        <select id="school_id" name="school_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            <option value="">Selecione</option>
            @foreach ($schools as $school)
                <option value="{{ $school->id }}" data-tenant-id="{{ $school->tenant_id }}" @selected(old('school_id', $order?->school_id) == $school->id)>{{ $school->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="parent_id" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Responsável *</label>
        <select id="parent_id" name="parent_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            <option value="">Selecione</option>
            @foreach ($parents as $parent)
                <option value="{{ $parent->id }}" data-tenant-id="{{ $parent->tenant_id }}" @selected(old('parent_id', $order?->parent_id) == $parent->id)>{{ $parent->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="student_id" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Aluno *</label>
        <select id="student_id" name="student_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            <option value="">Selecione</option>
            @foreach ($students as $student)
                <option value="{{ $student->id }}" data-tenant-id="{{ $student->tenant_id }}" @selected(old('student_id', $order?->student_id) == $student->id)>{{ $student->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="order_date" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Data do pedido *</label>
        <input id="order_date" name="order_date" type="date" value="{{ old('order_date', $order?->order_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
    </div>
    <div>
        <label for="status" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Status *</label>
        <select id="status" name="status" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            @foreach ($statuses as $key => $label)
                <option value="{{ $key }}" @selected(old('status', $order?->status ?? 'active') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="md:col-span-3">
        <label for="notes" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Observações</label>
        <textarea id="notes" name="notes" rows="3" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">{{ old('notes', $order?->notes) }}</textarea>
    </div>
</div>
