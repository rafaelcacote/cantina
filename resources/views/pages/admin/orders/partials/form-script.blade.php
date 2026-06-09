<script>
    (function () {
        const tenant = document.getElementById('tenant_id');
        const school = document.getElementById('school_id');
        const student = document.getElementById('student_id');
        const parent = document.getElementById('parent_id');
        const user = document.getElementById('placed_by_user_id');
        if (!tenant) return;

        const syncByTenant = (select) => {
            if (!select) return;
            const tenantId = tenant.value;
            [...select.options].forEach((opt, idx) => {
                if (idx === 0) return;
                const match = !!tenantId && opt.dataset.tenantId === tenantId;
                opt.hidden = !match;
                opt.disabled = !match;
            });
            if (!tenantId || select.selectedOptions[0]?.disabled) select.value = '';
        };

        tenant.addEventListener('change', () => {
            syncByTenant(school);
            syncByTenant(student);
            syncByTenant(parent);
            syncByTenant(user);
        });

        syncByTenant(school);
        syncByTenant(student);
        syncByTenant(parent);
        syncByTenant(user);
    })();
</script>
