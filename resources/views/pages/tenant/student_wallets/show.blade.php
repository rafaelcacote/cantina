@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Detalhes da Carteira</h1>
            <div class="flex gap-2">
                <a href="{{ route('tenant.student-wallets.edit', $wallet) }}" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Editar</a>
                <a href="{{ route('tenant.student-wallets.index') }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Voltar</a>
            </div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <dl class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div><dt class="text-xs uppercase text-gray-500">Aluno</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $wallet->student?->name ?? '-' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Saldo</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">R$ {{ number_format((float) $wallet->balance, 2, ',', '.') }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Limite de crédito</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">R$ {{ number_format((float) $wallet->credit_limit, 2, ',', '.') }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Permite negativo</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $wallet->allow_negative_balance ? 'Sim' : 'Não' }}</dd></div>
            </dl>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Extrato</h2>
                <a href="{{ route('tenant.wallet-transactions.index', ['student_id' => $wallet->student_id]) }}" class="text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">Ver todos</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Tipo</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Valor</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Saldo depois</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Data</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($wallet->transactions as $transaction)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $transactionTypes[$transaction->transaction_type] ?? $transaction->transaction_type }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">R$ {{ number_format((float) $transaction->amount, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">R$ {{ number_format((float) $transaction->balance_after, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $transaction->created_at?->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Nenhuma movimentação registrada.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
