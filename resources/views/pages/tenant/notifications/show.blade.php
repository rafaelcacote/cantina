@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">Detalhes da Notificação</h1>
            <div class="flex gap-2">
                @if ($notification->read_at)
                    <form method="POST" action="{{ route('tenant.notifications.mark-as-unread', $notification) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-medium text-white hover:bg-amber-600">Marcar como não lida</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('tenant.notifications.mark-as-read', $notification) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="rounded-lg bg-green-500 px-4 py-2 text-sm font-medium text-white hover:bg-green-600">Marcar como lida</button>
                    </form>
                @endif
                <a href="{{ route('tenant.notifications.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Voltar</a>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800/40 dark:bg-green-900/20 dark:text-green-300">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="xl:col-span-2 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Resumo</h2>
                <dl class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div><dt class="text-xs uppercase text-gray-500">Tipo</dt><dd class="mt-1"><span class="inline-flex rounded-full bg-blue-100 px-2 py-1 text-xs font-medium text-blue-700 dark:bg-blue-900/20 dark:text-blue-300">{{ $notification->notification_type }}</span></dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Usuário</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $notification->user?->name ?? '-' }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Aluno</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $notification->student?->name ?? '-' }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Status</dt><dd class="mt-1"><span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $notification->read_at ? 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/20 dark:text-amber-300' }}">{{ $notification->read_at ? 'Lida' : 'Não lida' }}</span></dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Lida em</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $notification->read_at?->format('d/m/Y H:i') ?: '-' }}</dd></div>
                    <div class="md:col-span-2"><dt class="text-xs uppercase text-gray-500">Título</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $notification->title }}</dd></div>
                    <div class="md:col-span-2"><dt class="text-xs uppercase text-gray-500">Mensagem</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $notification->message }}</dd></div>
                </dl>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Timestamps</h2>
                <dl class="mt-4 space-y-4">
                    <div><dt class="text-xs uppercase text-gray-500">Criada em</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $notification->created_at?->format('d/m/Y H:i') }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Atualizada em</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $notification->updated_at?->format('d/m/Y H:i') }}</dd></div>
                </dl>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Payload JSON</h2>
            <pre class="mt-4 overflow-auto rounded-lg bg-gray-900 p-4 text-xs text-gray-100">{{ $notification->payload ? json_encode($notification->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '{}' }}</pre>
        </div>
    </div>
@endsection
