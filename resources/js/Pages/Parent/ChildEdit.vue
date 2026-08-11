<script setup>
import MobileShell from '@/Layouts/MobileShell.vue';
import BackLink from '@/Components/portal/BackLink.vue';
import { computed, ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    child: { type: Object, required: true },
    form: { type: Object, required: true },
    pin: { type: String, default: null },
    has_pin: { type: Boolean, default: false },
    schools: { type: Array, default: () => [] },
    relationshipTypes: { type: Array, default: () => [] },
    shifts: { type: Array, default: () => [] },
});

const showCurrentPin = ref(false);
const showNewPin = ref(false);

const form = useForm({
    name: props.form.name ?? '',
    school_id: props.form.school_id ?? '',
    birth_date: props.form.birth_date ?? '',
    grade: props.form.grade ?? '',
    classroom: props.form.classroom ?? '',
    shift: props.form.shift ?? '',
    relationship_type: props.form.relationship_type ?? 'Responsável',
    can_buy_on_tab: Boolean(props.form.can_buy_on_tab),
    personal_pin: '',
    personal_pin_confirmation: '',
});

const inputClass = 'h-12 w-full rounded-2xl border border-line bg-white/80 px-4 text-sm text-ink outline-none transition focus:border-leaf/40';
const labelClass = 'mb-1.5 block text-xs font-semibold uppercase tracking-[0.14em] text-ink-soft/45';

const pinLabel = computed(() => {
    if (props.pin) {
        return showCurrentPin.value ? props.pin : '••••';
    }

    return props.has_pin ? 'Cadastrado — defina um novo para visualizar' : 'Ainda não cadastrado';
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        birth_date: data.birth_date || null,
        grade: data.grade || null,
        classroom: data.classroom || null,
        shift: data.shift || null,
        can_buy_on_tab: data.can_buy_on_tab ? 1 : 0,
        personal_pin: data.personal_pin || null,
        personal_pin_confirmation: data.personal_pin ? data.personal_pin_confirmation : null,
    })).put(`/parent/children/${props.child.id}`);
};
</script>

<template>
    <Head :title="`Editar ${child.name}`" />

    <MobileShell role="parent">
        <section class="space-y-6">
            <div>
                <BackLink :href="`/parent/children/${child.id}`" :label="child.name" />
                <h2 class="font-display mt-4 text-[2rem] font-semibold leading-none tracking-tight text-ink">
                    Editar filho
                </h2>
                <p class="mt-2 text-sm text-ink-soft/60">
                    Atualize os dados, o fiado e o PIN usado nas compras.
                </p>
            </div>

            <form class="space-y-6" @submit.prevent="submit">
                <section class="space-y-3.5">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-ink-soft/40">Dados</p>

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
                </section>

                <section class="space-y-3.5">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-ink-soft/40">Permissões</p>

                    <label class="flex items-center justify-between gap-3 rounded-[1.35rem] border border-line bg-white/75 px-4 py-4">
                        <span>
                            <span class="block text-sm font-semibold text-ink">Pode comprar no fiado</span>
                            <span class="mt-0.5 block text-xs text-ink-soft/50">
                                Libera a conta de fiado e o pagamento com PIN.
                            </span>
                        </span>
                        <input
                            v-model="form.can_buy_on_tab"
                            type="checkbox"
                            class="size-5 rounded border-line text-leaf focus:ring-leaf/30"
                        >
                    </label>
                    <p v-if="form.errors.can_buy_on_tab" class="text-xs text-bloom">{{ form.errors.can_buy_on_tab }}</p>
                </section>

                <section class="space-y-3.5">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-ink-soft/40">PIN do aluno</p>

                    <article class="rounded-[1.35rem] border border-line bg-white/75 px-4 py-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-ink-soft/40">PIN atual</p>
                                <p class="mt-1 font-display text-2xl font-semibold tracking-[0.18em] text-ink">
                                    {{ pinLabel }}
                                </p>
                            </div>
                            <button
                                v-if="pin"
                                type="button"
                                class="flex size-10 items-center justify-center rounded-2xl bg-mist text-leaf-deep"
                                :aria-label="showCurrentPin ? 'Ocultar PIN' : 'Mostrar PIN'"
                                @click="showCurrentPin = !showCurrentPin"
                            >
                                <i :class="showCurrentPin ? 'pi pi-eye-slash' : 'pi pi-eye'" />
                            </button>
                        </div>
                    </article>

                    <div>
                        <label :class="labelClass">{{ has_pin ? 'Novo PIN' : 'Definir PIN' }}</label>
                        <div class="relative">
                            <input
                                v-model="form.personal_pin"
                                :type="showNewPin ? 'text' : 'password'"
                                inputmode="numeric"
                                maxlength="8"
                                autocomplete="one-time-code"
                                :class="inputClass"
                                placeholder="4 a 8 dígitos"
                            >
                            <button
                                type="button"
                                class="absolute inset-y-0 right-3 text-ink-soft/45"
                                @click="showNewPin = !showNewPin"
                            >
                                <i :class="showNewPin ? 'pi pi-eye-slash' : 'pi pi-eye'" />
                            </button>
                        </div>
                        <p v-if="form.errors.personal_pin" class="mt-1.5 text-xs text-bloom">{{ form.errors.personal_pin }}</p>
                    </div>
                    <div v-if="form.personal_pin">
                        <label :class="labelClass">Confirmar PIN</label>
                        <input
                            v-model="form.personal_pin_confirmation"
                            type="password"
                            inputmode="numeric"
                            maxlength="8"
                            :class="inputClass"
                        >
                        <p v-if="form.errors.personal_pin_confirmation" class="mt-1.5 text-xs text-bloom">
                            {{ form.errors.personal_pin_confirmation }}
                        </p>
                    </div>
                </section>

                <button
                    type="submit"
                    class="flex h-14 w-full items-center justify-center rounded-[1.2rem] bg-ink text-sm font-semibold text-foam disabled:opacity-50"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Salvando...' : 'Salvar alterações' }}
                </button>
            </form>
        </section>
    </MobileShell>
</template>
