<script setup>
import MobileShell from '@/Layouts/MobileShell.vue';
import BackLink from '@/Components/portal/BackLink.vue';
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    child: { type: Object, required: true },
    share: { type: Object, required: true },
});

const copied = ref(false);
const canNativeShare = computed(() => typeof navigator !== 'undefined' && typeof navigator.share === 'function');

const copyLink = async () => {
    try {
        await navigator.clipboard.writeText(props.share.url);
        copied.value = true;
        window.setTimeout(() => {
            copied.value = false;
        }, 2000);
    } catch {
        copied.value = false;
    }
};

const nativeShare = async () => {
    try {
        await navigator.share({
            title: `Acesso de ${props.child.name}`,
            text: props.share.share_text,
            url: props.share.url,
        });
    } catch {
        // cancelled
    }
};
</script>

<template>
    <Head title="Enviar acesso" />

    <MobileShell role="parent">
        <section class="space-y-6">
            <div>
                <BackLink :href="`/parent/children/${child.id}`" :label="child.name" />
                <h2 class="font-display mt-4 text-[2rem] font-semibold leading-none tracking-tight text-ink">
                    Acesso do filho
                </h2>
                <p class="mt-2 text-sm leading-relaxed text-ink-soft/60">
                    <template v-if="share.has_access">
                        {{ child.name }} já tem conta. Envie o link de entrada.
                    </template>
                    <template v-else>
                        Envie o convite. {{ child.name }} abre o link e cria a própria senha.
                    </template>
                </p>
            </div>

            <article class="rounded-[1.45rem] border border-line bg-white/80 p-4">
                <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-ink-soft/40">Aluno</p>
                <p class="mt-1 font-display text-xl font-semibold text-ink">{{ child.name }}</p>
                <p v-if="share.email" class="mt-1 text-sm text-ink-soft/55">{{ share.email }}</p>
                <p v-else-if="share.expires_at" class="mt-1 text-xs text-ink-soft/45">
                    Convite válido até {{ share.expires_at }}
                </p>
            </article>

            <div class="space-y-2.5">
                <a
                    :href="share.whatsapp_url"
                    target="_blank"
                    rel="noopener"
                    class="flex h-14 items-center justify-between rounded-[1.2rem] bg-ink px-4 text-foam"
                >
                    <span>
                        <span class="block text-[10px] font-bold uppercase tracking-[0.15em] text-zest/80">Recomendado</span>
                        <span class="block text-sm font-semibold">Enviar no WhatsApp</span>
                    </span>
                    <i class="pi pi-whatsapp text-lg text-zest" />
                </a>

                <button
                    type="button"
                    class="flex h-14 w-full items-center justify-between rounded-[1.2rem] border border-line bg-white/80 px-4 text-left"
                    @click="copyLink"
                >
                    <span>
                        <span class="block text-sm font-semibold text-ink">{{ copied ? 'Link copiado!' : 'Copiar link' }}</span>
                        <span class="mt-0.5 block max-w-[28ch] truncate text-xs text-ink-soft/50">{{ share.url }}</span>
                    </span>
                    <i class="pi pi-copy text-leaf-deep" />
                </button>

                <button
                    v-if="canNativeShare"
                    type="button"
                    class="flex h-14 w-full items-center justify-between rounded-[1.2rem] border border-line bg-white/80 px-4 text-left"
                    @click="nativeShare"
                >
                    <span>
                        <span class="block text-sm font-semibold text-ink">Compartilhar</span>
                        <span class="mt-0.5 block text-xs text-ink-soft/50">Mensagens, Telegram e outros</span>
                    </span>
                    <i class="pi pi-share-alt text-leaf-deep" />
                </button>
            </div>
        </section>
    </MobileShell>
</template>
