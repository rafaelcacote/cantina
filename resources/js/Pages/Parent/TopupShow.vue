<script setup>
import MobileShell from '@/Layouts/MobileShell.vue';
import BackLink from '@/Components/portal/BackLink.vue';
import { computed, ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { formatMoney, topupStatusMeta } from '@/composables/useFormat';

const props = defineProps({
    topup: { type: Object, required: true },
});

const copied = ref('');
const fileInput = ref(null);
const status = computed(() => topupStatusMeta(props.topup.status));

const form = useForm({
    receipt: null,
});

const copy = async (value, key) => {
    try {
        await navigator.clipboard.writeText(value);
        copied.value = key;
        window.setTimeout(() => {
            if (copied.value === key) {
                copied.value = '';
            }
        }, 2000);
    } catch {
        copied.value = '';
    }
};

const pickFile = (event) => {
    const [file] = event.target.files || [];
    form.receipt = file || null;
};

const submitReceipt = () => {
    form.post(`/parent/topups/${props.topup.id}/receipt`, {
        forceFormData: true,
    });
};
</script>

<template>
    <Head :title="`Recarga #${topup.code}`" />

    <MobileShell role="parent">
        <section class="space-y-6">
            <div>
                <BackLink :href="`/parent/children/${topup.child.id}`" :label="topup.child.name" />
            </div>

            <article class="relative overflow-hidden rounded-[1.7rem] bg-ink px-5 py-5 text-foam shadow-[0_20px_48px_rgba(20,36,31,0.22)]">
                <div class="pointer-events-none absolute -right-10 -top-12 size-40 rounded-full border-[28px] border-zest/10" />
                <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-foam/40">
                    Recarga #{{ topup.code }}
                </p>
                <h2 class="font-display mt-2 text-[1.7rem] font-semibold leading-tight">
                    {{ formatMoney(topup.amount) }}
                </h2>
                <p class="mt-2 text-xs text-foam/50">{{ topup.child.name }} · {{ status.hint }}</p>
                <p class="mt-4 inline-flex rounded-full bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-wide">
                    {{ status.label }}
                </p>
            </article>

            <article class="space-y-3 rounded-[1.45rem] border border-line bg-white/80 p-4">
                <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-ink-soft/40">Pague com Pix</p>
                <p class="text-sm leading-relaxed text-ink-soft/65">
                    Use a chave da cantina e coloque o código
                    <strong class="text-ink">#{{ topup.code }}</strong>
                    na descrição do Pix.
                </p>

                <div class="rounded-2xl bg-mist px-4 py-3">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-ink-soft/40">Chave Pix</p>
                    <p class="mt-1 break-all text-sm font-semibold text-ink">{{ topup.pix_key }}</p>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <button
                        type="button"
                        class="h-12 rounded-2xl bg-ink text-sm font-semibold text-foam"
                        @click="copy(topup.pix_key, 'pix')"
                    >
                        {{ copied === 'pix' ? 'Copiada!' : 'Copiar chave' }}
                    </button>
                    <button
                        type="button"
                        class="h-12 rounded-2xl border border-line bg-white text-sm font-semibold text-ink"
                        @click="copy(topup.code, 'code')"
                    >
                        {{ copied === 'code' ? 'Copiado!' : 'Copiar código' }}
                    </button>
                </div>

                <a
                    :href="topup.whatsapp_url"
                    target="_blank"
                    rel="noopener"
                    class="flex h-12 items-center justify-center rounded-2xl border border-line bg-white text-sm font-semibold text-leaf-deep"
                >
                    Enviar no WhatsApp
                </a>
            </article>

            <article v-if="topup.status === 'rejected'" class="rounded-[1.45rem] border border-bloom/30 bg-bloom/10 px-4 py-4">
                <p class="text-sm font-semibold text-ink">Recarga recusada</p>
                <p class="mt-1 text-sm text-ink-soft/70">{{ topup.rejection_reason || 'A cantina não conseguiu confirmar o pagamento.' }}</p>
            </article>

            <article v-if="topup.can_upload" class="space-y-3 rounded-[1.45rem] border border-line bg-white/80 p-4">
                <p class="font-display text-lg font-semibold text-ink">Comprovante</p>
                <p class="text-sm text-ink-soft/60">
                    Depois de pagar, envie a foto por aqui. A cantina confere e libera o saldo.
                </p>

                <input
                    ref="fileInput"
                    type="file"
                    accept="image/*"
                    capture="environment"
                    class="hidden"
                    @change="pickFile"
                >

                <button
                    type="button"
                    class="flex h-12 w-full items-center justify-center rounded-2xl border border-dashed border-line text-sm font-semibold text-ink"
                    @click="fileInput?.click()"
                >
                    {{ form.receipt ? form.receipt.name : 'Escolher foto' }}
                </button>
                <p v-if="form.errors.receipt" class="text-xs text-bloom">{{ form.errors.receipt }}</p>

                <button
                    type="button"
                    class="flex h-12 w-full items-center justify-center rounded-2xl bg-ink text-sm font-semibold text-foam disabled:opacity-40"
                    :disabled="form.processing || !form.receipt"
                    @click="submitReceipt"
                >
                    {{ form.processing ? 'Enviando...' : 'Enviar comprovante' }}
                </button>
            </article>

            <article v-if="topup.receipt_url" class="overflow-hidden rounded-[1.45rem] border border-line bg-white/80">
                <div class="px-4 py-3">
                    <p class="text-sm font-semibold text-ink">Comprovante enviado</p>
                </div>
                <a :href="topup.receipt_url" target="_blank" rel="noopener" class="block bg-mist">
                    <img :src="topup.receipt_url" alt="Comprovante" class="mx-auto max-h-96 w-full object-contain">
                </a>
            </article>
        </section>
    </MobileShell>
</template>
