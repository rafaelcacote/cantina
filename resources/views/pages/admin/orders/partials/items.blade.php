<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gray-50 dark:bg-gray-900/50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Produto</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Snapshot</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Qtd</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Preço unit.</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Total</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($order->items as $item)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $item->product?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $item->item_name_snapshot }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $item->quantity }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">R$ {{ number_format((float) $item->unit_price, 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">R$ {{ number_format((float) $item->total_price, 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $itemStatuses[$item->item_status] ?? $item->item_status }}</td>
                        <td class="px-4 py-3 text-sm">
                            <div class="space-y-2">
                                <form method="POST" action="{{ route('admin.orders.items.update', [$order, $item]) }}" class="grid grid-cols-1 gap-2 lg:grid-cols-6">
                                    @csrf
                                    @method('PUT')
                                    <select name="product_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-2 py-2 text-xs focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}" @selected((int) $item->product_id === (int) $product->id)>{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="number" min="1" name="quantity" value="{{ $item->quantity }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-2 py-2 text-xs focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                    <input type="number" min="0" step="0.01" name="unit_price" value="{{ $item->unit_price }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-2 py-2 text-xs focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                    <select name="item_status" class="w-full rounded-lg border border-gray-300 bg-transparent px-2 py-2 text-xs focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                        @foreach ($itemStatuses as $key => $label)
                                            <option value="{{ $key }}" @selected($item->item_status === $key)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" name="observation" value="{{ $item->observation }}" placeholder="Observação" class="w-full rounded-lg border border-gray-300 bg-transparent px-2 py-2 text-xs focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                    <input type="text" name="custom_request_text" value="{{ $item->custom_request_text }}" placeholder="Customização" class="w-full rounded-lg border border-gray-300 bg-transparent px-2 py-2 text-xs focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                    <div class="lg:col-span-6">
                                        <button type="submit" class="rounded-md bg-brand-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-brand-600">
                                            Atualizar item
                                        </button>
                                    </div>
                                </form>
                                <form method="POST" action="{{ route('admin.orders.items.destroy', [$order, $item]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-md border border-red-300 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-50 dark:border-red-700 dark:text-red-300 dark:hover:bg-red-900/20">
                                        Remover
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                            Nenhum item adicionado ao pedido.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
