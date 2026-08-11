@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">Auditoria</h1>

        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <form method="GET" action="{{ route('tenant.audit-logs.index') }}" class="grid grid-cols-1 gap-3 lg:grid-cols-6">
                <select name="user_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="">Todos os usuários</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected($userId === (int) $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
                <select name="action" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="">Todas as ações</option>
                    @foreach ($actions as $actionItem)
                        <option value="{{ $actionItem }}" @selected($action === $actionItem)>{{ $actionItem }}</option>
                    @endforeach
                </select>
                <select name="entity_type" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="">Todos os tipos de entidade</option>
                    @foreach ($entityTypes as $entityTypeItem)
                        <option value="{{ $entityTypeItem }}" @selected($entityType === $entityTypeItem)>{{ $entityTypeItem }}</option>
                    @endforeach
                </select>
                <input type="date" name="from" value="{{ $from }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                <input type="date" name="to" value="{{ $to }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                <div class="flex gap-2">
                    <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Filtrar</button>
                    <a href="{{ route('tenant.audit-logs.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Limpar</a>
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Usuário</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Ação</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Entidade</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Entity ID</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Data</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($logs as $log)
                            @php
                                $actionBadge = match (strtolower($log->action)) {
                                    'create', 'created', 'store' => 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-300',
                                    'delete', 'deleted', 'remove' => 'bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-300',
                                    'update', 'updated', 'edit' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/20 dark:text-amber-300',
                                    default => 'bg-blue-100 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300',
                                };
                            @endphp
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $log->user?->name ?? '-' }}</td>
                                <td class="px-4 py-3"><span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $actionBadge }}">{{ $log->action }}</span></td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $log->entity_type }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $log->entity_id ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $log->created_at?->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <a href="{{ route('tenant.audit-logs.show', $log) }}" class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Visualizar</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Nenhum log de auditoria encontrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-100 px-4 py-3 dark:border-gray-800">{{ $logs->links() }}</div>
        </div>
    </div>
@endsection
