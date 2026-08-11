<script setup>
import InviteShell from '@/Layouts/InviteShell.vue';
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    token: { type: String, required: true },
    tenant: { type: Object, required: true },
    studentName: { type: String, required: true },
    expiresAt: { type: String, default: null },
});

const form = useForm({
    email: '',
    password: '',
    password_confirmation: '',
});

const inputClass = 'h-12 w-full rounded-2xl border border-line bg-white/80 px-4 text-sm text-ink outline-none transition focus:border-leaf/40';
const labelClass = 'mb-1.5 block text-xs font-semibold uppercase tracking-[0.14em] text-ink-soft/45';

const canSubmit = computed(() =>
    form.email.trim()
    && form.password.length >= 6
    && form.password === form.password_confirmation,
);

const submit = () => {
    form.post(`/aluno/convite/${props.token}`);
};
</script>

<template>
    <Head title="Criar acesso" />

    <InviteShell :tenant="tenant">
        <section class="space-y-6">
            <div>
                <p class="text-sm font-medium text-ink-soft/55">Área do aluno</p>
                <h2 class="font-display mt-1 text-[2rem] font-semibold leading-[1.08] tracking-tight text-ink">
                    Olá, {{ studentName.split(' ')[0] }}<span class="text-leaf">.</span>
                </h2>
                <p class="mt-3 max-w-[34ch] text-sm leading-relaxed text-ink-soft/65">
                    Crie seu e-mail e senha para pedir na cantina.
                </p>
                <p v-if="expiresAt" class="mt-2 text-xs text-ink-soft/45">
                    Este convite vale até {{ expiresAt }}.
                </p>
            </div>

            <form class="space-y-3.5" @submit.prevent="submit">
                <div>
                    <label :class="labelClass">E-mail</label>
                    <input v-model="form.email" type="email" autocomplete="email" required :class="inputClass">
                    <p v-if="form.errors.email" class="mt-1.5 text-xs text-bloom">{{ form.errors.email }}</p>
                </div>
                <div>
                    <label :class="labelClass">Senha</label>
                    <input v-model="form.password" type="password" autocomplete="new-password" required :class="inputClass">
                    <p v-if="form.errors.password" class="mt-1.5 text-xs text-bloom">{{ form.errors.password }}</p>
                </div>
                <div>
                    <label :class="labelClass">Confirmar senha</label>
                    <input v-model="form.password_confirmation" type="password" autocomplete="new-password" required :class="inputClass">
                </div>

                <button
                    type="submit"
                    class="flex h-14 w-full items-center justify-center rounded-[1.2rem] bg-ink text-sm font-semibold text-foam disabled:opacity-40"
                    :disabled="form.processing || !canSubmit"
                >
                    {{ form.processing ? 'Criando...' : 'Entrar no app' }}
                </button>
            </form>
        </section>
    </InviteShell>
</template>
