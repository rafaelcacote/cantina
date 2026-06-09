@php($wallet = $wallet ?? null)
<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    <div>
        <label for="tenant_id" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tenant *</label>
        <select id="tenant_id" name="tenant_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            <option value="">Selecione</option>
            @foreach ($tenants as $tenant)
                <option value="{{ $tenant->id }}" @selected(old('tenant_id', $wallet?->tenant_id) == $tenant->id)>{{ $tenant->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="student_id" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Aluno *</label>
        <select id="student_id" name="student_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            <option value="">Selecione</option>
            @foreach ($students as $student)
                <option value="{{ $student->id }}" data-tenant-id="{{ $student->tenant_id }}" @selected(old('student_id', $wallet?->student_id) == $student->id)>{{ $student->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="balance" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Saldo *</label>
        <input id="balance" name="balance" type="number" step="0.01" value="{{ old('balance', $wallet?->balance ?? 0) }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
    </div>
    <div>
        <label for="credit_limit" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Limite de crédito *</label>
        <input id="credit_limit" name="credit_limit" type="number" step="0.01" min="0" value="{{ old('credit_limit', $wallet?->credit_limit ?? 0) }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
    </div>
</div>
<div class="mt-6">
    <label class="inline-flex items-center gap-3 rounded-lg border border-gray-200 px-4 py-3 dark:border-gray-800">
        <input type="checkbox" name="allow_negative_balance" value="1" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" @checked(old('allow_negative_balance', $wallet?->allow_negative_balance ?? false))>
        <span class="text-sm text-gray-700 dark:text-gray-300">Permitir saldo negativo</span>
    </label>
</div>
