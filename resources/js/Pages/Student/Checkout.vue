<script setup>
import MobileShell from '@/Layouts/MobileShell.vue';
import ConfirmSheet from '@/Components/portal/ConfirmSheet.vue';
import BackLink from '@/Components/portal/BackLink.vue';
import { computed, onMounted, ref } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Message from 'primevue/message';
import Textarea from 'primevue/textarea';
import { formatMoney, paymentLabel } from '@/composables/useFormat';

const props = defineProps({
    walletBalance: { type: Number, default: 0 },
    paymentOptions: { type: Array, default: () => [] },
});

const page = usePage();
const cart = ref([]);
const confirmOpen = ref(false);

const form = useForm({
    items: [],
    payment_mode: props.paymentOptions[0]?.value || 'wallet',
    notes: '',
    student_pin: '',
});

const total = computed(() =>
    cart.value.reduce((sum, item) => sum + Number(item.price) * Number(item.quantity), 0),
);

const needsPin = computed(() => form.payment_mode === 'tab');
const firstError = computed(() => Object.values(page.props.errors || {})[0] || null);
const selectedPayment = computed(() =>
    props.paymentOptions.find((option) => option.value === form.payment_mode),
);

const paymentIcon = (mode) => ({
    wallet: 'pi pi-wallet',
    cash: 'pi pi-money-bill',
    tab: 'pi pi-calendar',
    pix: 'pi pi-qrcode',
    card: 'pi pi-credit-card',
})[mode] || 'pi pi-circle';

onMounted(() => {
    try {
        cart.value = JSON.parse(sessionStorage.getItem('student-cart') || '[]');
    } catch {
        cart.value = [];
    }

    if (!cart.value.length) {
        router.visit('/student/menu');
        return;
    }

    form.items = cart.value.map((item) => ({
        product_id: item.product_id,
        quantity: item.quantity,
    }));
});

const openConfirm = () => {
    if (!cart.value.length || form.processing) {
        return;
    }

    confirmOpen.value = true;
};

const submit = () => {
    form.post('/student/orders', {
        preserveScroll: true,
        onSuccess: () => {
            sessionStorage.removeItem('student-cart');
        },
        onFinish: () => {
            confirmOpen.value = false;
        },
    });
};
</script>

<template>
    <Head title="Confirmar pedido" />

    <MobileShell role="student">
        <section class="space-y-7 pb-28">
            <div>
                <BackLink href="/student/menu" label="Cardápio" />
                <div class="mt-5 flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-ink-soft/40">
                            Última etapa
                        </p>
                        <h2 class="font-display mt-1 text-[2rem] font-semibold leading-none tracking-tight text-ink">
                            Tudo certo?
                        </h2>
                    </div>
                    <span class="flex size-11 items-center justify-center rounded-2xl bg-zest/70 text-ink">
                        <i class="pi pi-shopping-bag text-sm" />
                    </span>
                </div>
                <p class="mt-3 max-w-[32ch] text-sm leading-relaxed text-ink-soft/60">
                    Revise os itens, escolha como pagar e envie para a cantina.
                </p>
            </div>

            <Message v-if="firstError" severity="error" :closable="false">
                {{ firstError }}
            </Message>

            <article class="overflow-hidden rounded-[1.55rem] border border-line bg-white/70 backdrop-blur">
                <div class="flex items-center justify-between border-b border-line px-4 py-3.5">
                    <h3 class="font-display text-lg font-semibold text-ink">Sua sacola</h3>
                    <span class="rounded-full bg-mist px-2.5 py-1 text-[10px] font-semibold text-ink-soft/55">
                        {{ cart.length }} {{ cart.length === 1 ? 'produto' : 'produtos' }}
                    </span>
                </div>
                <ul class="divide-y divide-line px-4">
                    <li
                        v-for="item in cart"
                        :key="item.product_id"
                        class="flex items-center justify-between gap-3 py-3"
                    >
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="flex size-8 shrink-0 items-center justify-center rounded-xl bg-mist text-xs font-bold text-leaf-deep">
                                {{ item.quantity }}×
                            </span>
                            <div class="min-w-0">
                            <p class="truncate font-medium text-ink">{{ item.name }}</p>
                                <p class="text-xs text-ink-soft/45">{{ formatMoney(item.price) }} cada</p>
                            </div>
                        </div>
                        <p class="shrink-0 font-semibold text-ink">
                            {{ formatMoney(item.price * item.quantity) }}
                        </p>
                    </li>
                </ul>
                <div class="flex items-center justify-between bg-ink px-4 py-4 text-foam">
                    <span class="text-sm text-foam/55">Total do pedido</span>
                    <span class="font-display text-xl font-semibold text-zest">{{ formatMoney(total) }}</span>
                </div>
            </article>

            <fieldset class="space-y-3">
                <legend class="font-display text-xl font-semibold text-ink">
                    Como quer pagar?
                </legend>
                <label
                    v-for="option in paymentOptions"
                    :key="option.value"
                    class="flex cursor-pointer items-center gap-3 rounded-[1.35rem] border px-3.5 py-3 transition"
                    :class="form.payment_mode === option.value
                        ? 'border-leaf/25 bg-white text-ink shadow-[0_12px_30px_rgba(61,122,95,0.1)] ring-1 ring-leaf/15'
                        : 'portal-card text-ink'"
                >
                    <input
                        v-model="form.payment_mode"
                        type="radio"
                        name="payment_mode"
                        :value="option.value"
                        class="sr-only"
                    >
                    <span
                        class="flex size-11 shrink-0 items-center justify-center rounded-2xl transition"
                        :class="form.payment_mode === option.value
                            ? 'bg-ink text-zest'
                            : 'bg-mist text-leaf-deep'"
                    >
                        <i :class="paymentIcon(option.value)" class="text-sm" />
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-sm font-semibold">{{ option.label }}</span>
                        <span class="mt-0.5 block truncate text-xs text-ink-soft/50">
                            {{ option.value === 'wallet'
                                ? `${option.hint} · saldo ${formatMoney(walletBalance)}`
                                : option.hint }}
                        </span>
                    </span>
                    <span
                        class="flex size-5 shrink-0 items-center justify-center rounded-full border"
                        :class="form.payment_mode === option.value
                            ? 'border-leaf bg-leaf text-white'
                            : 'border-line bg-white'"
                    >
                        <i v-if="form.payment_mode === option.value" class="pi pi-check text-[8px]" />
                    </span>
                </label>
            </fieldset>

            <div v-if="needsPin" class="space-y-1.5">
                <label class="block text-sm font-medium text-ink" for="student_pin">PIN do fiado</label>
                <InputText
                    id="student_pin"
                    v-model="form.student_pin"
                    type="password"
                    inputmode="numeric"
                    maxlength="20"
                    autocomplete="one-time-code"
                    class="w-full"
                    placeholder="Digite o PIN"
                />
            </div>

            <div class="space-y-2">
                <label class="font-display block text-lg font-semibold text-ink" for="notes">Alguma observação?</label>
                <Textarea
                    id="notes"
                    v-model="form.notes"
                    rows="3"
                    maxlength="500"
                    auto-resize
                    class="w-full"
                    placeholder="Ex.: sem cebola, deixar na mesa 2..."
                />
            </div>
        </section>

        <div class="fixed inset-x-0 bottom-[calc(5.75rem+env(safe-area-inset-bottom))] z-30 px-4">
            <Button
                class="mx-auto flex w-full max-w-lg !justify-between !rounded-[1.45rem] !border-white/10 !bg-ink !px-5 !py-3.5 !text-foam shadow-[0_18px_42px_rgba(20,36,31,0.3)]"
                :disabled="form.processing || !cart.length"
                @click="openConfirm"
            >
                <span>
                    <span class="block text-left text-[10px] font-semibold uppercase tracking-[0.15em] text-foam/45">Confirmar</span>
                    <span class="font-display text-lg font-semibold">Enviar pedido</span>
                </span>
                <span class="flex items-center gap-3">
                    <span class="font-display text-lg font-semibold text-zest">{{ formatMoney(total) }}</span>
                    <span class="flex size-8 items-center justify-center rounded-full bg-zest text-ink">
                        <i class="pi pi-arrow-right text-[11px]" />
                    </span>
                </span>
            </Button>
        </div>

        <ConfirmSheet
            v-model:visible="confirmOpen"
            title="Enviar para a cantina?"
            :message="`O pedido será pago com ${paymentLabel(form.payment_mode).toLowerCase()}.`"
            confirm-label="Enviar agora"
            icon="pi pi-send"
            :loading="form.processing"
            @confirm="submit"
        >
            <div class="mt-4 rounded-2xl bg-mist/70 px-3 py-3 text-sm">
                <p class="flex items-center justify-between">
                    <span class="text-ink-soft/65">Itens</span>
                    <span class="font-semibold text-ink">{{ cart.length }}</span>
                </p>
                <p class="mt-1.5 flex items-center justify-between">
                    <span class="text-ink-soft/65">Pagamento</span>
                    <span class="font-semibold text-ink">{{ selectedPayment?.label }}</span>
                </p>
                <p class="mt-1.5 flex items-center justify-between">
                    <span class="text-ink-soft/65">Total</span>
                    <span class="font-display font-semibold text-ink">{{ formatMoney(total) }}</span>
                </p>
            </div>
        </ConfirmSheet>
    </MobileShell>
</template>
