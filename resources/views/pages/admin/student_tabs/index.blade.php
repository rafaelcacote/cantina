@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">Fiados</h1>
            <a href="{{ route('admin.student-tabs.create') }}" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Novo Fiado</a>
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800/40 dark:bg-green-900/20 dark:text-green-300">{{ session('success') }}</div>
        @endif

        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <form method="GET" action="{{ route('admin.student-tabs.index') }}" class="grid grid-cols-1 gap-3 lg:grid-cols-5">
                <select id="tenant_filter" name="tenant_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="">Todos os tenants</option>
                    @foreach ($tenants as $tenant)
                        <option value="{{ $tenant->id }}" @selected($tenantId === (int) $tenant->id)>{{ $tenant->name }}</option>
                    @endforeach
                </select>
                <select id="student_filter" name="student_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="">Todos os alunos</option>
                    @foreach ($students as $student)
                        <option value="{{ $student->id }}" data-tenant-id="{{ $student->tenant_id }}" @selected($studentId === (int) $student->id)>{{ $student->name }}</option>
                    @endforeach
                </select>
                <select name="active" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="">Todos os status</option>
                    <option value="1" @selected($active === '1')>Ativo</option>
                    <option value="0" @selected($active === '0')>Inativo</option>
                </select>
                <div class="lg:col-span-2 flex gap-2">
                    <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Filtrar</button>
                    <a href="{{ route('admin.student-tabs.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Limpar</a>
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Aluno</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Tenant</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Saldo em aberto</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Ciclo</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Vencimento</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($tabs as $tab)
                            @php
                                $openBalance = (float) $tab->current_balance;
                                $openBalanceBadge = $openBalance > 0
                                    ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/20 dark:text-amber-300'
                                    : 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-300';
                                $openBalanceLabel = $openBalance > 0 ? 'Em aberto' : 'Sem débito';
                            @endphp
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $tab->student?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $tenantNames[$tab->tenant_id] ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                    <div class="flex items-center gap-2">
                                        <span>R$ {{ number_format($openBalance, 2, ',', '.') }}</span>
                                        <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $openBalanceBadge }}">{{ $openBalanceLabel }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $cycleTypes[$tab->billing_cycle_type] ?? $tab->billing_cycle_type }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $tab->due_day ?: '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $tab->active ? 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-300' }}">{{ $tab->active ? 'Ativo' : 'Inativo' }}</span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <div class="flex gap-2">
                                        <a href="{{ route('admin.student-tabs.show', $tab) }}" class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Visualizar</a>
                                        <a href="{{ route('admin.student-tabs.edit', $tab) }}" class="rounded-md bg-brand-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-brand-600">Editar</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Nenhuma conta de fiado encontrada.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-100 px-4 py-3 dark:border-gray-800">{{ $tabs->links() }}</div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const tenant = document.getElementById('tenant_filter');
            const student = document.getElementById('student_filter');
            if (!tenant || !student) return;
            const syncStudents = () => {
                const tenantId = tenant.value;
                [...student.options].forEach((opt, idx) => {
                    if (idx === 0) return;
                    const match = !tenantId || opt.dataset.tenantId === tenantId;
                    opt.hidden = !match;
                    opt.disabled = !match;
                });
                if (student.selectedOptions[0]?.disabled) student.value = '';
            };
            tenant.addEventListener('change', syncStudents);
            syncStudents();
        })();
    </script>
@endpush
