<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: { type: String, required: true },
    label: { type: String, default: 'Voltar' },
});
</script>

<template>
    <Link
        :href="href"
        class="group inline-flex items-center gap-2 rounded-full border border-line bg-white/75 py-1 pl-1 pr-3.5 text-sm font-semibold text-ink shadow-[0_6px_18px_rgba(20,36,31,0.05)] backdrop-blur transition hover:bg-white active:scale-[0.98]"
    >
        <span class="flex size-7 items-center justify-center rounded-full bg-mist text-leaf-deep transition group-hover:bg-zest/70 group-hover:text-ink">
            <i class="pi pi-arrow-left text-[11px]" />
        </span>
        {{ label }}
    </Link>
</template>
