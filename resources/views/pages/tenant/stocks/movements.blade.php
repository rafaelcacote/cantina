@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Movimentações de Estoque</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Histórico de movimentações do seu tenant.</p>
            </div>
            <a href="{{ route('tenant.stocks.index') }}"
               class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
                Ver Estoque
            </a>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <form method="GET" action="{{ route('tenant.stock-movements.index') }}" class="grid grid-cols-1 gap-3 lg:grid-cols-5">
                <select name="product_id"
                        class="h-11 rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90 lg:col-span-2">
                    <option value="">Todos os produtos</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}" @selected($productId === (int) $product->id)>{{ $product->name }}</option>
                    @endforeach
                </select>

                <select name="movement_type"
                        class="h-11 rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
                    <option value="">Todos os tipos</option>
                    @foreach ($movementTypes as $key => $label)
                        <option value="{{ $key }}" @selected($movementType === $key)>{{ $label }}</option>
                    @endforeach
                </select>

                <input type="date" name="from" value="{{ $from }}"
                       class="h-11 rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">

                <input type="date" name="to" value="{{ $to }}"
                       class="h-11 rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">

                <div class="lg:col-span-5 flex justify-end gap-2">
                    <button type="submit"
                            class="inline-flex h-11 items-center justify-center rounded-lg bg-brand-500 px-4 text-sm font-medium text-white hover:bg-brand-600">
                        Filtrar
                    </button>
                    <a href="{{ route('tenant.stock-movements.index') }}"
                       class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
                        Limpar
                    </a>
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            <th class="px-4 py-3">Produto</th>
                            <th class="px-4 py-3">Tipo</th>
                            <th class="px-4 py-3">Qtd</th>
                            <th class="px-4 py-3">Anterior</th>
                            <th class="px-4 py-3">Nova</th>
                            <th class="px-4 py-3">Descrição</th>
                            <th class="px-4 py-3">Usuário</th>
                            <th class="px-4 py-3">Data</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($movements as $movement)
                            @php
                                $stockId = $movement->product?->stock?->id;
                            @endphp
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                    @if ($stockId)
                                        <a class="font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400" href="{{ route('tenant.stocks.show', ['stock' => $stockId]) }}">
                                            {{ $movement->product?->name ?? '-' }}
                                        </a>
                                    @else
                                        {{ $movement->product?->name ?? '-' }}
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $movementTypes[$movement->movement_type] ?? $movement->movement_type }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $movement->quantity }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $movement->previous_quantity }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $movement->new_quantity }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $movement->description ?: '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $movement->creator?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $movement->created_at?->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Nenhuma movimentação encontrada.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-100 px-4 py-3 dark:border-gray-800">
                {{ $movements->links() }}
            </div>
        </div>
    </div>
@endsection
