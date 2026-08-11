@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="flex items-center gap-2.5 text-2xl font-semibold text-gray-800 dark:text-white/90">
                <span class="inline-flex size-8 items-center justify-center text-brand-500 dark:text-brand-400">
                    {!! \App\Helpers\MenuHelper::getIconSvg('charts') !!}
                </span>
                Extrato
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Histórico de movimentações das carteiras.</p>
        </div>

        @if ($errors->any())
            <div class="rounded-xl border border-error-500 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/15 dark:text-error-400">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <form method="GET" action="{{ route('tenant.wallet-transactions.index') }}" class="mb-4 flex w-full flex-row flex-wrap gap-3">
                <select name="student_id"
                        class="h-11 min-w-0 flex-[1.4] basis-0 rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
                    <option value="">Todos os alunos</option>
                    @foreach ($students as $student)
                        <option value="{{ $student->id }}" @selected($studentId === (int) $student->id)>{{ $student->name }}</option>
                    @endforeach
                </select>

                <select name="transaction_type"
                        class="h-11 min-w-0 flex-1 basis-0 rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
                    <option value="">Todos os tipos</option>
                    @foreach ($transactionTypes as $key => $label)
                        <option value="{{ $key }}" @selected($transactionType === $key)>{{ $label }}</option>
                    @endforeach
                </select>

                <input type="date"
                       name="from"
                       value="{{ $from }}"
                       title="Data inicial"
                       class="h-11 min-w-0 flex-1 basis-0 rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">

                <input type="date"
                       name="to"
                       value="{{ $to }}"
                       title="Data final"
                       class="h-11 min-w-0 flex-1 basis-0 rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">

                <button type="submit"
                        class="inline-flex h-11 shrink-0 items-center justify-center rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
                    Filtrar
                </button>
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead>
                    <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        <th class="px-4 py-3">Aluno</th>
                        <th class="px-4 py-3">Tipo</th>
                        <th class="px-4 py-3">Valor</th>
                        <th class="px-4 py-3">Antes</th>
                        <th class="px-4 py-3">Depois</th>
                        <th class="px-4 py-3">Data</th>
                        <th class="px-4 py-3 text-right">Ações</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($transactions as $transaction)
                        @php
                            $typeBadge = match ($transaction->transaction_type) {
                                'debit' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                                'refund' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                                'adjustment' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
                                default => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                            };
                        @endphp
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-800 dark:text-white/90">{{ $transaction->student?->name ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $typeBadge }}">
                                    {{ $transactionTypes[$transaction->transaction_type] ?? $transaction->transaction_type }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">R$ {{ number_format((float) $transaction->amount, 2, ',', '.') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">R$ {{ number_format((float) $transaction->balance_before, 2, ',', '.') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">R$ {{ number_format((float) $transaction->balance_after, 2, ',', '.') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $transaction->created_at?->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('tenant.wallet-transactions.show', $transaction) }}"
                                       title="Visualizar"
                                       class="inline-flex size-10 items-center justify-center rounded-lg text-brand-500 transition-colors hover:bg-brand-50 hover:text-brand-700 dark:text-brand-400 dark:hover:bg-white/5 dark:hover:text-brand-300">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <path d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12s-3.75 6.75-9.75 6.75S2.25 12 2.25 12Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <circle cx="12" cy="12" r="2.75" stroke="currentColor" stroke-width="1.5"/>
                                        </svg>
                                        <span class="sr-only">Visualizar</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                Nenhuma transação encontrada.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
@endsection
