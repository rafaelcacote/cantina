<script setup>
import MobileShell from '@/Layouts/MobileShell.vue';
import ConfirmSheet from '@/Components/portal/ConfirmSheet.vue';
import BackLink from '@/Components/portal/BackLink.vue';
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import { formatMoney, orderStatusMeta, paymentLabel } from '@/composables/useFormat';

const props = defineProps({
    portalRole: { type: String, default: 'student' },
    basePath: { type: String, default: '/student' },
    order: { type: Object, required: true },
});

const cancelOpen = ref(false);
const cancelling = ref(false);
const status = computed(() => orderStatusMeta(props.order.status));

const cancelOrder = () => {
    cancelling.value = true;
    router.patch(`${props.basePath}/orders/${props.order.id}/cancel`, {}, {
        onFinish: () => {
            cancelling.value = false;
            cancelOpen.value = false;
        },
    });
};
</script>

<template>
    <Head :title="`Pedido #${order.id}`" />

    <MobileShell :role="portalRole">
        <section class="space-y-6">
            <div>
                <BackLink :href="`${basePath}/orders`" label="Pedidos" />
            </div>

            <article class="relative overflow-hidden rounded-[1.7rem] bg-ink px-5 py-5 text-foam shadow-[0_20px_48px_rgba(20,36,31,0.22)]">
                <div class="pointer-events-none absolute -right-12 -top-14 size-40 rounded-full border-[28px] border-zest/10" />
                <div class="relative flex items-start gap-3.5">
                    <span class="flex size-12 shrink-0 items-center justify-center rounded-[1.1rem] bg-zest text-ink">
                        <i :class="status.icon" class="text-base" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-foam/40">
                            Pedido #{{ order.id }}
                        </p>
                        <h2 class="font-display mt-1 text-[1.65rem] font-semibold leading-tight tracking-tight">
                            {{ status.hint || status.label }}
                        </h2>
                        <p class="mt-1.5 text-xs text-foam/45">{{ order.created_at }}</p>
                    </div>
                </div>
                <div class="relative mt-5 flex items-center justify-between border-t border-white/10 pt-4">
                    <span class="text-sm text-foam/50">{{ order.items.length }} {{ order.items.length === 1 ? 'produto' : 'produtos' }}</span>
                    <span class="font-display text-2xl font-semibold text-zest">{{ formatMoney(order.total) }}</span>
                </div>
            </article>

            <article class="overflow-hidden rounded-[1.5rem] border border-line bg-white/70 backdrop-blur">
                <div class="border-b border-line px-4 py-3.5">
                    <h3 class="font-display text-lg font-semibold text-ink">Itens do pedido</h3>
                </div>
                <ul class="divide-y divide-line px-4">
                    <li
                        v-for="item in order.items"
                        :key="item.id"
                        class="flex items-center justify-between gap-3 py-3"
                    >
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="flex size-8 shrink-0 items-center justify-center rounded-xl bg-mist text-xs font-bold text-leaf-deep">
                                {{ item.quantity }}×
                            </span>
                            <div class="min-w-0">
                            <p class="truncate font-medium text-ink">{{ item.name }}</p>
                                <p class="text-xs text-ink-soft/45">{{ formatMoney(item.unit_price) }} cada</p>
                            </div>
                        </div>
                        <p class="shrink-0 font-semibold text-ink">{{ formatMoney(item.total) }}</p>
                    </li>
                </ul>
            </article>

            <article class="flex items-start gap-3 rounded-[1.35rem] bg-white/45 px-4 py-3.5 text-sm">
                <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-mist text-leaf-deep">
                    <i class="pi pi-wallet text-xs" />
                </span>
                <div class="min-w-0">
                    <p class="text-[10px] font-medium uppercase tracking-wide text-ink-soft/40">Pagamento</p>
                    <p class="mt-0.5 font-semibold text-ink">{{ paymentLabel(order.payment_mode) }}</p>
                    <p v-if="order.notes" class="mt-2 leading-relaxed text-ink-soft/60">{{ order.notes }}</p>
                </div>
            </article>

            <Button
                v-if="order.can_cancel"
                label="Cancelar solicitação"
                severity="secondary"
                outlined
                class="w-full"
                icon="pi pi-times"
                @click="cancelOpen = true"
            />
        </section>

        <ConfirmSheet
            v-model:visible="cancelOpen"
            title="Cancelar este pedido?"
            message="A cantina deixa de preparar esta solicitação. Só funciona enquanto o pedido ainda está pendente."
            confirm-label="Cancelar pedido"
            icon="pi pi-times-circle"
            danger
            :loading="cancelling"
            @confirm="cancelOrder"
        />
    </MobileShell>
</template>
