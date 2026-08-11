<script setup>
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';

const visible = defineModel('visible', { type: Boolean, default: false });

defineProps({
    title: { type: String, required: true },
    message: { type: String, default: '' },
    confirmLabel: { type: String, default: 'Confirmar' },
    cancelLabel: { type: String, default: 'Voltar' },
    icon: { type: String, default: 'pi pi-question-circle' },
    danger: { type: Boolean, default: false },
    loading: { type: Boolean, default: false },
});

const emit = defineEmits(['confirm']);
</script>

<template>
    <Dialog
        v-model:visible="visible"
        modal
        :show-header="false"
        :draggable="false"
        :dismissable-mask="!loading"
        :closable="false"
        position="bottom"
        :pt="{
            root: { class: 'portal-sheet' },
            mask: { class: 'portal-sheet-mask' },
            header: { class: 'hidden' },
            content: { class: '!px-5 !pb-2 !pt-2' },
            footer: { class: '!px-5 !pb-5 !pt-2' },
        }"
    >
        <div class="mx-auto mb-4 h-1.5 w-10 rounded-full bg-line" />

        <div class="flex items-start gap-3">
            <span
                class="flex size-11 shrink-0 items-center justify-center rounded-2xl"
                :class="danger ? 'bg-bloom/15 text-bloom' : 'bg-zest/70 text-ink'"
            >
                <i :class="icon" class="text-lg" />
            </span>
            <div class="min-w-0 pt-0.5">
                <h3 class="font-display text-xl font-semibold tracking-tight text-ink">
                    {{ title }}
                </h3>
                <p v-if="message" class="mt-1.5 text-sm leading-relaxed text-ink-soft/70">
                    {{ message }}
                </p>
            </div>
        </div>

        <slot />

        <template #footer>
            <div class="flex w-full gap-2">
                <Button
                    :label="cancelLabel"
                    severity="secondary"
                    outlined
                    class="flex-1"
                    :disabled="loading"
                    @click="visible = false"
                />
                <Button
                    :label="confirmLabel"
                    class="flex-1"
                    :severity="danger ? 'danger' : 'primary'"
                    :loading="loading"
                    @click="emit('confirm')"
                />
            </div>
        </template>
    </Dialog>
</template>
