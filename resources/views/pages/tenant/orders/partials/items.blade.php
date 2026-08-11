@php
    $itemInput = fn (string $field = '') => 'h-11 w-full rounded-lg border bg-transparent px-3 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:text-white/90 '.($field && $errors->has($field) ? 'border-error-500 dark:border-error-500' : 'border-gray-300 dark:border-gray-700');
    $openAddForm = $order->items->isEmpty() || $errors->hasAny(['product_id', 'quantity', 'unit_price']);
    $itemCount = $order->items->count();
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
        <button type="button"
                @click="adding = !adding"
                class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-transparent dark:text-gray-300 dark:hover:bg-white/5">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <span x-text="adding ? 'Fechar' : 'Adicionar item'"></span>
        </button>
    </div>

    <div class="divide-y divide-gray-100 dark:divide-gray-800">
        @forelse ($order->items as $item)
            @php
                $itemBadge = match ($item->item_status) {
                    'delivered' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                    'cancelled' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                    'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
                    default => 'bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-300',
                };
            @endphp
            <div class="px-5 py-4" x-data="{ editing: false }">
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
                            @if ($item->custom_request_text)
                                <span class="text-gray-300 dark:text-gray-600">·</span>
                                {{ $item->custom_request_text }}
                            @endif
                        </p>
                    </div>
                    <div class="flex shrink-0 flex-col items-end gap-2">
                        <p class="text-sm font-semibold tabular-nums text-gray-800 dark:text-white/90">
                            R$ {{ number_format((float) $item->total_price, 2, ',', '.') }}
                        </p>
                        <span class="inline-flex rounded-full px-2 py-0.5 text-[11px] font-medium {{ $itemBadge }}">
                            {{ $itemStatuses[$item->item_status] ?? $item->item_status }}
                        </span>
                    </div>
                </div>

                <div class="mt-3 flex items-center justify-end gap-1">
                    <button type="button"
                            @click="editing = !editing"
                            title="Editar item"
                            class="inline-flex size-9 items-center justify-center rounded-lg text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M4 20h4l10.5-10.5a2.121 2.121 0 0 0-3-3L5 17v3Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M13.5 6.5l3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="sr-only">Editar item</span>
                    </button>
                    <button type="button"
                            title="Remover item"
                            data-name="{{ $item->product?->name ?? $item->item_name_snapshot }}"
                            data-action="{{ route('tenant.orders.items.destroy', [$order, $item]) }}"
                            @click="$dispatch('open-confirm-delete', {
                                name: $el.dataset.name,
                                action: $el.dataset.action,
                                title: 'Remover item?',
                                description: 'O item será removido deste pedido.',
                                confirmLabel: 'Remover'
                            })"
                            class="inline-flex size-9 items-center justify-center rounded-lg text-gray-500 transition-colors hover:bg-error-50 hover:text-error-600 dark:text-gray-400 dark:hover:bg-error-500/10 dark:hover:text-error-400">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M3 6h18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M8 6V4.5A1.5 1.5 0 0 1 9.5 3h5A1.5 1.5 0 0 1 16 4.5V6M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="sr-only">Remover item</span>
                    </button>
                </div>

                <form method="POST"
                      action="{{ route('tenant.orders.items.update', [$order, $item]) }}"
                      x-show="editing"
                      x-cloak
                      class="mt-3 space-y-3 rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-white/[0.03]"
                      novalidate>
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-300">Produto</label>
                        <select name="product_id" class="{{ $itemInput() }}">
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" @selected((int) $item->product_id === (int) $product->id)>
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-row gap-3">
                        <div class="min-w-0 flex-1">
                            <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-300">Qtd</label>
                            <input type="number" min="1" name="quantity" value="{{ $item->quantity }}" class="{{ $itemInput() }}">
                        </div>
                        <div class="min-w-0 flex-1">
                            <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-300">Preço un.</label>
                            <input type="number" min="0" step="0.01" name="unit_price" value="{{ $item->unit_price }}" class="{{ $itemInput() }}">
                        </div>
                        <div class="min-w-0 flex-1">
                            <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-300">Status</label>
                            <select name="item_status" class="{{ $itemInput() }}">
                                @foreach ($itemStatuses as $key => $label)
                                    <option value="{{ $key }}" @selected($item->item_status === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="flex flex-row gap-3">
                        <div class="min-w-0 flex-1">
                            <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-300">Observação</label>
                            <input type="text" name="observation" value="{{ $item->observation }}" class="{{ $itemInput() }}">
                        </div>
                        <div class="min-w-0 flex-1">
                            <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-300">Customização</label>
                            <input type="text" name="custom_request_text" value="{{ $item->custom_request_text }}" class="{{ $itemInput() }}">
                        </div>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button"
                                @click="editing = false"
                                class="inline-flex h-9 items-center rounded-lg px-3 text-xs font-medium text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="inline-flex h-9 items-center rounded-lg bg-brand-500 px-3 text-xs font-medium text-white hover:bg-brand-600">
                            Salvar item
                        </button>
                    </div>
                </form>
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

    <div x-show="adding" x-cloak class="border-t border-gray-200 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
        <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">Novo item</h3>
        <form method="POST" action="{{ route('tenant.orders.items.store', $order) }}" class="mt-4 space-y-4" novalidate>
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

            <div class="flex flex-row gap-3">
                <div class="min-w-0 flex-1">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Quantidade <span class="text-error-500">*</span>
                    </label>
                    <input type="number" min="1" name="quantity" value="{{ old('quantity', 1) }}" class="{{ $itemInput('quantity') }}">
                    @error('quantity')
                        <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
                    @enderror
                </div>
                <div class="min-w-0 flex-1">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Preço unitário</label>
                    <input type="number" min="0" step="0.01" name="unit_price" value="{{ old('unit_price') }}" placeholder="Padrão do produto" class="{{ $itemInput('unit_price') }}">
                </div>
                <div class="min-w-0 flex-1">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Status <span class="text-error-500">*</span>
                    </label>
                    <select name="item_status" class="{{ $itemInput('item_status') }}">
                        @foreach ($itemStatuses as $key => $label)
                            <option value="{{ $key }}" @selected(old('item_status', 'pending') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex flex-row gap-3">
                <div class="min-w-0 flex-1">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Observação</label>
                    <input type="text" name="observation" value="{{ old('observation') }}" placeholder="Ex.: sem cebola" class="{{ $itemInput() }}">
                </div>
                <div class="min-w-0 flex-1">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Pedido customizado</label>
                    <input type="text" name="custom_request_text" value="{{ old('custom_request_text') }}" placeholder="Opcional" class="{{ $itemInput() }}">
                </div>
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
</div>
