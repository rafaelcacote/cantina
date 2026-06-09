@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Detalhes da Transação</h1>
            <a href="{{ route('tenant.wallet-transactions.index') }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Voltar</a>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <dl class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div><dt class="text-xs uppercase text-gray-500">Aluno</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $transaction->student?->name ?? '-' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Tipo</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $transactionTypes[$transaction->transaction_type] ?? $transaction->transaction_type }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Valor</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">R$ {{ number_format((float) $transaction->amount, 2, ',', '.') }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Saldo antes</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">R$ {{ number_format((float) $transaction->balance_before, 2, ',', '.') }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Saldo depois</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">R$ {{ number_format((float) $transaction->balance_after, 2, ',', '.') }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Referência</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $transaction->reference_type ?: '-' }} {{ $transaction->reference_id ? '#'.$transaction->reference_id : '' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Criado por</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $transaction->creator?->name ?? '-' }}</dd></div>
                <div class="md:col-span-2"><dt class="text-xs uppercase text-gray-500">Descrição</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $transaction->description ?: '-' }}</dd></div>
            </dl>
        </div>
    </div>
@endsection
