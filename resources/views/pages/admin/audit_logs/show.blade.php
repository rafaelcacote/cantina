@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">Detalhes da Auditoria</h1>
            <a href="{{ route('admin.audit-logs.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Voltar</a>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="xl:col-span-2 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Resumo</h2>
                <dl class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div><dt class="text-xs uppercase text-gray-500">Tenant</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $tenantName ?? '-' }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Usuário</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $log->user?->name ?? '-' }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Ação</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $log->action }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Entidade</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $log->entity_type }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Entity ID</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $log->entity_id ?? '-' }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">IP</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $log->ip_address ?: '-' }}</dd></div>
                    <div class="md:col-span-2"><dt class="text-xs uppercase text-gray-500">User Agent</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200 break-all">{{ $log->user_agent ?: '-' }}</dd></div>
                </dl>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Timestamps</h2>
                <dl class="mt-4 space-y-4">
                    <div><dt class="text-xs uppercase text-gray-500">Criado em</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $log->created_at?->format('d/m/Y H:i') }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Atualizado em</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $log->updated_at?->format('d/m/Y H:i') }}</dd></div>
                </dl>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Old Data</h2>
                <pre class="mt-4 overflow-auto rounded-lg bg-gray-900 p-4 text-xs text-gray-100">{{ $log->old_data ? json_encode($log->old_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '{}' }}</pre>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">New Data</h2>
                <pre class="mt-4 overflow-auto rounded-lg bg-gray-900 p-4 text-xs text-gray-100">{{ $log->new_data ? json_encode($log->new_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '{}' }}</pre>
            </div>
        </div>
    </div>
@endsection
