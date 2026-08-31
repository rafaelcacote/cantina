<script setup>
import MobileShell from '@/Layouts/MobileShell.vue';
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    schools: { type: Array, default: () => [] },
});

const form = useForm({
    school_id: props.schools[0]?.id || null,
});

const canSubmit = computed(() => !!form.school_id);

const submit = () => {
    form.post('/parent/self');
};
</script>

<template>
    <Head title="Pedir para mim" />

    <MobileShell role="parent">
        <section class="space-y-7">
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-ink-soft/40">Para você</p>
                <h2 class="font-display mt-1 text-[2rem] font-semibold leading-none tracking-tight text-ink">
                    Pedir para mim<span class="text-leaf">.</span>
                </h2>
                <p class="mt-3 max-w-[34ch] text-sm leading-relaxed text-ink-soft/65">
                    Escolha a escola/cantina onde você vai retirar o pedido. Vamos criar sua carteira e fiado.
                </p>
            </div>

            <form class="space-y-4" @submit.prevent="submit">
                <div class="space-y-2">
                    <button
                        v-for="school in schools"
                        :key="school.id"
                        type="button"
                        class="flex w-full items-center justify-between rounded-[1.35rem] border px-4 py-4 text-left transition"
                        :class="form.school_id === school.id
                            ? 'border-ink bg-ink text-foam'
                            : 'border-line bg-white/75 text-ink'"
                        @click="form.school_id = school.id"
                    >
                        <span class="font-semibold">{{ school.name }}</span>
                        <i v-if="form.school_id === school.id" class="pi pi-check text-zest" />
                    </button>
                </div>

                <p v-if="form.errors.school_id" class="text-xs text-bloom">{{ form.errors.school_id }}</p>
                <p v-if="!schools.length" class="rounded-[1.35rem] border border-dashed border-line bg-white/40 px-4 py-6 text-center text-sm text-ink-soft/60">
                    Nenhuma escola disponível. Cadastre um filho ou peça à cantina para liberar o acesso.
                </p>

                <button
                    type="submit"
                    class="flex h-14 w-full items-center justify-center rounded-[1.2rem] bg-ink text-sm font-semibold text-foam disabled:opacity-40"
                    :disabled="form.processing || !canSubmit"
                >
                    {{ form.processing ? 'Preparando...' : 'Continuar para o cardápio' }}
                </button>
            </form>
        </section>
    </MobileShell>
</template>
