<script setup>
import InviteShell from '@/Layouts/InviteShell.vue';
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    token: { type: String, required: true },
    tenant: { type: Object, required: true },
    schools: { type: Array, default: () => [] },
    expiresAt: { type: String, default: null },
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    phone: '',
    cpf: '',
    school_id: props.schools[0]?.id || null,
});

const inputClass = 'h-12 w-full rounded-2xl border border-line bg-white/80 px-4 text-sm text-ink outline-none transition focus:border-leaf/40';
const labelClass = 'mb-1.5 block text-xs font-semibold uppercase tracking-[0.14em] text-ink-soft/45';

const canSubmit = computed(() =>
    form.name.trim()
    && form.email.trim()
    && form.password.length >= 6
    && form.password === form.password_confirmation
    && form.cpf.replace(/\D/g, '').length === 11
    && form.school_id,
);

const submit = () => {
    form.post(`/invite/${props.token}`);
};
</script>

<template>
    <Head title="Cadastro de solicitante" />

    <InviteShell :tenant="tenant">
        <section class="space-y-6">
            <div>
                <p class="text-sm font-medium text-ink-soft/55">Área do solicitante</p>
                <h2 class="font-display mt-1 text-[2rem] font-semibold leading-[1.08] tracking-tight text-ink">
                    Peça na cantina<span class="text-leaf">.</span>
                </h2>
                <p class="mt-3 max-w-[34ch] text-sm leading-relaxed text-ink-soft/65">
                    Crie sua conta para pedir, usar carteira e fiado — sem cadastro de filhos.
                </p>
                <p v-if="expiresAt" class="mt-2 text-xs text-ink-soft/45">
                    Este convite vale até {{ expiresAt }}.
                </p>
            </div>

            <form class="space-y-3.5" @submit.prevent="submit">
                <div>
                    <label :class="labelClass">Nome</label>
                    <input v-model="form.name" type="text" autocomplete="name" required :class="inputClass">
                    <p v-if="form.errors.name" class="mt-1.5 text-xs text-bloom">{{ form.errors.name }}</p>
                </div>
                <div>
                    <label :class="labelClass">E-mail</label>
                    <input v-model="form.email" type="email" autocomplete="email" required :class="inputClass">
                    <p v-if="form.errors.email" class="mt-1.5 text-xs text-bloom">{{ form.errors.email }}</p>
                </div>
                <div>
                    <label :class="labelClass">CPF</label>
                    <input v-model="form.cpf" type="text" inputmode="numeric" required :class="inputClass">
                    <p v-if="form.errors.cpf" class="mt-1.5 text-xs text-bloom">{{ form.errors.cpf }}</p>
                </div>
                <div>
                    <label :class="labelClass">Telefone</label>
                    <input v-model="form.phone" type="tel" :class="inputClass">
                    <p v-if="form.errors.phone" class="mt-1.5 text-xs text-bloom">{{ form.errors.phone }}</p>
                </div>
                <div>
                    <label :class="labelClass">Escola / cantina</label>
                    <select v-model="form.school_id" required :class="inputClass">
                        <option v-for="school in schools" :key="school.id" :value="school.id">
                            {{ school.name }}
                        </option>
                    </select>
                    <p v-if="form.errors.school_id" class="mt-1.5 text-xs text-bloom">{{ form.errors.school_id }}</p>
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
                    {{ form.processing ? 'Criando...' : 'Criar conta' }}
                </button>
            </form>
        </section>
    </InviteShell>
</template>
