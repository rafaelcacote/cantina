@extends('layouts.app')

@section('content')
    <div class="space-y-5">
        <div>
            <h1 class="text-lg font-semibold text-gray-800 dark:text-white/90">Dashboard</h1>
            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Visão geral da cantina</p>
        </div>

        <section class="space-y-2">
            <h2 class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Cadastros</h2>
            <div class="flex flex-row gap-3">
                <div class="min-w-0 flex-1 rounded-xl border border-gray-200 bg-white px-3.5 py-3 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Escolas</p>
                    <p class="mt-1 text-lg font-semibold tabular-nums text-gray-900 dark:text-white">{{ $metrics['total_schools'] }}</p>
                </div>
                <div class="min-w-0 flex-1 rounded-xl border border-gray-200 bg-white px-3.5 py-3 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Alunos</p>
                    <p class="mt-1 text-lg font-semibold tabular-nums text-gray-900 dark:text-white">{{ $metrics['total_students'] }}</p>
                </div>
                <div class="min-w-0 flex-1 rounded-xl border border-gray-200 bg-white px-3.5 py-3 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Responsáveis</p>
                    <p class="mt-1 text-lg font-semibold tabular-nums text-gray-900 dark:text-white">{{ $metrics['total_parents'] }}</p>
                </div>
                <div class="min-w-0 flex-1 rounded-xl border border-gray-200 bg-white px-3.5 py-3 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Produtos</p>
                    <p class="mt-1 text-lg font-semibold tabular-nums text-gray-900 dark:text-white">{{ $metrics['total_products'] }}</p>
                </div>
            </div>
        </section>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            <section class="space-y-2">
                <h2 class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Pedidos</h2>
                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-xl border border-gray-200 bg-white px-3.5 py-3 dark:border-gray-800 dark:bg-white/[0.03]">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Total</p>
                        <p class="mt-1 text-lg font-semibold tabular-nums text-gray-900 dark:text-white">{{ $metrics['total_orders'] }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-white px-3.5 py-3 dark:border-gray-800 dark:bg-white/[0.03]">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Pendentes</p>
                        <p class="mt-1 text-lg font-semibold tabular-nums text-amber-600 dark:text-amber-400">{{ $metrics['pending_orders'] }}</p>
                    </div>
                </div>
            </section>

            <section class="space-y-2">
                <h2 class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Financeiro</h2>
                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-xl border border-gray-200 bg-white px-3.5 py-3 dark:border-gray-800 dark:bg-white/[0.03]">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Carteiras</p>
                        <p class="mt-1 text-lg font-semibold tabular-nums text-green-700 dark:text-green-400">R$ {{ number_format($metrics['wallets_balance'], 2, ',', '.') }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-white px-3.5 py-3 dark:border-gray-800 dark:bg-white/[0.03]">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Fiado aberto</p>
                        <p class="mt-1 text-lg font-semibold tabular-nums text-red-700 dark:text-red-400">R$ {{ number_format($metrics['open_tab_balance'], 2, ',', '.') }}</p>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection
