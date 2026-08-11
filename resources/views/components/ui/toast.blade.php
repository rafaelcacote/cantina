@php
    $flashToasts = collect([
        'success' => session('success'),
        'error' => session('error'),
        'warning' => session('warning'),
        'info' => session('info'),
    ])->filter(fn ($message) => filled($message))
        ->map(fn ($message, $type) => [
            'id' => $type.'-'.uniqid(),
            'type' => $type,
            'message' => (string) $message,
            'visible' => true,
        ])
        ->values()
        ->all();

    $toastDurationMs = 5000;
@endphp

@if (! empty($flashToasts))
    <style>
        [x-cloak]{display:none!important}
        @keyframes toast-progress {
            from { transform: scaleX(1); }
            to { transform: scaleX(0); }
        }
        .toast-progress-bar {
            transform-origin: left center;
            animation: toast-progress linear forwards;
        }
    </style>

    <div
        x-cloak
        x-data="{
            toasts: @js($flashToasts),
            duration: {{ $toastDurationMs }},
            titles: {
                success: 'Sucesso',
                error: 'Erro',
                warning: 'Atenção',
                info: 'Informação'
            },
            init() {
                this.toasts.forEach((toast) => {
                    setTimeout(() => this.dismiss(toast.id), this.duration);
                });
            },
            dismiss(id) {
                const toast = this.toasts.find((item) => item.id === id);
                if (!toast) return;
                toast.visible = false;
                setTimeout(() => {
                    this.toasts = this.toasts.filter((item) => item.id !== id);
                }, 200);
            }
        }"
        class="fixed right-4 top-4 z-999999 flex w-[calc(100%-2rem)] max-w-sm flex-col gap-3 sm:right-6 sm:top-6"
    >
        <template x-for="toast in toasts" :key="toast.id">
            <div
                x-show="toast.visible"
                x-transition.opacity.duration.200ms
                class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-800 dark:bg-gray-900"
                role="status"
                aria-live="polite"
            >
                <div class="flex items-start gap-3 p-4">
                    <div
                        class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full"
                        :class="{
                            'bg-green-100 text-green-600 dark:bg-green-500/20 dark:text-green-400': toast.type === 'success',
                            'bg-red-100 text-red-600 dark:bg-red-500/20 dark:text-red-400': toast.type === 'error',
                            'bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400': toast.type === 'warning',
                            'bg-blue-100 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400': toast.type === 'info'
                        }"
                    >
                        <svg x-show="toast.type === 'success'" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <svg x-show="toast.type === 'error'" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M12 8v5m0 3h.01M12 3a9 9 0 100 18 9 9 0 000-18z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <svg x-show="toast.type === 'warning'" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M12 9v4m0 4h.01M10.3 4.3L2.8 17.2A2 2 0 004.5 20h15a2 2 0 001.7-2.8L13.7 4.3a2 2 0 00-3.4 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <svg x-show="toast.type === 'info'" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M12 16v-4m0-4h.01M12 3a9 9 0 100 18 9 9 0 000-18z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>

                    <div class="min-w-0 flex-1 pt-0.5">
                        <p class="text-sm font-semibold text-gray-800 dark:text-white/90" x-text="titles[toast.type] || 'Aviso'"></p>
                        <p class="mt-0.5 text-sm text-gray-600 dark:text-gray-300" x-text="toast.message"></p>
                    </div>

                    <button
                        type="button"
                        class="rounded-lg p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-white/5 dark:hover:text-white"
                        @click="dismiss(toast.id)"
                        aria-label="Fechar"
                    >
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>

                <div class="h-1 w-full bg-gray-100 dark:bg-gray-800">
                    <div
                        class="toast-progress-bar h-full w-full"
                        :class="{
                            'bg-green-500': toast.type === 'success',
                            'bg-red-500': toast.type === 'error',
                            'bg-amber-500': toast.type === 'warning',
                            'bg-blue-500': toast.type === 'info'
                        }"
                        :style="`animation-duration: ${duration}ms`"
                    ></div>
                </div>
            </div>
        </template>
    </div>
@endif
