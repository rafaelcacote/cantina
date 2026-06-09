@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">Notificações</h1>

        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <form method="GET" action="{{ route('admin.notifications.index') }}" class="grid grid-cols-1 gap-3 lg:grid-cols-7">
                <select id="tenant_filter" name="tenant_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="">Todos os tenants</option>
                    @foreach ($tenants as $tenant)
                        <option value="{{ $tenant->id }}" @selected($tenantId === (int) $tenant->id)>{{ $tenant->name }}</option>
                    @endforeach
                </select>
                <select id="user_filter" name="user_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="">Todos os usuários</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" data-tenant-id="{{ $user->tenant_id }}" @selected($userId === (int) $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
                <select id="student_filter" name="student_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="">Todos os alunos</option>
                    @foreach ($students as $student)
                        <option value="{{ $student->id }}" data-tenant-id="{{ $student->tenant_id }}" @selected($studentId === (int) $student->id)>{{ $student->name }}</option>
                    @endforeach
                </select>
                <select name="notification_type" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="">Todos os tipos</option>
                    @foreach ($types as $typeItem)
                        <option value="{{ $typeItem }}" @selected($type === $typeItem)>{{ $typeItem }}</option>
                    @endforeach
                </select>
                <select name="read_status" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="">Lidas e não lidas</option>
                    <option value="read" @selected($readStatus === 'read')>Lidas</option>
                    <option value="unread" @selected($readStatus === 'unread')>Não lidas</option>
                </select>
                <div class="lg:col-span-2 flex gap-2">
                    <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Filtrar</button>
                    <a href="{{ route('admin.notifications.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Limpar</a>
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Tenant</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Título</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Usuário/Aluno</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Tipo</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Criada em</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($notifications as $notification)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $tenantNames[$notification->tenant_id] ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $notification->title }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $notification->user?->name ?? '-' }}
                                    @if ($notification->student)
                                        <span class="text-gray-500 dark:text-gray-400">/ {{ $notification->student->name }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3"><span class="inline-flex rounded-full bg-blue-100 px-2 py-1 text-xs font-medium text-blue-700 dark:bg-blue-900/20 dark:text-blue-300">{{ $notification->notification_type }}</span></td>
                                <td class="px-4 py-3"><span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $notification->read_at ? 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/20 dark:text-amber-300' }}">{{ $notification->read_at ? 'Lida' : 'Não lida' }}</span></td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $notification->created_at?->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <a href="{{ route('admin.notifications.show', $notification) }}" class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Visualizar</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Nenhuma notificação encontrada.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-100 px-4 py-3 dark:border-gray-800">{{ $notifications->links() }}</div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const tenant = document.getElementById('tenant_filter');
            const user = document.getElementById('user_filter');
            const student = document.getElementById('student_filter');
            if (!tenant) return;
            const syncByTenant = (select) => {
                if (!select) return;
                const tenantId = tenant.value;
                [...select.options].forEach((opt, idx) => {
                    if (idx === 0) return;
                    const match = !tenantId || opt.dataset.tenantId === tenantId;
                    opt.hidden = !match;
                    opt.disabled = !match;
                });
                if (select.selectedOptions[0]?.disabled) select.value = '';
            };
            tenant.addEventListener('change', () => { syncByTenant(user); syncByTenant(student); });
            syncByTenant(user);
            syncByTenant(student);
        })();
    </script>
@endpush
