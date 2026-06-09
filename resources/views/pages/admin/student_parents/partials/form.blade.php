@php
    $selectedTenant = old('tenant_id', $studentParent?->tenant_id);
    $selectedStudent = old('student_id', $studentParent?->student_id);
    $selectedParent = old('parent_id', $studentParent?->parent_id);
@endphp

<div>
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Tenant</label>
    <select id="tenantSelect" name="tenant_id" required
            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
        <option value="">Selecione</option>
        @foreach($tenants as $tenant)
            <option value="{{ $tenant->id }}" @selected((string) $selectedTenant === (string) $tenant->id)>{{ $tenant->name }}</option>
        @endforeach
    </select>
</div>

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Aluno</label>
        <select id="studentSelect" name="student_id" required
                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
            <option value="">Selecione</option>
            @foreach($students as $student)
                <option value="{{ $student->id }}" data-tenant="{{ $student->tenant_id }}" @selected((string) $selectedStudent === (string) $student->id)>{{ $student->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Responsável</label>
        <select id="parentSelect" name="parent_id" required
                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
            <option value="">Selecione</option>
            @foreach($parents as $parent)
                <option value="{{ $parent->id }}" data-tenant="{{ $parent->tenant_id }}" @selected((string) $selectedParent === (string) $parent->id)>{{ $parent->name }}</option>
            @endforeach
        </select>
    </div>
</div>

<div>
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de relação</label>
    <input type="text" name="relationship_type" value="{{ old('relationship_type', $studentParent?->relationship_type) }}"
           class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
</div>

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Principal</label>
        <select name="is_primary"
                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
            <option value="1" @selected(old('is_primary', (string) (int) ($studentParent?->is_primary ?? 0)) === '1')>Sim</option>
            <option value="0" @selected(old('is_primary', (string) (int) ($studentParent?->is_primary ?? 0)) === '0')>Não</option>
        </select>
    </div>
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Responsável financeiro</label>
        <select name="financial_responsible"
                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
            <option value="1" @selected(old('financial_responsible', (string) (int) ($studentParent?->financial_responsible ?? 0)) === '1')>Sim</option>
            <option value="0" @selected(old('financial_responsible', (string) (int) ($studentParent?->financial_responsible ?? 0)) === '0')>Não</option>
        </select>
    </div>
</div>

@push('scripts')
    <script>
        (function () {
            const tenantSelect = document.getElementById('tenantSelect');
            const studentSelect = document.getElementById('studentSelect');
            const parentSelect = document.getElementById('parentSelect');
            if (!tenantSelect || !studentSelect || !parentSelect) return;

            const filterByTenant = (selectEl) => {
                const tenantId = tenantSelect.value;
                [...selectEl.options].forEach((option, index) => {
                    if (index === 0) {
                        option.hidden = false;
                        return;
                    }
                    const optionTenantId = option.getAttribute('data-tenant');
                    option.hidden = !!tenantId && optionTenantId !== tenantId;
                    if (option.hidden && option.selected) {
                        selectEl.value = '';
                    }
                });
            };

            const applyFilters = () => {
                filterByTenant(studentSelect);
                filterByTenant(parentSelect);
            };

            tenantSelect.addEventListener('change', applyFilters);
            applyFilters();
        })();
    </script>
@endpush
