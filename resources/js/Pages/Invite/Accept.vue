<script setup>
import InviteShell from '@/Layouts/InviteShell.vue';
import { computed, ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { cpfDigits, formatCpf, isValidCpf } from '@/composables/useCpf';
import { formatPhone, isValidPhone, phoneDigits } from '@/composables/usePhone';

const props = defineProps({
    token: { type: String, required: true },
    tenant: { type: Object, required: true },
    schools: { type: Array, default: () => [] },
    relationshipTypes: { type: Array, default: () => [] },
    shifts: { type: Array, default: () => [] },
    expiresAt: { type: String, default: null },
});

const step = ref(1);
const draftChild = ref(emptyChild());

const form = useForm({
    name: '',
    email: '',
    phone: '',
    cpf: '',
    password: '',
    password_confirmation: '',
    children: [],
});

const steps = [
    { id: 1, label: 'Sua conta' },
    { id: 2, label: 'Filhos' },
    { id: 3, label: 'Confirmar' },
];

const inputClass = 'h-12 w-full rounded-2xl border border-line bg-white/80 px-4 text-sm text-ink outline-none transition focus:border-leaf/40';
const labelClass = 'mb-1.5 block text-xs font-semibold uppercase tracking-[0.14em] text-ink-soft/45';

function emptyChild() {
    return {
        name: '',
        school_id: props.schools[0]?.id ?? '',
        birth_date: '',
        grade: '',
        classroom: '',
        shift: '',
        relationship_type: 'Responsável',
    };
}

const schoolName = (id) => props.schools.find((school) => Number(school.id) === Number(id))?.name || 'Escola';

const cpfMasked = computed({
    get: () => formatCpf(form.cpf),
    set: (value) => {
        form.cpf = cpfDigits(value);
    },
});

const phoneMasked = computed({
    get: () => formatPhone(form.phone),
    set: (value) => {
        form.phone = phoneDigits(value);
    },
});

const phoneHint = computed(() => {
    const digits = phoneDigits(form.phone);

    if (!digits.length) {
        return '';
    }

    if (!isValidPhone(digits)) {
        return 'Digite o DDD e o número.';
    }

    return '';
});

const cpfHint = computed(() => {
    const digits = cpfDigits(form.cpf);

    if (!digits.length) {
        return '';
    }

    if (digits.length < 11) {
        return 'Digite os 11 números do CPF.';
    }

    if (!isValidCpf(digits)) {
        return 'CPF inválido.';
    }

    return '';
});

const canGoAccount = computed(() =>
    form.name.trim()
    && form.email.trim()
    && isValidCpf(form.cpf)
    && (!form.phone || isValidPhone(form.phone))
    && form.password.length >= 6
    && form.password === form.password_confirmation,
);

const canAddChild = computed(() => draftChild.value.name.trim() && draftChild.value.school_id);

const fieldError = (key) => form.errors[key];

const goAccount = () => {
    if (!canGoAccount.value) {
        return;
    }
    step.value = 2;
};

const addChild = () => {
    if (!canAddChild.value) {
        return;
    }

    form.children.push({ ...draftChild.value });
    draftChild.value = emptyChild();
};

const removeChild = (index) => {
    form.children.splice(index, 1);
};

const goReview = () => {
    if (canAddChild.value) {
        addChild();
    }

    if (!form.children.length) {
        return;
    }

    step.value = 3;
};

const submit = () => {
    form.post(`/invite/${props.token}`, {
        preserveScroll: true,
        onError: () => {
            if (form.errors.name || form.errors.email || form.errors.password || form.errors.phone || form.errors.cpf) {
                step.value = 1;
                return;
            }

            if (Object.keys(form.errors).some((key) => key.startsWith('children'))) {
                step.value = 2;
            }
        },
    });
};
</script>

<template>
    <Head title="Criar acesso" />

    <InviteShell :tenant="tenant">
        <section class="space-y-6">
            <div>
                <p class="text-sm font-medium text-ink-soft/55">Bem-vindo à cantina</p>
                <h2 class="font-display mt-1 text-[2rem] font-semibold leading-[1.08] tracking-tight text-ink">
                    Crie seu acesso<span class="text-leaf">.</span>
                </h2>
                <p class="mt-3 max-w-[34ch] text-sm leading-relaxed text-ink-soft/65">
                    Em seguida, cadastre os filhos que você vai acompanhar.
                </p>
                <p v-if="expiresAt" class="mt-2 text-xs text-ink-soft/45">
                    Este convite vale até {{ expiresAt }}.
                </p>
            </div>

            <ol class="grid grid-cols-3 gap-2">
                <li
                    v-for="item in steps"
                    :key="item.id"
                    class="rounded-full px-3 py-1.5 text-center text-[11px] font-semibold"
                    :class="step === item.id
                        ? 'bg-ink text-foam'
                        : step > item.id
                            ? 'bg-leaf/15 text-leaf-deep'
                            : 'bg-white/70 text-ink-soft/45'"
                >
                    {{ item.label }}
                </li>
            </ol>

            <p v-if="!schools.length" class="rounded-2xl border border-bloom/30 bg-bloom/10 px-4 py-3 text-sm text-ink">
                A cantina ainda não cadastrou escolas. Peça para liberar antes de continuar.
            </p>

            <form class="space-y-5" @submit.prevent="step === 3 ? submit() : null">
                <div v-if="step === 1" class="space-y-3.5">
                    <div>
                        <label :class="labelClass">Seu nome</label>
                        <input v-model="form.name" type="text" autocomplete="name" :class="inputClass">
                        <p v-if="fieldError('name')" class="mt-1.5 text-xs text-bloom">{{ fieldError('name') }}</p>
                    </div>
                    <div>
                        <label :class="labelClass">E-mail</label>
                        <input v-model="form.email" type="email" autocomplete="email" :class="inputClass">
                        <p v-if="fieldError('email')" class="mt-1.5 text-xs text-bloom">{{ fieldError('email') }}</p>
                    </div>
                    <div>
                        <label :class="labelClass">CPF</label>
                        <input
                            v-model="cpfMasked"
                            type="text"
                            inputmode="numeric"
                            autocomplete="off"
                            maxlength="14"
                            placeholder="000.000.000-00"
                            :class="inputClass"
                        >
                        <p v-if="fieldError('cpf')" class="mt-1.5 text-xs text-bloom">{{ fieldError('cpf') }}</p>
                        <p v-else-if="cpfHint" class="mt-1.5 text-xs text-bloom">{{ cpfHint }}</p>
                    </div>
                    <div>
                        <label :class="labelClass">Telefone</label>
                        <input
                            v-model="phoneMasked"
                            type="tel"
                            inputmode="numeric"
                            autocomplete="tel"
                            maxlength="15"
                            placeholder="(00) 00000-0000"
                            :class="inputClass"
                        >
                        <p v-if="fieldError('phone')" class="mt-1.5 text-xs text-bloom">{{ fieldError('phone') }}</p>
                        <p v-else-if="phoneHint" class="mt-1.5 text-xs text-bloom">{{ phoneHint }}</p>
                    </div>
                    <div>
                        <label :class="labelClass">Senha</label>
                        <input v-model="form.password" type="password" autocomplete="new-password" :class="inputClass">
                        <p v-if="fieldError('password')" class="mt-1.5 text-xs text-bloom">{{ fieldError('password') }}</p>
                    </div>
                    <div>
                        <label :class="labelClass">Confirmar senha</label>
                        <input v-model="form.password_confirmation" type="password" autocomplete="new-password" :class="inputClass">
                    </div>

                    <button
                        type="button"
                        class="flex h-14 w-full items-center justify-center rounded-[1.2rem] bg-ink text-sm font-semibold text-foam disabled:opacity-40"
                        :disabled="!canGoAccount"
                        @click="goAccount"
                    >
                        Continuar
                    </button>
                </div>

                <div v-else-if="step === 2" class="space-y-4">
                    <div v-if="form.children.length" class="space-y-2">
                        <article
                            v-for="(child, index) in form.children"
                            :key="`${child.name}-${index}`"
                            class="flex items-center gap-3 rounded-[1.25rem] border border-line bg-white/80 p-3.5"
                        >
                            <div class="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-mist font-display font-semibold text-leaf-deep">
                                {{ child.name.charAt(0).toUpperCase() }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-ink">{{ child.name }}</p>
                                <p class="mt-0.5 truncate text-xs text-ink-soft/55">
                                    {{ schoolName(child.school_id) }}
                                    <span v-if="child.relationship_type"> · {{ child.relationship_type }}</span>
                                </p>
                            </div>
                            <button type="button" class="text-xs font-semibold text-bloom" @click="removeChild(index)">
                                Remover
                            </button>
                        </article>
                    </div>

                    <div class="rounded-[1.45rem] border border-line bg-white/75 p-4">
                        <p class="font-display text-lg font-semibold text-ink">
                            {{ form.children.length ? 'Outro filho' : 'Primeiro filho' }}
                        </p>
                        <div class="mt-4 space-y-3">
                            <div>
                                <label :class="labelClass">Nome do aluno</label>
                                <input v-model="draftChild.name" type="text" :class="inputClass">
                            </div>
                            <div>
                                <label :class="labelClass">Escola</label>
                                <select v-model="draftChild.school_id" :class="inputClass">
                                    <option disabled value="">Selecione</option>
                                    <option v-for="school in schools" :key="school.id" :value="school.id">
                                        {{ school.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label :class="labelClass">Nascimento</label>
                                    <input v-model="draftChild.birth_date" type="date" :class="inputClass">
                                </div>
                                <div>
                                    <label :class="labelClass">Parentesco</label>
                                    <select v-model="draftChild.relationship_type" :class="inputClass">
                                        <option v-for="type in relationshipTypes" :key="type" :value="type">
                                            {{ type }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="grid grid-cols-3 gap-2">
                                <div>
                                    <label :class="labelClass">Série</label>
                                    <input v-model="draftChild.grade" type="text" :class="inputClass">
                                </div>
                                <div>
                                    <label :class="labelClass">Turma</label>
                                    <input v-model="draftChild.classroom" type="text" :class="inputClass">
                                </div>
                                <div>
                                    <label :class="labelClass">Turno</label>
                                    <select v-model="draftChild.shift" :class="inputClass">
                                        <option value="">—</option>
                                        <option v-for="shift in shifts" :key="shift" :value="shift">
                                            {{ shift }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="mt-4 w-full rounded-2xl border border-dashed border-line py-3 text-sm font-semibold text-leaf-deep disabled:opacity-40"
                            :disabled="!canAddChild"
                            @click="addChild"
                        >
                            Adicionar à lista
                        </button>
                    </div>

                    <p v-if="fieldError('children')" class="text-xs text-bloom">{{ fieldError('children') }}</p>

                    <div class="grid grid-cols-2 gap-2">
                        <button
                            type="button"
                            class="h-14 rounded-[1.2rem] border border-line bg-white/70 text-sm font-semibold text-ink"
                            @click="step = 1"
                        >
                            Voltar
                        </button>
                        <button
                            type="button"
                            class="h-14 rounded-[1.2rem] bg-ink text-sm font-semibold text-foam disabled:opacity-40"
                            :disabled="!form.children.length && !canAddChild"
                            @click="goReview"
                        >
                            Revisar
                        </button>
                    </div>
                </div>

                <div v-else class="space-y-4">
                    <article class="rounded-[1.45rem] border border-line bg-white/80 p-4">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-ink-soft/40">Responsável</p>
                        <p class="mt-2 font-display text-xl font-semibold text-ink">{{ form.name }}</p>
                        <p class="mt-1 text-sm text-ink-soft/60">{{ form.email }}</p>
                        <p v-if="form.phone" class="mt-1 text-sm text-ink-soft/60">{{ formatPhone(form.phone) }}</p>
                    </article>

                    <article class="rounded-[1.45rem] border border-line bg-white/80 p-4">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-ink-soft/40">Filhos</p>
                        <ul class="mt-3 divide-y divide-line">
                            <li v-for="(child, index) in form.children" :key="index" class="py-2.5">
                                <p class="text-sm font-semibold text-ink">{{ child.name }}</p>
                                <p class="text-xs text-ink-soft/55">{{ schoolName(child.school_id) }}</p>
                            </li>
                        </ul>
                    </article>

                    <p class="text-xs leading-relaxed text-ink-soft/55">
                        Os alunos entram como pendentes. A cantina confirma o cadastro antes das compras.
                    </p>

                    <div class="grid grid-cols-2 gap-2">
                        <button
                            type="button"
                            class="h-14 rounded-[1.2rem] border border-line bg-white/70 text-sm font-semibold text-ink"
                            @click="step = 2"
                        >
                            Voltar
                        </button>
                        <button
                            type="submit"
                            class="h-14 rounded-[1.2rem] bg-ink text-sm font-semibold text-foam disabled:opacity-50"
                            :disabled="form.processing || !schools.length"
                        >
                            {{ form.processing ? 'Criando...' : 'Criar conta' }}
                        </button>
                    </div>
                </div>
            </form>
        </section>
    </InviteShell>
</template>
