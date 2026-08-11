<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    month: { type: Object, required: true },
    hrefFor: { type: Function, required: true },
});
</script>

<template>
    <div class="flex items-center justify-between rounded-[1.25rem] border border-line bg-white/75 px-2 py-2">
        <Link
            :href="hrefFor(month.prev)"
            class="flex size-10 items-center justify-center rounded-2xl text-ink-soft/60 transition hover:bg-mist hover:text-ink"
            :title="`Mês anterior`"
            preserve-scroll
        >
            <i class="pi pi-chevron-left text-xs" />
        </Link>
        <p class="font-display text-base font-semibold text-ink">{{ month.label }}</p>
        <Link
            :href="hrefFor(month.next)"
            class="flex size-10 items-center justify-center rounded-2xl text-ink-soft/60 transition hover:bg-mist hover:text-ink"
            :title="`Próximo mês`"
            preserve-scroll
        >
            <i class="pi pi-chevron-right text-xs" />
        </Link>
    </div>
</template>
