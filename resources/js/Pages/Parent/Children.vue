<script setup>
import MobileShell from '@/Layouts/MobileShell.vue';
import { Head, Link } from '@inertiajs/vue3';
import { formatMoney, studentStatusMeta } from '@/composables/useFormat';

defineProps({
    children: { type: Array, default: () => [] },
});
</script>

<template>
    <Head title="Filhos" />

    <MobileShell role="parent">
        <section class="space-y-6">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 class="font-display text-[2rem] font-semibold leading-none tracking-tight text-ink">
                        Filhos
                    </h2>
                    <p class="mt-2 max-w-[28ch] text-sm leading-relaxed text-ink-soft/60">
                        Saldos, turma e status de cada aluno.
                    </p>
                </div>
                <Link
                    href="/parent/children/create"
                    class="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-ink text-zest shadow-[0_10px_24px_rgba(20,36,31,0.16)]"
                    title="Cadastrar filho"
                >
                    <i class="pi pi-plus text-sm" />
                </Link>
            </div>

            <div v-if="children.length" class="space-y-3">
                <Link
                    v-for="child in children"
                    :key="child.id"
                    :href="`/parent/children/${child.id}`"
                    class="flex items-center gap-3 rounded-[1.35rem] border border-line bg-white/80 p-3.5 backdrop-blur transition active:scale-[0.99]"
                >
                    <div class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-mist font-display text-lg font-semibold text-leaf-deep">
                        {{ child.name.charAt(0).toUpperCase() }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-ink">{{ child.name }}</p>
                        <p class="mt-0.5 truncate text-xs text-ink-soft/55">
                            {{ child.school || 'Escola' }}
                            <span v-if="child.grade"> · {{ child.grade }}</span>
                        </p>
                        <p class="mt-1 text-[11px] font-semibold text-ink-soft/40">
                            {{ studentStatusMeta(child.status).label }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-leaf-deep">{{ formatMoney(child.balance) }}</p>
                        <p class="text-[11px] text-ink-soft/45">carteira</p>
                        <p v-if="child.tab_balance > 0" class="mt-1 text-[11px] font-semibold text-bloom">
                            {{ formatMoney(child.tab_balance) }} fiado
                        </p>
                    </div>
                </Link>
            </div>

            <div
                v-else
                class="rounded-[1.35rem] border border-dashed border-line bg-white/50 px-5 py-10 text-center"
            >
                <p class="font-display text-lg font-semibold text-ink">Nenhum filho ainda</p>
                <p class="mx-auto mt-2 max-w-[28ch] text-sm text-ink-soft/60">
                    Cadastre o primeiro aluno para acompanhar saldo e pedidos.
                </p>
                <Link
                    href="/parent/children/create"
                    class="mt-5 inline-flex h-12 items-center rounded-2xl bg-ink px-5 text-sm font-semibold text-foam"
                >
                    Cadastrar filho
                </Link>
            </div>
        </section>
    </MobileShell>
</template>
