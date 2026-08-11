@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="flex items-center gap-2.5 text-2xl font-semibold text-gray-800 dark:text-white/90">
                    <span class="inline-flex size-8 items-center justify-center text-brand-500 dark:text-brand-400">
                        {!! \App\Helpers\MenuHelper::getIconSvg('tables') !!}
                    </span>
                    Movimentações de Estoque
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Histórico de movimentações do seu tenant.</p>
            </div>
            <a href="{{ route('tenant.stocks.index') }}"
               class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-transparent dark:text-gray-300 dark:hover:bg-white/5">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Voltar ao Estoque
            </a>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <form method="GET" action="{{ route('tenant.stock-movements.index') }}" class="mb-4 flex w-full flex-row flex-wrap gap-3">
                <select name="product_id"
                        class="h-11 min-w-0 flex-[1.5] basis-0 rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
                    <option value="">Todos os produtos</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}" @selected($productId === (int) $product->id)>{{ $product->name }}</option>
                    @endforeach
                </select>

                <select name="movement_type"
                        class="h-11 min-w-0 flex-1 basis-0 rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
                    <option value="">Todos os tipos</option>
                    @foreach ($movementTypes as $key => $label)
                        <option value="{{ $key }}" @selected($movementType === $key)>{{ $label }}</option>
                    @endforeach
                </select>

                <input type="date" name="from" value="{{ $from }}"
                       class="h-11 min-w-0 flex-1 basis-0 rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">

                <input type="date" name="to" value="{{ $to }}"
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
                            <td class="px-4 py-3 text-sm font-medium text-gray-800 dark:text-white/90">
                                @if ($stockId)
                                    <a class="text-brand-600 hover:text-brand-700 dark:text-brand-400" href="{{ route('tenant.stocks.show', ['stock' => $stockId]) }}">
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

            <div class="mt-4">
                {{ $movements->links() }}
            </div>
        </div>
    </div>
@endsection
