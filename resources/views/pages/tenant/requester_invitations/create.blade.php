@extends('layouts.app')

@section('content')
    @php
        $inputClass = fn (string $field) => 'h-11 w-full rounded-lg border bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:text-white/90 '.($errors->has($field) ? 'border-error-500 dark:border-error-500' : 'border-gray-300 dark:border-gray-700');
    @endphp

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="flex items-center gap-2.5 text-2xl font-semibold text-gray-800 dark:text-white/90">
                <span class="inline-flex size-8 items-center justify-center text-brand-500 dark:text-brand-400">
                    {!! \App\Helpers\MenuHelper::getIconSvg('parent') !!}
                </span>
                Novo convite
            </h1>
            <a href="{{ route('tenant.requester-invitations.index') }}" class="text-sm font-medium text-brand-600">Voltar</a>
        </div>

        <div class="rounded-2xl border border-brand-100 bg-brand-50/70 px-5 py-4 text-sm text-gray-700 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-gray-300">
            O solicitante abre o link no celular, cria a conta e já pode pedir na cantina — com carteira e fiado, sem cadastro de filhos.
        </div>

        <form method="POST" action="{{ route('tenant.requester-invitations.store') }}" novalidate
              class="space-y-5 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            @csrf

            <div class="flex w-full flex-col gap-5 sm:flex-row">
                <div class="min-w-0 flex-1">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Expira em</label>
                    <input type="datetime-local"
                           name="expires_at"
                           value="{{ old('expires_at', now()->addDays(30)->format('Y-m-d\TH:i')) }}"
                           class="{{ $inputClass('expires_at') }}">
                    @error('expires_at')
                        <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
                    @enderror
                    <p class="mt-1.5 text-xs text-gray-500">Padrão: 30 dias.</p>
                </div>
                <div class="min-w-0 flex-1">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Máximo de usos</label>
                    <input type="number"
                           min="1"
                           name="max_uses"
                           value="{{ old('max_uses') }}"
                           placeholder="Ilimitado"
                           class="{{ $inputClass('max_uses') }}">
                    @error('max_uses')
                        <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
                    @enderror
                    <p class="mt-1.5 text-xs text-gray-500">Deixe vazio para um link compartilhado (WhatsApp da turma).</p>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('tenant.requester-invitations.index') }}"
                   class="inline-flex h-11 items-center rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300">
                    Cancelar
                </a>
                <button type="submit"
                        class="inline-flex h-11 items-center rounded-lg bg-brand-500 px-5 text-sm font-medium text-white hover:bg-brand-600">
                    Gerar link
                </button>
            </div>
        </form>
    </div>
@endsection
