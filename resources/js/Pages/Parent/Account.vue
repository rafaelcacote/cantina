<script setup>
import MobileShell from '@/Layouts/MobileShell.vue';
import ConfirmSheet from '@/Components/portal/ConfirmSheet.vue';
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import Avatar from 'primevue/avatar';
import Button from 'primevue/button';

const props = defineProps({
    profile: {
        type: Object,
        default: () => ({
            name: '',
            email: '',
            phone: null,
            cpf: null,
            children_count: 0,
        }),
    },
});

const logoutOpen = ref(false);
const loggingOut = ref(false);
const initial = (props.profile.name || '?').charAt(0).toUpperCase();

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

    <MobileShell role="parent">
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
                        <h3 class="truncate font-display text-xl font-semibold">{{ profile.name }}</h3>
                        <p class="mt-0.5 truncate text-xs text-foam/50">{{ profile.email }}</p>
                    </div>
                </div>
            </article>

            <dl class="overflow-hidden rounded-[1.45rem] border border-line bg-white/65 px-4 backdrop-blur">
                <div class="flex items-center justify-between border-b border-line py-3.5">
                    <dt class="text-xs text-ink-soft/45">Telefone</dt>
                    <dd class="text-sm font-semibold text-ink">{{ profile.phone || '—' }}</dd>
                </div>
                <div class="flex items-center justify-between border-b border-line py-3.5">
                    <dt class="text-xs text-ink-soft/45">CPF</dt>
                    <dd class="text-sm font-semibold text-ink">{{ profile.cpf || '—' }}</dd>
                </div>
                <div class="flex items-center justify-between py-3.5">
                    <dt class="text-xs text-ink-soft/45">Filhos vinculados</dt>
                    <dd class="text-sm font-semibold text-ink">{{ profile.children_count }}</dd>
                </div>
            </dl>

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
