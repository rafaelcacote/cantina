<div id="tenant_field_wrapper">
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Tenant</label>
    <select id="tenant_id" name="tenant_id"
            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
        <option value="">Selecione</option>
        @foreach($tenants as $tenant)
            <option value="{{ $tenant->id }}" @selected((int) old('tenant_id', $selectedTenantId ?? '') === $tenant->id)>{{ $tenant->name }}</option>
        @endforeach
    </select>
    <p id="tenant_field_hint" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
        Obrigatório para usuários vinculados a uma cantina.
    </p>
</div>

@push('scripts')
    <script>
        (function () {
            const userType = document.querySelector('select[name="user_type"]');
            const tenantWrapper = document.getElementById('tenant_field_wrapper');
            const tenantSelect = document.getElementById('tenant_id');
            const tenantHint = document.getElementById('tenant_field_hint');

            if (!userType || !tenantWrapper || !tenantSelect) return;

            const syncTenantField = () => {
                const isSuperAdmin = userType.value === 'super_admin';
                tenantWrapper.classList.toggle('hidden', isSuperAdmin);
                tenantSelect.required = !isSuperAdmin;
                tenantSelect.disabled = isSuperAdmin;

                if (isSuperAdmin) {
                    tenantSelect.value = '';
                }

                if (tenantHint) {
                    tenantHint.textContent = isSuperAdmin
                        ? 'Super admin não pertence a um tenant.'
                        : 'Obrigatório para usuários vinculados a uma cantina.';
                }
            };

            userType.addEventListener('change', syncTenantField);
            syncTenantField();
        })();
    </script>
@endpush
