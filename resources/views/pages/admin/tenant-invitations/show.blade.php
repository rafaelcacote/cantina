@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Detalhes do Convite</h1>
            <div class="flex gap-2">
                <a href="{{ route('admin.tenant-invitations.edit', $invitation) }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm dark:border-gray-700">Editar</a>
                <a href="{{ route('admin.tenant-invitations.index') }}" class="text-sm font-medium text-brand-600">Voltar</a>
            </div>
        </div>
        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
        @endif
        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div><dt class="text-xs uppercase text-gray-500">Tenant</dt><dd class="mt-1 text-sm font-medium">{{ $invitation->tenant?->name }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Tipo</dt><dd class="mt-1 text-sm font-medium">{{ $types[$invitation->type] ?? $invitation->type }}</dd></div>
                <div class="sm:col-span-2">
                    <dt class="text-xs uppercase text-gray-500">Link de aceite</dt>
                    <dd class="mt-1 break-all text-sm font-medium text-brand-600">
                        <a href="{{ $acceptUrl }}" target="_blank" rel="noopener">{{ $acceptUrl }}</a>
                    </dd>
                </div>
                <div><dt class="text-xs uppercase text-gray-500">Token</dt><dd class="mt-1 break-all text-sm font-mono text-xs">{{ $invitation->token }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Usos</dt><dd class="mt-1 text-sm font-medium">{{ $invitation->used_count }}{{ $invitation->max_uses ? ' / '.$invitation->max_uses : '' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Expira em</dt><dd class="mt-1 text-sm font-medium">{{ $invitation->expires_at?->format('d/m/Y H:i') ?? 'Sem expiração' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Ativo</dt><dd class="mt-1 text-sm font-medium">{{ $invitation->active ? 'Sim' : 'Não' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Criado por</dt><dd class="mt-1 text-sm font-medium">{{ $invitation->creator?->name ?? '-' }}</dd></div>
            </dl>
        </div>
    </div>
@endsection
