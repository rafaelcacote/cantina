<div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
    <h2 class="mb-4 text-base font-semibold text-gray-800 dark:text-white/90">Itens do cardápio</h2>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
            <thead>
            <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                <th class="px-4 py-3">Produto</th>
                <th class="px-4 py-3">Qtd planejada</th>
                <th class="px-4 py-3">Qtd disponível</th>
                <th class="px-4 py-3">Preço</th>
                <th class="px-4 py-3">Ordem</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3 text-right">Ações</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse ($dailyMenu->items->sortBy('sort_order') as $item)
                <tr>
                    <td colspan="7" class="px-4 py-4">
                        <div class="flex flex-col gap-3">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div>
                                    <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $item->product?->name ?? '-' }}</p>
                                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                        Planejada: {{ $item->planned_quantity ?? '-' }}
                                        · Disponível: {{ $item->available_quantity ?? '-' }}
                                        · Preço: {{ $item->price_override !== null ? 'R$ '.number_format((float) $item->price_override, 2, ',', '.') : '-' }}
                                        · Ordem: {{ $item->sort_order }}
                                    </p>
                                </div>
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $item->active ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                                    {{ $item->active ? 'Ativo' : 'Inativo' }}
                                </span>
                            </div>

                            <form method="POST" action="{{ route('tenant.daily-menus.items.update', [$dailyMenu, $item]) }}" class="flex flex-col gap-3 rounded-xl border border-gray-200 p-3 dark:border-gray-800" novalidate>
                                @csrf
                                @method('PUT')
                                <div class="flex w-full flex-row flex-wrap gap-2">
                                    <select name="product_id"
                                            class="h-10 min-w-0 flex-[1.5] basis-40 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}" @selected((int) $item->product_id === (int) $product->id)>
                                                {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="number" name="planned_quantity" min="0" value="{{ $item->planned_quantity }}" placeholder="Planejada"
                                           class="h-10 min-w-0 flex-1 basis-24 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
                                    <input type="number" name="available_quantity" min="0" value="{{ $item->available_quantity }}" placeholder="Disponível"
                                           class="h-10 min-w-0 flex-1 basis-24 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
                                    <input type="number" step="0.01" min="0" name="price_override" value="{{ $item->price_override }}" placeholder="Preço"
                                           class="h-10 min-w-0 flex-1 basis-24 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
                                    <input type="number" min="0" name="sort_order" value="{{ $item->sort_order }}" placeholder="Ordem"
                                           class="h-10 min-w-0 flex-1 basis-20 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
                                    <select name="active"
                                            class="h-10 min-w-0 flex-1 basis-24 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
                                        <option value="1" @selected($item->active)>Ativo</option>
                                        <option value="0" @selected(! $item->active)>Inativo</option>
                                    </select>
                                </div>
                                <div class="flex items-center justify-end gap-2">
                                    <button type="submit"
                                            class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3 text-xs font-medium text-white hover:bg-brand-600">
                                        Salvar item
                                    </button>
                                </div>
                            </form>

                            <form method="POST" action="{{ route('tenant.daily-menus.items.destroy', [$dailyMenu, $item]) }}" class="flex justify-end" onsubmit="return confirm('Remover este item do cardápio?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        title="Remover"
                                        class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg border border-error-500/40 px-3 text-xs font-medium text-error-600 transition-colors hover:bg-error-50 dark:border-error-500/30 dark:text-error-400 dark:hover:bg-error-500/15">
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
