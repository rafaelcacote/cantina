<script setup>
import MobileShell from '@/Layouts/MobileShell.vue';
import BackLink from '@/Components/portal/BackLink.vue';
import { Head, Link } from '@inertiajs/vue3';
import { formatMoney, orderStatusMeta, studentStatusMeta, topupStatusMeta } from '@/composables/useFormat';

defineProps({
    child: { type: Object, required: true },
    orders: { type: Array, default: () => [] },
    transactions: { type: Array, default: () => [] },
    topups: { type: Array, default: () => [] },
    canDeposit: { type: Boolean, default: false },
    tab: {
        type: Object,
        default: () => ({
            month: { label: '', key: '' },
            summary: { charged: 0, open: 0, paid: 0, count: 0 },
            open_balance: 0,
        }),
    },
});

const classroom = (child) => [child.grade, child.classroom].filter(Boolean).join(' · ') || '—';
</script>

<template>
    <Head :title="child.name" />

    <MobileShell role="parent">
        <section class="space-y-6">
            <div>
                <BackLink href="/parent/children" label="Filhos" />
            </div>

            <article class="relative overflow-hidden rounded-[1.7rem] bg-ink px-5 py-5 text-foam shadow-[0_20px_48px_rgba(20,36,31,0.22)]">
                <div class="pointer-events-none absolute -right-10 -top-12 size-40 rounded-full border-[28px] border-zest/10" />
                <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-foam/40">Carteira</p>
                <h2 class="font-display mt-2 text-[1.7rem] font-semibold leading-tight">{{ child.name }}</h2>
                <p class="mt-1 text-xs text-foam/50">{{ child.school || 'Escola' }}</p>
                <p class="font-display mt-5 text-[2.2rem] font-semibold leading-none text-zest">
                    {{ formatMoney(child.balance) }}
                </p>
                <p class="mt-3 text-[11px] font-semibold uppercase tracking-wide text-foam/40">
                    {{ studentStatusMeta(child.status).label }}
                </p>
                <div class="relative mt-5 space-y-2">
                    <Link
                        v-if="child.can_order"
                        :href="`/parent/children/${child.id}/menu`"
                        class="flex items-center justify-between rounded-[1.25rem] bg-zest px-4 py-3.5 text-ink"
                    >
                        <span>
                            <span class="block text-[10px] font-bold uppercase tracking-[0.15em] text-ink/55">Cantina</span>
                            <span class="mt-0.5 block font-semibold">Fazer pedido para {{ child.name.split(' ')[0] }}</span>
                        </span>
                        <span class="flex size-9 items-center justify-center rounded-full bg-ink text-zest">
                            <i class="pi pi-shopping-bag text-xs" />
                        </span>
                    </Link>
                    <Link
                        v-if="canDeposit"
                        :href="`/parent/children/${child.id}/topups/create`"
                        class="flex items-center justify-between rounded-[1.25rem] bg-white/10 px-4 py-3.5 text-foam"
                    >
                        <span>
                            <span class="block text-[10px] font-bold uppercase tracking-[0.15em] text-foam/45">Pix</span>
                            <span class="mt-0.5 block font-semibold">Depositar na carteira</span>
                        </span>
                        <span class="flex size-9 items-center justify-center rounded-full bg-zest text-ink">
                            <i class="pi pi-plus text-xs" />
                        </span>
                    </Link>
                    <Link
                        :href="`/parent/children/${child.id}/controls`"
                        class="flex items-center justify-between rounded-[1.25rem] bg-white/10 px-4 py-3.5 text-foam"
                    >
                        <span>
                            <span class="block text-[10px] font-bold uppercase tracking-[0.15em] text-foam/45">Controle parental</span>
                            <span class="mt-0.5 block font-semibold">Limites, seções e produtos bloqueados</span>
                        </span>
                        <span class="flex size-9 items-center justify-center rounded-full bg-zest text-ink">
                            <i class="pi pi-shield text-xs" />
                        </span>
                    </Link>
                    <Link
                        :href="`/parent/children/${child.id}/tab`"
                        class="flex items-center justify-between rounded-[1.25rem] bg-white/10 px-4 py-3.5 text-foam"
                    >
                        <span>
                            <span class="block text-[10px] font-bold uppercase tracking-[0.15em] text-foam/45">Fiado</span>
                            <span class="mt-0.5 block font-semibold">Ver o que pediu no fiado</span>
                        </span>
                        <span class="flex size-9 items-center justify-center rounded-full bg-zest text-ink">
                            <i class="pi pi-book text-xs" />
                        </span>
                    </Link>
                    <Link
                        :href="`/parent/children/${child.id}/edit`"
                        class="flex items-center justify-between rounded-[1.25rem] bg-white/10 px-4 py-3.5 text-foam"
                    >
                        <span>
                            <span class="block text-[10px] font-bold uppercase tracking-[0.15em] text-foam/45">Dados e permissões</span>
                            <span class="mt-0.5 block font-semibold">Editar cadastro, fiado e PIN</span>
                        </span>
                        <span class="flex size-9 items-center justify-center rounded-full bg-zest text-ink">
                            <i class="pi pi-pencil text-xs" />
                        </span>
                    </Link>
                    <Link
                        :href="`/parent/children/${child.id}/access`"
                        class="flex items-center justify-between rounded-[1.25rem] bg-white/10 px-4 py-3.5 text-foam"
                    >
                        <span>
                            <span class="block text-[10px] font-bold uppercase tracking-[0.15em] text-foam/45">App do aluno</span>
                            <span class="mt-0.5 block font-semibold">{{ child.has_access ? 'Enviar link de entrada' : 'Enviar acesso ao filho' }}</span>
                        </span>
                        <span class="flex size-9 items-center justify-center rounded-full bg-zest text-ink">
                            <i class="pi pi-send text-xs" />
                        </span>
                    </Link>
                </div>
            </article>

            <dl class="overflow-hidden rounded-[1.45rem] border border-line bg-white/65 px-4 backdrop-blur">
                <div class="flex items-center justify-between border-b border-line py-3.5">
                    <dt class="text-xs text-ink-soft/45">Turma</dt>
                    <dd class="text-sm font-semibold text-ink">{{ classroom(child) }}</dd>
                </div>
                <div class="flex items-center justify-between border-b border-line py-3.5">
                    <dt class="text-xs text-ink-soft/45">Turno</dt>
                    <dd class="text-sm font-semibold text-ink">{{ child.shift || '—' }}</dd>
                </div>
                <div class="flex items-center justify-between border-b border-line py-3.5">
                    <dt class="text-xs text-ink-soft/45">Nascimento</dt>
                    <dd class="text-sm font-semibold text-ink">{{ child.birth_date || '—' }}</dd>
                </div>
                <div class="flex items-center justify-between border-b border-line py-3.5">
                    <dt class="text-xs text-ink-soft/45">Parentesco</dt>
                    <dd class="text-sm font-semibold text-ink">{{ child.relationship || '—' }}</dd>
                </div>
                <div class="flex items-center justify-between border-b border-line py-3.5">
                    <dt class="text-xs text-ink-soft/45">Pode comprar no fiado</dt>
                    <dd class="text-sm font-semibold text-ink">{{ child.can_buy_on_tab ? 'Sim' : 'Não' }}</dd>
                </div>
                <div class="flex items-center justify-between py-3.5">
                    <dt class="text-xs text-ink-soft/45">PIN do aluno</dt>
                    <dd class="text-sm font-semibold text-ink">{{ child.has_pin ? 'Cadastrado' : 'Não cadastrado' }}</dd>
                </div>
            </dl>

            <section>
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="font-display text-xl font-semibold text-ink">Fiado</h3>
                    <Link :href="`/parent/children/${child.id}/tab`" class="text-xs font-semibold text-leaf-deep">
                        Ver por mês
                    </Link>
                </div>
                <Link
                    :href="`/parent/children/${child.id}/tab`"
                    class="block overflow-hidden rounded-[1.45rem] border border-line bg-white/70 px-4 py-4"
                >
                    <div class="flex items-end justify-between gap-3">
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-ink-soft/40">
                                {{ tab.month?.label || 'Este mês' }}
                            </p>
                            <p class="font-display mt-2 text-2xl font-semibold text-ink">
                                {{ formatMoney(tab.summary?.charged) }}
                            </p>
                            <p class="mt-1 text-xs text-ink-soft/50">
                                {{ tab.summary?.count || 0 }}
                                {{ (tab.summary?.count || 0) === 1 ? 'pedido' : 'pedidos' }} neste mês
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-ink-soft/40">Em aberto</p>
                            <p class="mt-1 text-sm font-semibold text-bloom">{{ formatMoney(tab.open_balance) }}</p>
                        </div>
                    </div>
                </Link>
            </section>

            <section v-if="topups.length">
                <h3 class="font-display mb-3 text-xl font-semibold text-ink">Recargas</h3>
                <div class="space-y-2">
                    <Link
                        v-for="topup in topups"
                        :key="topup.id"
                        :href="`/parent/topups/${topup.id}`"
                        class="flex items-center justify-between rounded-[1.2rem] border border-line bg-white/75 px-4 py-3"
                    >
                        <div>
                            <p class="text-sm font-semibold text-ink">#{{ topup.code }} · {{ topupStatusMeta(topup.status).label }}</p>
                            <p class="mt-0.5 text-xs text-ink-soft/50">{{ topup.created_at }}</p>
                        </div>
                        <p class="text-sm font-semibold text-ink">{{ formatMoney(topup.amount) }}</p>
                    </Link>
                </div>
            </section>

            <section>
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="font-display text-xl font-semibold text-ink">Pedidos</h3>
                    <Link href="/parent/orders" class="text-xs font-semibold text-leaf-deep">Ver todos</Link>
                </div>
                <div v-if="orders.length" class="space-y-2">
                    <Link
                        v-for="order in orders"
                        :key="order.id"
                        :href="`/parent/orders/${order.id}`"
                        class="flex items-center justify-between rounded-[1.2rem] border border-line bg-white/75 px-4 py-3"
                    >
                        <div>
                            <p class="text-sm font-semibold text-ink">#{{ order.id }} · {{ orderStatusMeta(order.status).label }}</p>
                            <p class="mt-0.5 text-xs text-ink-soft/50">{{ order.created_at }}</p>
                        </div>
                        <p class="text-sm font-semibold text-ink">{{ formatMoney(order.total) }}</p>
                    </Link>
                </div>
                <p v-else class="rounded-[1.2rem] border border-dashed border-line px-4 py-6 text-center text-sm text-ink-soft/50">
                    Nenhum pedido ainda.
                </p>
            </section>

            <section>
                <h3 class="font-display mb-3 text-xl font-semibold text-ink">Extrato</h3>
                <div v-if="transactions.length" class="divide-y divide-line overflow-hidden rounded-[1.45rem] border border-line bg-white/70 px-4">
                    <div v-for="tx in transactions" :key="tx.id" class="flex items-center justify-between py-3.5">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-ink">{{ tx.description || tx.type }}</p>
                            <p class="text-[11px] text-ink-soft/45">{{ tx.created_at }}</p>
                        </div>
                        <p
                            class="shrink-0 text-sm font-semibold"
                            :class="tx.type === 'debit' ? 'text-bloom' : 'text-leaf-deep'"
                        >
                            {{ tx.type === 'debit' ? '−' : '+' }}{{ formatMoney(tx.amount) }}
                        </p>
                    </div>
                </div>
                <p v-else class="rounded-[1.2rem] border border-dashed border-line px-4 py-6 text-center text-sm text-ink-soft/50">
                    Sem movimentações na carteira.
                </p>
            </section>
        </section>
    </MobileShell>
</template>
