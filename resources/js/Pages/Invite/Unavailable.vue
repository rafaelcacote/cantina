<script setup>
import InviteShell from '@/Layouts/InviteShell.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    tenant: { type: Object, required: true },
    reason: { type: String, required: true },
});
</script>

<template>
    <Head title="Convite indisponível" />

    <InviteShell :tenant="tenant">
        <section class="rounded-[1.7rem] border border-line bg-white/80 px-5 py-10 text-center backdrop-blur">
            <span class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-mist text-leaf-deep">
                <i class="pi pi-link text-xl" />
            </span>
            <h2 class="font-display mt-5 text-2xl font-semibold text-ink">Convite indisponível</h2>
            <p class="mx-auto mt-3 max-w-[30ch] text-sm leading-relaxed text-ink-soft/65">
                {{ reason }}
            </p>
        </section>
    </InviteShell>
</template>
