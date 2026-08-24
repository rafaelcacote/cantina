@extends('layouts.app')

@section('content')
    <div class="space-y-5">
        <div>
            <h1 class="text-lg font-semibold text-gray-800 dark:text-white/90">Caixa</h1>
            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                Painel do operador{{ $schoolName ? ' — '.$schoolName : '' }}
            </p>
        </div>

        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <div class="rounded-xl border border-gray-200 bg-white px-3.5 py-3 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-xs text-gray-500">Pedidos pendentes</p>
                <p class="mt-1 text-lg font-semibold tabular-nums text-amber-600">{{ $metrics['pending_orders'] }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white px-3.5 py-3 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-xs text-gray-500">Pedidos hoje</p>
                <p class="mt-1 text-lg font-semibold tabular-nums text-gray-900 dark:text-white">{{ $metrics['today_orders'] }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white px-3.5 py-3 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-xs text-gray-500">Alunos</p>
                <p class="mt-1 text-lg font-semibold tabular-nums text-gray-900 dark:text-white">{{ $metrics['students'] }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white px-3.5 py-3 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-xs text-gray-500">Saldo carteiras</p>
                <p class="mt-1 text-lg font-semibold tabular-nums text-green-700">R$ {{ number_format($metrics['wallets_balance'], 2, ',', '.') }}</p>
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('operator.pos.index') }}" class="inline-flex rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Abrir PDV</a>
            <a href="{{ route('operator.orders.create') }}" class="inline-flex rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium dark:border-gray-700 dark:text-gray-300">Pedido manual</a>
            <a href="{{ route('operator.orders.index') }}" class="inline-flex rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium dark:border-gray-700 dark:text-gray-300">Ver pedidos</a>
            <a href="{{ route('operator.students.index') }}" class="inline-flex rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium dark:border-gray-700 dark:text-gray-300">Consultar aluno</a>
        </div>
    </div>
@endsection
