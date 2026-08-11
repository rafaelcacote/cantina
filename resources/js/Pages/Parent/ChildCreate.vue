<script setup>
import MobileShell from '@/Layouts/MobileShell.vue';
import BackLink from '@/Components/portal/BackLink.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    schools: { type: Array, default: () => [] },
    relationshipTypes: { type: Array, default: () => [] },
    shifts: { type: Array, default: () => [] },
});

const form = useForm({
    name: '',
    school_id: props.schools[0]?.id ?? '',
    birth_date: '',
    grade: '',
    classroom: '',
    shift: '',
    relationship_type: 'Responsável',
});

const inputClass = 'h-12 w-full rounded-2xl border border-line bg-white/80 px-4 text-sm text-ink outline-none transition focus:border-leaf/40';
const labelClass = 'mb-1.5 block text-xs font-semibold uppercase tracking-[0.14em] text-ink-soft/45';

const submit = () => {
    form.post('/parent/children');
};
</script>

<template>
    <Head title="Cadastrar filho" />

    <MobileShell role="parent">
        <section class="space-y-6">
            <div>
                <BackLink href="/parent/children" label="Filhos" />
                <h2 class="font-display mt-4 text-[2rem] font-semibold leading-none tracking-tight text-ink">
                    Novo filho
                </h2>
                <p class="mt-2 text-sm text-ink-soft/60">
                    O cadastro fica pendente até a cantina confirmar.
                </p>
            </div>

            <form class="space-y-3.5" @submit.prevent="submit">
                <div>
                    <label :class="labelClass">Nome</label>
                    <input v-model="form.name" type="text" required :class="inputClass">
                    <p v-if="form.errors.name" class="mt-1.5 text-xs text-bloom">{{ form.errors.name }}</p>
                </div>
                <div>
                    <label :class="labelClass">Escola</label>
                    <select v-model="form.school_id" required :class="inputClass">
                        <option disabled value="">Selecione</option>
                        <option v-for="school in schools" :key="school.id" :value="school.id">
                            {{ school.name }}
                        </option>
                    </select>
                    <p v-if="form.errors.school_id" class="mt-1.5 text-xs text-bloom">{{ form.errors.school_id }}</p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label :class="labelClass">Nascimento</label>
                        <input v-model="form.birth_date" type="date" :class="inputClass">
                    </div>
                    <div>
                        <label :class="labelClass">Parentesco</label>
                        <select v-model="form.relationship_type" :class="inputClass">
                            <option v-for="type in relationshipTypes" :key="type" :value="type">{{ type }}</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label :class="labelClass">Série</label>
                        <input v-model="form.grade" type="text" :class="inputClass">
                    </div>
                    <div>
                        <label :class="labelClass">Turma</label>
                        <input v-model="form.classroom" type="text" :class="inputClass">
                    </div>
                    <div>
                        <label :class="labelClass">Turno</label>
                        <select v-model="form.shift" :class="inputClass">
                            <option value="">—</option>
                            <option v-for="shift in shifts" :key="shift" :value="shift">{{ shift }}</option>
                        </select>
                    </div>
                </div>

                <button
                    type="submit"
                    class="flex h-14 w-full items-center justify-center rounded-[1.2rem] bg-ink text-sm font-semibold text-foam disabled:opacity-50"
                    :disabled="form.processing || !schools.length"
                >
                    {{ form.processing ? 'Salvando...' : 'Cadastrar' }}
                </button>
            </form>
        </section>
    </MobileShell>
</template>
