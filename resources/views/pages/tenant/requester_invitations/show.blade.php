@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-3xl space-y-6"
         x-data="{ copied: false, copy() { navigator.clipboard.writeText(@js($acceptUrl)); this.copied = true; setTimeout(() => this.copied = false, 2000) } }">
        <div class="flex items-center justify-between">
            <h1 class="flex items-center gap-2.5 text-2xl font-semibold text-gray-800 dark:text-white/90">
                <span class="inline-flex size-8 items-center justify-center text-brand-500 dark:text-brand-400">
                    {!! \App\Helpers\MenuHelper::getIconSvg('parent') !!}
                </span>
                Enviar convite
            </h1>
            <a href="{{ route('tenant.requester-invitations.index') }}" class="text-sm font-medium text-brand-600">Voltar</a>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Link do solicitante</p>
            <p class="mt-2 break-all text-sm font-medium text-gray-800 dark:text-white/90">{{ $acceptUrl }}</p>

            <div class="mt-5 flex flex-wrap gap-2">
                <button type="button"
                        @click="copy()"
                        class="inline-flex h-11 items-center justify-center rounded-lg bg-brand-500 px-4 text-sm font-medium text-white hover:bg-brand-600">
                    <span x-text="copied ? 'Copiado!' : 'Copiar link'"></span>
                </button>
                <a href="{{ $whatsappUrl }}"
                   target="_blank"
                   rel="noopener"
                   class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300">
                    Enviar no WhatsApp
                </a>
                <a href="{{ $mailtoUrl }}"
                   class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300">
                    Enviar por e-mail
                </a>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <dt class="text-xs uppercase text-gray-500">Usos</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">
                        {{ $invitation->used_count }}{{ $invitation->max_uses ? ' / '.$invitation->max_uses : ' · ilimitado' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs uppercase text-gray-500">Expira em</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">
                        {{ $invitation->expires_at?->format('d/m/Y H:i') ?? 'Sem expiração' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs uppercase text-gray-500">Status</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">
                        {{ $invitation->isUsable() ? 'Ativo' : 'Inativo' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs uppercase text-gray-500">Criado por</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">
                        {{ $invitation->creator?->name ?? '-' }}
                    </dd>
                </div>
            </dl>

            <form method="POST" action="{{ route('tenant.requester-invitations.toggle', $invitation) }}" class="mt-6">
                @csrf
                @method('PATCH')
                <button type="submit"
                        class="inline-flex h-11 items-center rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300">
                    {{ $invitation->active ? 'Desativar convite' : 'Reativar convite' }}
                </button>
            </form>
        </div>
    </div>
@endsection
