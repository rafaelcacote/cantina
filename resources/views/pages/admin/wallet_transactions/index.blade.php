@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">Extrato</h1>

        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <form method="GET" action="{{ route('admin.wallet-transactions.index') }}" class="grid grid-cols-1 gap-3 lg:grid-cols-6">
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
                <select name="transaction_type" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="">Todos os tipos</option>
                    @foreach ($transactionTypes as $key => $label)
                        <option value="{{ $key }}" @selected($transactionType === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <input type="date" name="from" value="{{ $from }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                <input type="date" name="to" value="{{ $to }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                <div class="flex gap-2">
                    <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Filtrar</button>
                    <a href="{{ route('admin.wallet-transactions.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Limpar</a>
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
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Tipo</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Valor</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Antes</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Depois</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Data</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($transactions as $transaction)
                            @php
                                $typeBadge = match ($transaction->transaction_type) {
                                    'debit' => 'bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-300',
                                    'refund' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300',
                                    'adjustment' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/20 dark:text-purple-300',
                                    default => 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-300',
                                };
                            @endphp
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $transaction->student?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $tenantNames[$transaction->tenant_id] ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $typeBadge }}">
                                        {{ $transactionTypes[$transaction->transaction_type] ?? $transaction->transaction_type }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">R$ {{ number_format((float) $transaction->amount, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">R$ {{ number_format((float) $transaction->balance_before, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">R$ {{ number_format((float) $transaction->balance_after, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $transaction->created_at?->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3 text-sm"><a href="{{ route('admin.wallet-transactions.show', $transaction) }}" class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Visualizar</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Nenhuma transação encontrada.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-100 px-4 py-3 dark:border-gray-800">{{ $transactions->links() }}</div>
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
