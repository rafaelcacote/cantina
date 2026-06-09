@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Extrato</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Histórico de movimentações das carteiras.</p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <form method="GET" action="{{ route('tenant.wallet-transactions.index') }}" class="grid grid-cols-1 gap-3 lg:grid-cols-5">
                <select name="student_id" class="h-11 rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90">
                    <option value="">Todos os alunos</option>
                    @foreach ($students as $student)
                        <option value="{{ $student->id }}" @selected($studentId === (int) $student->id)>{{ $student->name }}</option>
                    @endforeach
                </select>
                <select name="transaction_type" class="h-11 rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90">
                    <option value="">Todos os tipos</option>
                    @foreach ($transactionTypes as $key => $label)
                        <option value="{{ $key }}" @selected($transactionType === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <input type="date" name="from" value="{{ $from }}" class="h-11 rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90">
                <input type="date" name="to" value="{{ $to }}" class="h-11 rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90">
                <div class="flex gap-2">
                    <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Filtrar</button>
                    <a href="{{ route('tenant.wallet-transactions.index') }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Limpar</a>
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Aluno</th>
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
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $typeBadge }}">
                                        {{ $transactionTypes[$transaction->transaction_type] ?? $transaction->transaction_type }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">R$ {{ number_format((float) $transaction->amount, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">R$ {{ number_format((float) $transaction->balance_before, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">R$ {{ number_format((float) $transaction->balance_after, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $transaction->created_at?->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <a href="{{ route('tenant.wallet-transactions.show', $transaction) }}" class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Visualizar</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Nenhuma transação encontrada.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-100 px-4 py-3 dark:border-gray-800">{{ $transactions->links() }}</div>
        </div>
    </div>
@endsection
