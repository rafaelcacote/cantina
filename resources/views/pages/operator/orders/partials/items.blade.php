@php
    $itemInput = fn (string $field = '') => 'h-11 w-full rounded-lg border bg-transparent px-3 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:text-white/90 '.($field && $errors->has($field) ? 'border-error-500 dark:border-error-500' : 'border-gray-300 dark:border-gray-700');
    $openAddForm = $order->status === 'pending' && ($order->items->isEmpty() || $errors->hasAny(['product_id', 'quantity']));
    $itemCount = $order->items->count();
    $canAddItems = $order->status === 'pending';
@endphp

<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
     x-data="{ adding: @js($openAddForm) }">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-5 py-4 dark:border-gray-800">
        <div>
            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Itens do pedido</h2>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                {{ $itemCount }} {{ $itemCount === 1 ? 'item' : 'itens' }}
            </p>
        </div>
        @if ($canAddItems)
            <button type="button"
                    @click="adding = !adding"
                    class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-transparent dark:text-gray-300 dark:hover:bg-white/5">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <span x-text="adding ? 'Fechar' : 'Adicionar item'"></span>
            </button>
        @endif
    </div>

    <div class="divide-y divide-gray-100 dark:divide-gray-800">
        @forelse ($order->items as $item)
            <div class="px-5 py-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                            <span class="tabular-nums text-gray-500 dark:text-gray-400">{{ $item->quantity }}×</span>
                            {{ $item->product?->name ?? $item->item_name_snapshot }}
                        </p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            R$ {{ number_format((float) $item->unit_price, 2, ',', '.') }} un.
                            @if ($item->observation)
                                <span class="text-gray-300 dark:text-gray-600">·</span>
                                {{ $item->observation }}
                            @endif
                        </p>
                    </div>
                    <p class="shrink-0 text-sm font-semibold tabular-nums text-gray-800 dark:text-white/90">
                        R$ {{ number_format((float) $item->total_price, 2, ',', '.') }}
                    </p>
                </div>
            </div>
        @empty
            <div class="px-5 py-10 text-center">
                <span class="mx-auto mb-3 inline-flex size-12 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-white/5">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M4 6h16M4 12h16M4 18h10" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                    </svg>
                </span>
                <p class="text-sm font-medium text-gray-800 dark:text-white/90">Nenhum item neste pedido</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Adicione produtos para confirmar e preparar o pedido.</p>
            </div>
        @endforelse
    </div>

    @if ($canAddItems)
        <div x-show="adding" x-cloak class="border-t border-gray-200 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">Novo item</h3>
            <form method="POST" action="{{ route('operator.orders.items.store', $order) }}" class="mt-4 space-y-4" novalidate>
                @csrf
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Produto <span class="text-error-500">*</span>
                    </label>
                    <select name="product_id" class="{{ $itemInput('product_id') }}">
                        <option value="">Selecione</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" @selected((string) old('product_id') === (string) $product->id)>
                                {{ $product->name }} — R$ {{ number_format((float) $product->price, 2, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                    @error('product_id')
                        <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Quantidade <span class="text-error-500">*</span>
                    </label>
                    <input type="number" min="1" name="quantity" value="{{ old('quantity', 1) }}" class="{{ $itemInput('quantity') }}">
                    @error('quantity')
                        <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 text-sm font-medium text-white transition-colors hover:bg-brand-600">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        Adicionar item
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
