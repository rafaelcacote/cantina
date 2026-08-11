<script setup>
import MobileShell from '@/Layouts/MobileShell.vue';
import BackLink from '@/Components/portal/BackLink.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { formatMoney } from '@/composables/useFormat';

const props = defineProps({
    child: { type: Object, required: true },
    pixKey: { type: String, required: true },
});

const form = useForm({
    amount: '',
});

const inputClass = 'h-14 w-full rounded-2xl border border-line bg-white/80 px-4 text-lg font-semibold text-ink outline-none transition focus:border-leaf/40';

const submit = () => {
    form.post(`/parent/children/${props.child.id}/topups`);
};
</script>

<template>
    <Head title="Depositar" />

    <MobileShell role="parent">
        <section class="space-y-6">
            <div>
                <BackLink :href="`/parent/children/${child.id}`" :label="child.name" />
                <h2 class="font-display mt-4 text-[2rem] font-semibold leading-none tracking-tight text-ink">
                    Depositar
                </h2>
                <p class="mt-2 text-sm leading-relaxed text-ink-soft/60">
                    Informe o valor. Depois você copia a chave Pix da cantina e envia o comprovante.
                </p>
            </div>

            <article class="rounded-[1.45rem] border border-line bg-white/80 px-4 py-4">
                <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-ink-soft/40">Carteira de</p>
                <p class="mt-1 font-display text-xl font-semibold text-ink">{{ child.name }}</p>
                <p class="mt-1 text-sm text-ink-soft/55">Saldo atual {{ formatMoney(child.balance) }}</p>
            </article>

            <form class="space-y-4" @submit.prevent="submit">
                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.14em] text-ink-soft/45">
                        Valor
                    </label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-sm font-semibold text-ink-soft/45">
                            R$
                        </span>
                        <input
                            v-model="form.amount"
                            type="number"
                            min="1"
                            max="1000"
                            step="0.01"
                            inputmode="decimal"
                            placeholder="0,00"
                            required
                            :class="inputClass + ' pl-12'"
                        >
                    </div>
                    <p v-if="form.errors.amount" class="mt-1.5 text-xs text-bloom">{{ form.errors.amount }}</p>
                </div>

                <p class="text-xs leading-relaxed text-ink-soft/50">
                    A chave Pix da cantina aparece no próximo passo. O saldo só entra depois da conferência.
                </p>

                <button
                    type="submit"
                    class="flex h-14 w-full items-center justify-center rounded-[1.2rem] bg-ink text-sm font-semibold text-foam disabled:opacity-50"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Criando...' : 'Continuar' }}
                </button>
            </form>
        </section>
    </MobileShell>
</template>
