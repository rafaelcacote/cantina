@props([
    'title' => 'Excluir registro?',
    'description' => 'Esta ação não pode ser desfeita.',
    'confirmLabel' => 'Excluir',
    'cancelLabel' => 'Cancelar',
])

<style>
    [x-cloak]{display:none!important}
</style>

<div
    x-data="{
        open: false,
        name: '',
        action: '',
        title: @js($title),
        description: @js($description),
        confirmLabel: @js($confirmLabel),
        submitting: false,
        show(detail = {}) {
            this.name = detail.name || '';
            this.action = detail.action || '';
            this.title = detail.title || @js($title);
            this.description = detail.description || @js($description);
            this.confirmLabel = detail.confirmLabel || @js($confirmLabel);
            this.submitting = false;
            this.open = true;
            document.body.style.overflow = 'hidden';
            this.$nextTick(() => this.$refs.cancelBtn?.focus());
        },
        close() {
            if (this.submitting) return;
            this.open = false;
            document.body.style.overflow = '';
        },
        confirm() {
            if (! this.action || this.submitting) return;
            this.submitting = true;
            this.$refs.deleteForm.submit();
        }
    }"
    @open-confirm-delete.window="show($event.detail)"
    @keydown.escape.window="open && close()"
>
    <form x-ref="deleteForm" method="POST" :action="action" class="hidden" aria-hidden="true">
        @csrf
        @method('DELETE')
    </form>

    <div
        x-show="open"
        x-cloak
        class="fixed inset-0 z-99999 flex items-center justify-center p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="confirm-delete-title"
        aria-describedby="confirm-delete-description"
    >
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="absolute inset-0 bg-gray-900/50 backdrop-blur-[2px]"
            @click="close()"
        ></div>

        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-3 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-2 scale-95"
            class="relative w-full max-w-[26rem] overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-gray-800 dark:bg-gray-900"
            @click.stop
        >
            <button
                type="button"
                class="absolute right-3 top-3 inline-flex size-9 items-center justify-center rounded-lg text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-white/5 dark:hover:text-white"
                @click="close()"
                :disabled="submitting"
                aria-label="Fechar"
            >
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
            </button>

            <div class="px-6 pb-2 pt-8 text-center sm:px-8">
                <div class="mx-auto mb-4 flex size-14 items-center justify-center rounded-full bg-error-50 text-error-500 ring-8 ring-error-50/60 dark:bg-error-500/15 dark:text-error-400 dark:ring-error-500/10">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M3 6h18" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                        <path d="M8 6V4.5A1.5 1.5 0 0 1 9.5 3h5A1.5 1.5 0 0 1 16 4.5V6M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M10 11v6M14 11v6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                    </svg>
                </div>

                <h2 id="confirm-delete-title" class="text-lg font-semibold text-gray-800 dark:text-white/90" x-text="title"></h2>

                <p id="confirm-delete-description" class="mt-2 text-sm leading-relaxed text-gray-500 dark:text-gray-400">
                    <span x-show="name">
                        Tem certeza que deseja excluir
                        <strong class="font-semibold text-gray-800 dark:text-white/90" x-text="name"></strong>?
                    </span>
                    <span class="mt-1 block" x-text="description"></span>
                </p>
            </div>

            <div class="mt-5 flex flex-col-reverse gap-2 border-t border-gray-200 bg-gray-50 px-6 py-4 sm:flex-row sm:items-center sm:justify-end dark:border-gray-800 dark:bg-white/[0.02]">
                <button
                    x-ref="cancelBtn"
                    type="button"
                    class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-60 dark:border-gray-700 dark:bg-transparent dark:text-gray-300 dark:hover:bg-white/5"
                    @click="close()"
                    :disabled="submitting"
                >
                    {{ $cancelLabel }}
                </button>
                <button
                    type="button"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-error-500 px-5 text-sm font-medium text-white transition-colors hover:bg-error-600 disabled:cursor-not-allowed disabled:opacity-70"
                    @click="confirm()"
                    :disabled="submitting"
                >
                    <svg x-show="!submitting" width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M3 6h18" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                        <path d="M8 6V4.5A1.5 1.5 0 0 1 9.5 3h5A1.5 1.5 0 0 1 16 4.5V6M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M10 11v6M14 11v6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                    </svg>
                    <svg x-show="submitting" class="animate-spin" width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" opacity="0.25"/>
                        <path d="M21 12a9 9 0 0 1-9 9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <span x-text="submitting ? 'Excluindo...' : confirmLabel"></span>
                </button>
            </div>
        </div>
    </div>
</div>
