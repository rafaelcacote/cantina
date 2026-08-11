@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Planos</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Catálogo de planos do SaaS.</p>
            </div>
            <a href="{{ route('admin.plans.create') }}" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Novo Plano</a>
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-900/40 dark:bg-green-900/20 dark:text-green-300">{{ session('success') }}</div>
        @endif

        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <form method="GET" action="{{ route('admin.plans.index') }}" class="mb-4">
                <div class="flex flex-col gap-3 sm:flex-row">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Buscar por nome ou slug..."
                           class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90">
                    <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 px-4 text-sm font-medium dark:border-gray-700 dark:text-gray-300">Buscar</button>
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead>
                    <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        <th class="px-4 py-3">Nome</th>
                        <th class="px-4 py-3">Preço</th>
                        <th class="px-4 py-3">Ciclo</th>
                        <th class="px-4 py-3">Ativo</th>
                        <th class="px-4 py-3 text-right">Ações</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($plans as $plan)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-800 dark:text-white/90">{{ $plan->name }} <span class="text-xs text-gray-400">{{ $plan->slug }}</span></td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">R$ {{ number_format((float) $plan->price, 2, ',', '.') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $billingCycles[$plan->billing_cycle] ?? $plan->billing_cycle }}</td>
                            <td class="px-4 py-3 text-sm">{{ $plan->active ? 'Sim' : 'Não' }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.plans.show', $plan) }}" class="rounded-md px-3 py-1.5 text-xs font-medium text-brand-600 hover:bg-brand-50">Visualizar</a>
                                <a href="{{ route('admin.plans.edit', $plan) }}" class="rounded-md px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-300">Editar</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">Nenhum plano encontrado.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $plans->links() }}</div>
        </div>
    </div>
@endsection
