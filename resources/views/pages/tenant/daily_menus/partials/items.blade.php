<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
            <thead class="bg-gray-50 dark:bg-gray-900/50">
                <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <th class="px-4 py-3">Produto</th>
                    <th class="px-4 py-3">Qtd planejada</th>
                    <th class="px-4 py-3">Qtd disponível</th>
                    <th class="px-4 py-3">Preço</th>
                    <th class="px-4 py-3">Ordem</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($dailyMenu->items->sortBy('sort_order') as $item)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $item->product?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $item->planned_quantity ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $item->available_quantity ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                            {{ $item->price_override !== null ? 'R$ '.number_format((float) $item->price_override, 2, ',', '.') : '-' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $item->sort_order }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $item->active ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' }}">
                                {{ $item->active ? 'Ativo' : 'Inativo' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="space-y-2">
                                <form method="POST" action="{{ route('tenant.daily-menus.items.update', [$dailyMenu, $item]) }}" class="grid grid-cols-1 gap-2 lg:grid-cols-6">
                                    @csrf
                                    @method('PUT')
                                    <select name="product_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-2 py-2 text-xs focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}" @selected((int) $item->product_id === (int) $product->id)>
                                                {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="number" name="planned_quantity" min="0" value="{{ $item->planned_quantity }}" placeholder="Planejada" class="w-full rounded-lg border border-gray-300 bg-transparent px-2 py-2 text-xs focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                    <input type="number" name="available_quantity" min="0" value="{{ $item->available_quantity }}" placeholder="Disponível" class="w-full rounded-lg border border-gray-300 bg-transparent px-2 py-2 text-xs focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                    <input type="number" step="0.01" min="0" name="price_override" value="{{ $item->price_override }}" placeholder="Preço" class="w-full rounded-lg border border-gray-300 bg-transparent px-2 py-2 text-xs focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                    <input type="number" min="0" name="sort_order" value="{{ $item->sort_order }}" placeholder="Ordem" class="w-full rounded-lg border border-gray-300 bg-transparent px-2 py-2 text-xs focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                    <label class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-2 py-2 text-xs text-gray-700 dark:border-gray-700 dark:text-gray-300">
                                        <input type="checkbox" name="active" value="1" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" @checked($item->active)>
                                        Ativo
                                    </label>
                                    <div class="lg:col-span-6">
                                        <button type="submit" class="rounded-md bg-brand-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-brand-600">
                                            Atualizar item
                                        </button>
                                    </div>
                                </form>
                                <form method="POST" action="{{ route('tenant.daily-menus.items.destroy', [$dailyMenu, $item]) }}">
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
                            Nenhum produto adicionado neste cardápio.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
