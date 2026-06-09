@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">Dashboard do Tenant</h1>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total de escolas</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ $metrics['total_schools'] }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total de alunos</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ $metrics['total_students'] }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total de responsáveis</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ $metrics['total_parents'] }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total de produtos</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ $metrics['total_products'] }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total de pedidos</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ $metrics['total_orders'] }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-sm text-gray-500 dark:text-gray-400">Pedidos pendentes</p>
                <p class="mt-2 text-2xl font-semibold text-amber-600 dark:text-amber-400">{{ $metrics['pending_orders'] }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-sm text-gray-500 dark:text-gray-400">Saldo total em carteiras</p>
                <p class="mt-2 text-2xl font-semibold text-green-700 dark:text-green-400">R$ {{ number_format($metrics['wallets_balance'], 2, ',', '.') }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total em fiado aberto</p>
                <p class="mt-2 text-2xl font-semibold text-red-700 dark:text-red-400">R$ {{ number_format($metrics['open_tab_balance'], 2, ',', '.') }}</p>
            </div>
        </div>
    </div>
@endsection
