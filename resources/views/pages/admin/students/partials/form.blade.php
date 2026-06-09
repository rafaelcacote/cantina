@php
    $selectedTenant = old('tenant_id', $student?->tenant_id);
    $selectedSchool = old('school_id', $student?->school_id);
@endphp

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
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
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Escola</label>
        <select id="schoolSelect" name="school_id" required
                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
            <option value="">Selecione</option>
            @foreach($schools as $school)
                <option value="{{ $school->id }}" data-tenant="{{ $school->tenant_id }}" @selected((string) $selectedSchool === (string) $school->id)>{{ $school->name }}</option>
            @endforeach
        </select>
    </div>
</div>

<div>
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Nome</label>
    <input type="text" name="name" value="{{ old('name', $student?->name) }}" required
           class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
</div>

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Matrícula</label>
        <input type="text" name="enrollment_number" value="{{ old('enrollment_number', $student?->enrollment_number) }}"
               class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
    </div>
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Data de Nascimento</label>
        <input type="date" name="birth_date" value="{{ old('birth_date', $student?->birth_date?->format('Y-m-d')) }}"
               class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
    </div>
</div>

<div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Série</label>
        <input type="text" name="grade" value="{{ old('grade', $student?->grade) }}"
               class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
    </div>
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Turma</label>
        <input type="text" name="classroom" value="{{ old('classroom', $student?->classroom) }}"
               class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
    </div>
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Turno</label>
        <input type="text" name="shift" value="{{ old('shift', $student?->shift) }}"
               class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
    </div>
</div>

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
        <select name="status" required
                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
            @foreach($statusOptions as $status)
                <option value="{{ $status }}" @selected(old('status', $student?->status ?? 'pending') === $status)>{{ $status }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Foto URL</label>
        <input type="text" name="photo_url" value="{{ old('photo_url', $student?->photo_url) }}"
               class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
    </div>
</div>

<div>
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">PIN (hash/texto)</label>
    <input type="text" name="personal_pin_hash" value="{{ old('personal_pin_hash', $student?->personal_pin_hash) }}"
           class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
</div>

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Pode comprar no crédito</label>
        <select name="can_buy_on_credit"
                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
            <option value="1" @selected(old('can_buy_on_credit', (string) (int) ($student?->can_buy_on_credit ?? 0)) === '1')>Sim</option>
            <option value="0" @selected(old('can_buy_on_credit', (string) (int) ($student?->can_buy_on_credit ?? 0)) === '0')>Não</option>
        </select>
    </div>
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Pode comprar no fiado</label>
        <select name="can_buy_on_tab"
                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
            <option value="1" @selected(old('can_buy_on_tab', (string) (int) ($student?->can_buy_on_tab ?? 0)) === '1')>Sim</option>
            <option value="0" @selected(old('can_buy_on_tab', (string) (int) ($student?->can_buy_on_tab ?? 0)) === '0')>Não</option>
        </select>
    </div>
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Acesso conveniência</label>
        <select name="convenience_access"
                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
            <option value="1" @selected(old('convenience_access', (string) (int) ($student?->convenience_access ?? 0)) === '1')>Sim</option>
            <option value="0" @selected(old('convenience_access', (string) (int) ($student?->convenience_access ?? 0)) === '0')>Não</option>
        </select>
    </div>
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Acesso lanches</label>
        <select name="snack_access"
                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
            <option value="1" @selected(old('snack_access', (string) (int) ($student?->snack_access ?? 1)) === '1')>Sim</option>
            <option value="0" @selected(old('snack_access', (string) (int) ($student?->snack_access ?? 1)) === '0')>Não</option>
        </select>
    </div>
</div>

@push('scripts')
    <script>
        (function () {
            const tenantSelect = document.getElementById('tenantSelect');
            const schoolSelect = document.getElementById('schoolSelect');
            if (!tenantSelect || !schoolSelect) return;

            const applyFilter = () => {
                const tenantId = tenantSelect.value;
                [...schoolSelect.options].forEach((option, index) => {
                    if (index === 0) {
                        option.hidden = false;
                        return;
                    }
                    const optionTenantId = option.getAttribute('data-tenant');
                    option.hidden = !!tenantId && optionTenantId !== tenantId;
                    if (option.hidden && option.selected) {
                        schoolSelect.value = '';
                    }
                });
            };

            tenantSelect.addEventListener('change', applyFilter);
            applyFilter();
        })();
    </script>
@endpush
