@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Pedidos</h1>
                <p class="mt-1 text-sm text-gray-500">Caixa / atendimento</p>
            </div>
            <a href="{{ route('operator.orders.create') }}" class="inline-flex rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Novo Pedido</a>
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
        @endif

        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <form method="GET" class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
                <input type="text" name="search" value="{{ $search }}" placeholder="ID ou aluno..." class="h-11 rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90 sm:col-span-2">
                <select name="status" class="h-11 rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90">
                    <option value="">Todos os status</option>
                    @foreach($statuses as $key => $label)
                        <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium dark:border-gray-700 dark:text-gray-300 sm:col-span-3 sm:w-auto">Filtrar</button>
            </form>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead>
                    <tr class="text-left text-xs uppercase text-gray-500">
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Aluno</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Total</th>
                        <th class="px-4 py-3 text-right">Ações</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($orders as $order)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium">{{ $order->id }}</td>
                            <td class="px-4 py-3 text-sm">{{ $order->student?->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm">{{ $statuses[$order->status] ?? $order->status }}</td>
                            <td class="px-4 py-3 text-sm">R$ {{ number_format((float) $order->final_amount, 2, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('operator.orders.show', $order) }}" class="text-xs font-medium text-brand-600">Abrir</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">Nenhum pedido.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $orders->links() }}</div>
        </div>
    </div>
@endsection
