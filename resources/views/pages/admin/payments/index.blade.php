@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">Pagamentos</h1>
            <a href="{{ route('admin.payments.create') }}" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Novo Pagamento</a>
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800/40 dark:bg-green-900/20 dark:text-green-300">{{ session('success') }}</div>
        @endif

        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <form method="GET" action="{{ route('admin.payments.index') }}" class="grid grid-cols-1 gap-3 lg:grid-cols-7">
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
                <select id="parent_filter" name="parent_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="">Todos os responsáveis</option>
                    @foreach ($parents as $parent)
                        <option value="{{ $parent->id }}" data-tenant-id="{{ $parent->tenant_id }}" @selected($parentId === (int) $parent->id)>{{ $parent->name }}</option>
                    @endforeach
                </select>
                <select name="payment_method" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="">Todos os métodos</option>
                    @foreach ($methods as $key => $label)
                        <option value="{{ $key }}" @selected($method === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="status" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="">Todos os status</option>
                    @foreach ($statuses as $key => $label)
                        <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <input type="date" name="from" value="{{ $from }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                <input type="date" name="to" value="{{ $to }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                <div class="lg:col-span-7 flex gap-2">
                    <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Filtrar</button>
                    <a href="{{ route('admin.payments.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Limpar</a>
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Tenant</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Aluno</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Responsável</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Valor</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Método</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Pago em</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($payments as $payment)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $tenantNames[$payment->tenant_id] ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $payment->student?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $payment->parent?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">R$ {{ number_format((float) $payment->amount, 2, ',', '.') }}</td>
                                <td class="px-4 py-3"><span class="inline-flex rounded-full px-2 py-1 text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300">{{ $methods[$payment->payment_method] ?? $payment->payment_method }}</span></td>
                                <td class="px-4 py-3"><span class="inline-flex rounded-full px-2 py-1 text-xs font-medium bg-purple-100 text-purple-700 dark:bg-purple-900/20 dark:text-purple-300">{{ $statuses[$payment->status] ?? $payment->status }}</span></td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $payment->paid_at?->format('d/m/Y H:i') ?: '-' }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <div class="flex gap-2">
                                        <a href="{{ route('admin.payments.show', $payment) }}" class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Visualizar</a>
                                        <a href="{{ route('admin.payments.edit', $payment) }}" class="rounded-md bg-brand-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-brand-600">Editar</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Nenhum pagamento encontrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-100 px-4 py-3 dark:border-gray-800">{{ $payments->links() }}</div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const tenant = document.getElementById('tenant_filter');
            const student = document.getElementById('student_filter');
            const parent = document.getElementById('parent_filter');
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
            tenant.addEventListener('change', () => { syncByTenant(student); syncByTenant(parent); });
            syncByTenant(student); syncByTenant(parent);
        })();
    </script>
@endpush
