<script setup>
import MobileShell from '@/Layouts/MobileShell.vue';
import ConfirmSheet from '@/Components/portal/ConfirmSheet.vue';
import { computed, ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import Avatar from 'primevue/avatar';
import Button from 'primevue/button';
import { formatMoney } from '@/composables/useFormat';

const props = defineProps({
    student: {
        type: Object,
        default: () => ({
            name: '',
            email: '',
            school: null,
            enrollment: null,
            grade: null,
            classroom: null,
            balance: 0,
            can_buy_on_tab: false,
            pin: null,
            has_pin: false,
        }),
    },
});

const logoutOpen = ref(false);
const loggingOut = ref(false);
const showCurrentPin = ref(false);
const showNewPin = ref(false);
const initial = (props.student.name || '?').charAt(0).toUpperCase();
const classroom = [props.student.grade, props.student.classroom].filter(Boolean).join(' · ') || '-';

const pinForm = useForm({
    personal_pin: '',
    personal_pin_confirmation: '',
});

const pinLabel = computed(() => {
    if (props.student.pin) {
        return showCurrentPin.value ? props.student.pin : '••••';
    }

    return props.student.has_pin ? 'Cadastrado — defina um novo para visualizar' : 'Ainda não cadastrado';
});

const savePin = () => {
    pinForm.put('/student/account/pin', {
        preserveScroll: true,
        onSuccess: () => pinForm.reset(),
    });
};

const logout = () => {
    loggingOut.value = true;
    router.post('/logout', {}, {
        onFinish: () => {
            loggingOut.value = false;
            logoutOpen.value = false;
        },
        onError: () => {
            window.location.href = '/signin';
        },
    });
};
</script>

<template>
    <Head title="Conta" />

    <MobileShell role="student">
        <section class="space-y-7">
            <div>
                <h2 class="font-display text-[2rem] font-semibold leading-none tracking-tight text-ink">
                    Minha conta
                </h2>
                <p class="mt-2 text-sm text-ink-soft/55">Seus dados no portal da cantina.</p>
            </div>

            <article class="relative overflow-hidden rounded-[1.7rem] bg-ink px-5 py-5 text-foam shadow-[0_20px_48px_rgba(20,36,31,0.22)]">
                <div class="pointer-events-none absolute -right-10 -top-12 size-40 rounded-full border-[28px] border-zest/10" />
                <div class="relative flex items-center gap-3.5">
                    <Avatar
                        :label="initial"
                        shape="square"
                        class="!size-14 !rounded-[1.15rem] !bg-zest !text-lg !font-semibold !text-ink"
                    />
                    <div class="min-w-0">
                        <h3 class="truncate font-display text-xl font-semibold">{{ student.name }}</h3>
                        <p class="mt-0.5 truncate text-xs text-foam/50">{{ student.email }}</p>
                    </div>
                </div>

                <div class="relative mt-5 rounded-[1.2rem] bg-white/8 px-4 py-3">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-foam/40">Saldo disponível</p>
                    <p class="font-display mt-1 text-2xl font-semibold text-zest">{{ formatMoney(student.balance) }}</p>
                </div>
            </article>

            <section>
                <p class="mb-3 text-[10px] font-semibold uppercase tracking-[0.18em] text-ink-soft/40">
                    Dados escolares
                </p>
                <dl class="overflow-hidden rounded-[1.45rem] border border-line bg-white/65 px-4 backdrop-blur">
                    <div class="flex items-center gap-3 border-b border-line py-3.5">
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-mist text-leaf-deep">
                            <i class="pi pi-building text-xs" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <dt class="text-[10px] font-medium uppercase tracking-wide text-ink-soft/40">Escola</dt>
                            <dd class="mt-0.5 truncate text-sm font-semibold text-ink">{{ student.school || '-' }}</dd>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 border-b border-line py-3.5">
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-mist text-leaf-deep">
                            <i class="pi pi-id-card text-xs" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <dt class="text-[10px] font-medium uppercase tracking-wide text-ink-soft/40">Matrícula</dt>
                            <dd class="mt-0.5 text-sm font-semibold text-ink">{{ student.enrollment || '-' }}</dd>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 py-3.5">
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-mist text-leaf-deep">
                            <i class="pi pi-users text-xs" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <dt class="text-[10px] font-medium uppercase tracking-wide text-ink-soft/40">Turma</dt>
                            <dd class="mt-0.5 text-sm font-semibold text-ink">{{ classroom }}</dd>
                        </div>
                    </div>
                </dl>
            </section>

            <section>
                <p class="mb-3 text-[10px] font-semibold uppercase tracking-[0.18em] text-ink-soft/40">
                    PIN do fiado
                </p>
                <article class="rounded-[1.45rem] border border-line bg-white/65 px-4 py-4 backdrop-blur">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-ink-soft/40">PIN atual</p>
                            <p class="mt-1 font-display text-2xl font-semibold tracking-[0.18em] text-ink">
                                {{ pinLabel }}
                            </p>
                            <p class="mt-1 text-xs text-ink-soft/45">
                                {{ student.can_buy_on_tab ? 'Usado para confirmar compra no fiado.' : 'O responsável ainda não liberou o fiado.' }}
                            </p>
                        </div>
                        <button
                            v-if="student.pin"
                            type="button"
                            class="flex size-10 items-center justify-center rounded-2xl bg-mist text-leaf-deep"
                            :aria-label="showCurrentPin ? 'Ocultar PIN' : 'Mostrar PIN'"
                            @click="showCurrentPin = !showCurrentPin"
                        >
                            <i :class="showCurrentPin ? 'pi pi-eye-slash' : 'pi pi-eye'" />
                        </button>
                    </div>

                    <form class="mt-4 space-y-3" @submit.prevent="savePin">
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.14em] text-ink-soft/45">
                                {{ student.has_pin ? 'Novo PIN' : 'Definir PIN' }}
                            </label>
                            <div class="relative">
                                <input
                                    v-model="pinForm.personal_pin"
                                    :type="showNewPin ? 'text' : 'password'"
                                    inputmode="numeric"
                                    maxlength="8"
                                    autocomplete="one-time-code"
                                    class="h-12 w-full rounded-2xl border border-line bg-white/80 px-4 pr-12 text-sm text-ink outline-none focus:border-leaf/40"
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
                            <p v-if="pinForm.errors.personal_pin" class="mt-1.5 text-xs text-bloom">
                                {{ pinForm.errors.personal_pin }}
                            </p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.14em] text-ink-soft/45">
                                Confirmar PIN
                            </label>
                            <input
                                v-model="pinForm.personal_pin_confirmation"
                                type="password"
                                inputmode="numeric"
                                maxlength="8"
                                class="h-12 w-full rounded-2xl border border-line bg-white/80 px-4 text-sm text-ink outline-none focus:border-leaf/40"
                            >
                            <p v-if="pinForm.errors.personal_pin_confirmation" class="mt-1.5 text-xs text-bloom">
                                {{ pinForm.errors.personal_pin_confirmation }}
                            </p>
                        </div>
                        <button
                            type="submit"
                            class="flex h-12 w-full items-center justify-center rounded-2xl bg-ink text-sm font-semibold text-foam disabled:opacity-50"
                            :disabled="pinForm.processing"
                        >
                            {{ pinForm.processing ? 'Salvando...' : 'Salvar PIN' }}
                        </button>
                    </form>
                </article>
            </section>

            <Button
                label="Sair da conta"
                severity="secondary"
                text
                class="w-full !text-ink-soft/55"
                icon="pi pi-sign-out"
                @click="logoutOpen = true"
            />
        </section>

        <ConfirmSheet
            v-model:visible="logoutOpen"
            title="Sair da conta?"
            message="Você vai precisar entrar de novo para ver saldo e pedidos."
            confirm-label="Sair"
            icon="pi pi-sign-out"
            danger
            :loading="loggingOut"
            @confirm="logout"
        />
    </MobileShell>
</template>
