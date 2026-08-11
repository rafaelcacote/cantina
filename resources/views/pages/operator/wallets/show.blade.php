@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Carteira</h1>
                <p class="mt-1 text-sm text-gray-500">{{ $wallet->student?->name }}</p>
            </div>
            <a href="{{ route('operator.wallets.index') }}" class="text-sm font-medium text-brand-600">Voltar</a>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-sm text-gray-500">Saldo atual</p>
            <p class="mt-1 text-2xl font-semibold text-green-700">R$ {{ number_format((float) $wallet->balance, 2, ',', '.') }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <h2 class="text-base font-semibold">Últimas movimentações</h2>
            <ul class="mt-4 divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($wallet->transactions as $tx)
                    <li class="flex justify-between py-3 text-sm">
                        <span>{{ $tx->transaction_type }} — {{ $tx->description ?? '-' }}</span>
                        <span>R$ {{ number_format((float) $tx->amount, 2, ',', '.') }}</span>
                    </li>
                @empty
                    <li class="py-4 text-sm text-gray-500">Sem movimentações.</li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection
