@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">Detalhes da Autorização</h1>
            <a href="{{ route('admin.purchase-authorizations.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Voltar</a>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <dl class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div><dt class="text-xs uppercase text-gray-500">Tenant</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $tenantName ?? '-' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Escola</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $authorization->school?->name ?? '-' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Aluno</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $authorization->student?->name ?? '-' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Tipo</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $authorizationTypes[$authorization->authorization_type] ?? $authorization->authorization_type }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Método</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $authorization->auth_method }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Resultado</dt><dd class="mt-1"><span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $authorization->success ? 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-300' }}">{{ $authorization->success ? 'Sucesso' : 'Falha' }}</span></dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Motivo da falha</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $authorization->failure_reason ?: '-' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Dispositivo</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $authorization->device_type ?: '-' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">IP</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $authorization->ip_address ?: '-' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Criado por</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $authorization->creator?->name ?? '-' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Pedido</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $authorization->order_id ? '#' . $authorization->order_id : '-' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Lançamento fiado</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $authorization->tab_entry_id ? '#' . $authorization->tab_entry_id : '-' }}</dd></div>
                <div class="md:col-span-2"><dt class="text-xs uppercase text-gray-500">User Agent</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200 break-all">{{ $authorization->user_agent ?: '-' }}</dd></div>
            </dl>
        </div>
    </div>
@endsection
