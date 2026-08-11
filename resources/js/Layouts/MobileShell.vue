<script setup>
import { computed, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import Avatar from 'primevue/avatar';
import Toast from 'primevue/toast';

const props = defineProps({
    role: {
        type: String,
        required: true,
        validator: (value) => ['parent', 'student'].includes(value),
    },
});

const page = usePage();
const toast = useToast();

const user = computed(() => page.props.auth?.user);
const tenant = computed(() => page.props.tenant);
const brandName = computed(() => tenant.value?.name || 'Cantina');
const brandLogo = computed(() => tenant.value?.logo_url || null);
const brandInitial = computed(() => (brandName.value || 'C').charAt(0).toUpperCase());
const userInitial = computed(() => (user.value?.name || '?').charAt(0).toUpperCase());

const navItems = computed(() => {
    if (props.role === 'parent') {
        return [
            { key: 'home', label: 'Início', href: '/parent', icon: 'pi pi-home' },
            { key: 'children', label: 'Filhos', href: '/parent/children', icon: 'pi pi-users' },
            { key: 'orders', label: 'Pedidos', href: '/parent/orders', icon: 'pi pi-shopping-bag' },
            { key: 'account', label: 'Conta', href: '/parent/account', icon: 'pi pi-user' },
        ];
    }

    return [
        { key: 'home', label: 'Início', href: '/student', icon: 'pi pi-home' },
        { key: 'menu', label: 'Produtos', href: '/student/menu', icon: 'pi pi-th-large' },
        { key: 'orders', label: 'Pedidos', href: '/student/orders', icon: 'pi pi-shopping-bag' },
        { key: 'account', label: 'Conta', href: '/student/account', icon: 'pi pi-user' },
    ];
});

const isActive = (href) => {
    const url = page.url.split('?')[0];
    if (href === '/parent' || href === '/student') {
        return url === href || url === `${href}/`;
    }
    return url === href || url.startsWith(`${href}/`);
};

const accountHref = computed(() => (props.role === 'student' ? '/student/account' : '/parent/account'));

watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) {
            toast.add({
                severity: 'success',
                summary: 'Pronto',
                detail: flash.success,
                life: 3600,
            });
        }

        if (flash?.error) {
            toast.add({
                severity: 'error',
                summary: 'Atenção',
                detail: flash.error,
                life: 4600,
            });
        }
    },
    { immediate: true, deep: true },
);
</script>

<template>
    <div class="portal-atmosphere portal-grain relative min-h-dvh overflow-x-hidden">
        <Toast position="top-center" />

        <div class="pointer-events-none absolute inset-x-0 top-0 h-72 opacity-70" aria-hidden="true">
            <div class="absolute -left-16 -top-10 size-56 rounded-full bg-leaf/15 blur-3xl" />
            <div class="absolute right-0 top-8 size-48 rounded-full bg-zest/25 blur-3xl" />
        </div>

        <div class="relative mx-auto flex min-h-dvh w-full max-w-lg flex-col px-4 pb-[calc(6.75rem+env(safe-area-inset-bottom))] pt-[max(1rem,env(safe-area-inset-top))] sm:px-6">
            <header class="mb-7 flex items-center justify-between gap-3">
                <div class="flex min-w-0 items-center gap-3">
                    <div
                        class="flex size-11 shrink-0 items-center justify-center overflow-hidden rounded-[0.95rem] border border-white/70 bg-white/80 p-0.5 shadow-[0_8px_24px_rgba(20,36,31,0.07)] backdrop-blur-xl"
                        :aria-label="brandName"
                    >
                        <img
                            v-if="brandLogo"
                            :src="brandLogo"
                            :alt="brandName"
                            class="size-full object-contain"
                        >
                        <span
                            v-else
                            class="font-display text-lg font-semibold tracking-tight text-ink"
                        >
                            {{ brandInitial }}
                        </span>
                    </div>

                    <div class="min-w-0">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-ink-soft/45">
                            {{ role === 'parent' ? 'Área do responsável' : 'Área do aluno' }}
                        </p>
                        <h1 class="font-display mt-1 truncate text-[1.15rem] font-semibold leading-none tracking-tight text-ink">
                            {{ brandName }}
                        </h1>
                    </div>
                </div>

                <div class="flex shrink-0 items-center gap-2">
                    <component
                        :is="accountHref ? Link : 'div'"
                        :href="accountHref || undefined"
                        class="block"
                    >
                        <Avatar
                            :label="userInitial"
                            shape="square"
                            class="!size-10 !rounded-full !bg-ink !text-sm !font-semibold !text-zest shadow-[0_10px_26px_rgba(20,36,31,0.16)]"
                            :title="user?.name"
                        />
                    </component>

                </div>
            </header>

            <main class="flex-1">
                <Transition
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="translate-y-2 opacity-0"
                    enter-to-class="translate-y-0 opacity-100"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                    mode="out-in"
                >
                    <div :key="page.url">
                        <slot />
                    </div>
                </Transition>
            </main>
        </div>

        <nav
            class="fixed inset-x-3 bottom-[max(0.7rem,env(safe-area-inset-bottom))] z-40"
            aria-label="Navegação principal"
        >
            <div class="mx-auto grid max-w-lg grid-cols-4 gap-1 rounded-[1.55rem] border border-white/70 bg-white/88 p-1.5 shadow-[0_16px_44px_rgba(20,36,31,0.14)] backdrop-blur-2xl">
                <component
                    :is="item.soon ? 'button' : Link"
                    v-for="item in navItems"
                    :key="item.key"
                    :href="item.soon ? undefined : item.href"
                    type="button"
                    class="group relative flex min-h-14 flex-col items-center justify-center gap-0.5 rounded-[1.15rem] px-2 py-1.5 text-[10px] font-semibold transition"
                    :class="isActive(item.href)
                        ? 'bg-ink text-foam shadow-[0_8px_18px_rgba(20,36,31,0.16)]'
                        : 'text-ink-soft/55 hover:text-ink-soft'"
                    :disabled="item.soon"
                    :title="item.soon ? 'Em breve' : item.label"
                >
                    <span
                        class="flex size-7 items-center justify-center rounded-xl transition"
                        :class="isActive(item.href)
                            ? 'text-zest'
                            : 'bg-transparent group-hover:bg-mist'"
                    >
                        <i :class="item.icon" class="text-[0.95rem]" />
                    </span>
                    <span>{{ item.label }}</span>
                </component>
            </div>
        </nav>
    </div>
</template>
