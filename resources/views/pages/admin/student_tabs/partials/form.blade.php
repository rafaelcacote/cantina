@php($tab = $tab ?? null)
<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    <div>
        <label for="tenant_id" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tenant *</label>
        <select id="tenant_id" name="tenant_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            <option value="">Selecione</option>
            @foreach ($tenants as $tenant)
                <option value="{{ $tenant->id }}" @selected(old('tenant_id', $tab?->tenant_id) == $tenant->id)>{{ $tenant->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="student_id" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Aluno *</label>
        <select id="student_id" name="student_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            <option value="">Selecione</option>
            @foreach ($students as $student)
                <option value="{{ $student->id }}" data-tenant-id="{{ $student->tenant_id }}" @selected(old('student_id', $tab?->student_id) == $student->id)>{{ $student->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="current_balance" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Saldo atual *</label>
        <input id="current_balance" name="current_balance" type="number" min="0" step="0.01" value="{{ old('current_balance', $tab?->current_balance ?? 0) }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
    </div>
    <div>
        <label for="billing_cycle_type" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Ciclo *</label>
        <select id="billing_cycle_type" name="billing_cycle_type" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            @foreach ($cycleTypes as $key => $label)
                <option value="{{ $key }}" @selected(old('billing_cycle_type', $tab?->billing_cycle_type ?? 'monthly') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="due_day" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Dia de vencimento</label>
        <input id="due_day" name="due_day" type="number" min="1" max="31" value="{{ old('due_day', $tab?->due_day) }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
    </div>
</div>
<div class="mt-6">
    <label class="inline-flex items-center gap-3 rounded-lg border border-gray-200 px-4 py-3 dark:border-gray-800">
        <input type="checkbox" name="active" value="1" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" @checked(old('active', $tab?->active ?? true))>
        <span class="text-sm text-gray-700 dark:text-gray-300">Ativo</span>
    </label>
</div>
