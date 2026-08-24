<script setup>
import MobileShell from '@/Layouts/MobileShell.vue';
import BackLink from '@/Components/portal/BackLink.vue';
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { formatMoney } from '@/composables/useFormat';

const props = defineProps({
    child: { type: Object, required: true },
    control: { type: Object, required: true },
    products: { type: Array, default: () => [] },
});

const form = useForm({
    enabled: Boolean(props.control.enabled),
    daily_spending_limit: props.control.daily_spending_limit ?? '',
    weekly_spending_limit: props.control.weekly_spending_limit ?? '',
    allow_tab_usage: Boolean(props.control.allow_tab_usage),
    allow_wallet_usage: Boolean(props.control.allow_wallet_usage),
    allow_convenience_access: Boolean(props.control.allow_convenience_access),
    allow_snack_access: Boolean(props.control.allow_snack_access),
    blocked_product_ids: props.products.filter((product) => product.blocked).map((product) => product.id),
});

const inputClass = 'h-12 w-full rounded-2xl border border-line bg-white/80 px-4 text-sm text-ink outline-none transition focus:border-leaf/40';
const labelClass = 'mb-1.5 block text-xs font-semibold uppercase tracking-[0.14em] text-ink-soft/45';

const blockedCount = computed(() => form.blocked_product_ids.length);

const isBlocked = (productId) => form.blocked_product_ids.includes(productId);

const toggleBlocked = (productId) => {
    if (isBlocked(productId)) {
        form.blocked_product_ids = form.blocked_product_ids.filter((id) => id !== productId);
        return;
    }

    form.blocked_product_ids = [...form.blocked_product_ids, productId];
};

const submit = () => {
    form.transform((data) => ({
        ...data,
        enabled: data.enabled ? 1 : 0,
        daily_spending_limit: data.daily_spending_limit === '' || data.daily_spending_limit === null
            ? null
            : data.daily_spending_limit,
        weekly_spending_limit: data.weekly_spending_limit === '' || data.weekly_spending_limit === null
            ? null
            : data.weekly_spending_limit,
        allow_tab_usage: data.allow_tab_usage ? 1 : 0,
        allow_wallet_usage: data.allow_wallet_usage ? 1 : 0,
        allow_convenience_access: data.allow_convenience_access ? 1 : 0,
        allow_snack_access: data.allow_snack_access ? 1 : 0,
        blocked_product_ids: data.blocked_product_ids,
    })).put(`/parent/children/${props.child.id}/controls`);
};
</script>

<template>
    <Head :title="`Controle · ${child.name}`" />

    <MobileShell role="parent">
        <section class="space-y-6 pb-28">
            <div>
                <BackLink :href="`/parent/children/${child.id}`" :label="child.name" />
                <h2 class="font-display mt-4 text-[2rem] font-semibold leading-none tracking-tight text-ink">
                    Controle parental
                </h2>
                <p class="mt-2 max-w-[34ch] text-sm leading-relaxed text-ink-soft/60">
                    Defina o que {{ child.name.split(' ')[0] }} pode pedir na cantina e quanto pode gastar.
                </p>
            </div>

            <form class="space-y-6" @submit.prevent="submit">
                <label
                    class="flex cursor-pointer items-center justify-between rounded-[1.35rem] border border-line bg-white/80 px-4 py-4"
                >
                    <span>
                        <span class="block text-sm font-semibold text-ink">Ativar controle</span>
                        <span class="mt-0.5 block text-xs text-ink-soft/50">
                            Quando ligado, as regras abaixo valem no app e nos pedidos.
                        </span>
                    </span>
                    <input v-model="form.enabled" type="checkbox" class="size-5 rounded border-line text-leaf">
                </label>

                <section class="space-y-3.5">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-ink-soft/40">Limites de gasto</p>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label :class="labelClass">Diário (R$)</label>
                            <input
                                v-model="form.daily_spending_limit"
                                type="number"
                                min="0"
                                step="0.01"
                                placeholder="Sem limite"
                                :class="inputClass"
                            >
                            <p v-if="form.errors.daily_spending_limit" class="mt-1.5 text-xs text-bloom">
                                {{ form.errors.daily_spending_limit }}
                            </p>
                        </div>
                        <div>
                            <label :class="labelClass">Semanal (R$)</label>
                            <input
                                v-model="form.weekly_spending_limit"
                                type="number"
                                min="0"
                                step="0.01"
                                placeholder="Sem limite"
                                :class="inputClass"
                            >
                            <p v-if="form.errors.weekly_spending_limit" class="mt-1.5 text-xs text-bloom">
                                {{ form.errors.weekly_spending_limit }}
                            </p>
                        </div>
                    </div>
                </section>

                <section class="space-y-3">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-ink-soft/40">Permissões</p>

                    <label class="flex items-center justify-between rounded-[1.25rem] border border-line bg-white/75 px-4 py-3.5">
                        <span>
                            <span class="block text-sm font-semibold text-ink">Lanches</span>
                            <span class="mt-0.5 block text-xs text-ink-soft/50">Seção de lanches da cantina</span>
                        </span>
                        <input v-model="form.allow_snack_access" type="checkbox" class="size-5 rounded border-line text-leaf">
                    </label>

                    <label class="flex items-center justify-between rounded-[1.25rem] border border-line bg-white/75 px-4 py-3.5">
                        <span>
                            <span class="block text-sm font-semibold text-ink">Conveniência</span>
                            <span class="mt-0.5 block text-xs text-ink-soft/50">Doces, bebidas e extras</span>
                        </span>
                        <input v-model="form.allow_convenience_access" type="checkbox" class="size-5 rounded border-line text-leaf">
                    </label>

                    <label class="flex items-center justify-between rounded-[1.25rem] border border-line bg-white/75 px-4 py-3.5">
                        <span>
                            <span class="block text-sm font-semibold text-ink">Carteira</span>
                            <span class="mt-0.5 block text-xs text-ink-soft/50">Pagar com saldo da carteira</span>
                        </span>
                        <input v-model="form.allow_wallet_usage" type="checkbox" class="size-5 rounded border-line text-leaf">
                    </label>

                    <label class="flex items-center justify-between rounded-[1.25rem] border border-line bg-white/75 px-4 py-3.5">
                        <span>
                            <span class="block text-sm font-semibold text-ink">Fiado</span>
                            <span class="mt-0.5 block text-xs text-ink-soft/50">Lançar compras na conta</span>
                        </span>
                        <input v-model="form.allow_tab_usage" type="checkbox" class="size-5 rounded border-line text-leaf">
                    </label>
                </section>

                <section class="space-y-3">
                    <div class="flex items-end justify-between gap-3">
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-ink-soft/40">Produtos bloqueados</p>
                            <p class="mt-1 text-sm text-ink-soft/55">
                                Toque para bloquear o que não pode pedir.
                            </p>
                        </div>
                        <span class="rounded-full bg-mist px-2.5 py-1 text-[11px] font-semibold text-ink-soft/55">
                            {{ blockedCount }} bloqueados
                        </span>
                    </div>

                    <div v-if="products.length" class="space-y-2">
                        <button
                            v-for="product in products"
                            :key="product.id"
                            type="button"
                            class="flex w-full items-center justify-between gap-3 rounded-[1.25rem] border px-4 py-3.5 text-left transition"
                            :class="isBlocked(product.id)
                                ? 'border-bloom/25 bg-bloom/5'
                                : 'border-line bg-white/75'"
                            @click="toggleBlocked(product.id)"
                        >
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-semibold text-ink">{{ product.name }}</span>
                                <span class="mt-0.5 block truncate text-xs text-ink-soft/50">
                                    {{ product.category || product.section || 'Produto' }}
                                    · {{ formatMoney(product.price) }}
                                </span>
                            </span>
                            <span
                                class="flex size-9 shrink-0 items-center justify-center rounded-full"
                                :class="isBlocked(product.id) ? 'bg-bloom text-white' : 'bg-mist text-ink-soft/45'"
                            >
                                <i :class="isBlocked(product.id) ? 'pi pi-ban' : 'pi pi-check'" class="text-xs" />
                            </span>
                        </button>
                    </div>
                    <p
                        v-else
                        class="rounded-[1.25rem] border border-dashed border-line px-4 py-8 text-center text-sm text-ink-soft/50"
                    >
                        Nenhum produto disponível no cardápio ainda.
                    </p>
                    <p v-if="form.errors.blocked_product_ids" class="text-xs text-bloom">
                        {{ form.errors.blocked_product_ids }}
                    </p>
                </section>

                <button
                    type="submit"
                    class="fixed inset-x-4 bottom-[calc(5.75rem+env(safe-area-inset-bottom))] z-30 mx-auto flex h-14 w-full max-w-lg items-center justify-center rounded-[1.45rem] bg-ink text-sm font-semibold text-foam shadow-[0_18px_42px_rgba(20,36,31,0.28)] disabled:opacity-60"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Salvando...' : 'Salvar controle' }}
                </button>
            </form>
        </section>
    </MobileShell>
</template>
