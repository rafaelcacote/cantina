@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Detalhes do Estoque</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Visualize e ajuste o estoque do produto.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('tenant.stocks.edit', $stock) }}"
                   class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">
                    Editar
                </a>
                <a href="{{ route('tenant.stocks.index') }}"
                   class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
                    Voltar
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-900/40 dark:bg-green-900/20 dark:text-green-300">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-300">
                Verifique os campos do ajuste rápido.
            </div>
        @endif

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03] xl:col-span-2">
                <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Dados do Produto</h2>
                <dl class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div><dt class="text-xs uppercase text-gray-500 dark:text-gray-400">Produto</dt><dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $stock->product?->name ?? '-' }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500 dark:text-gray-400">Seção</dt><dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $stock->product?->section?->name ?? '-' }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500 dark:text-gray-400">Categoria</dt><dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $stock->product?->category?->name ?? '-' }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500 dark:text-gray-400">Estoque atual</dt><dd class="mt-1 text-sm font-semibold text-gray-800 dark:text-white/90">{{ $stock->quantity }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500 dark:text-gray-400">Reservado</dt><dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $stock->reserved_quantity }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500 dark:text-gray-400">Mínimo configurado</dt><dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $stock->product?->minimum_stock_alert ?? '-' }}</dd></div>
                    <div>
                        <dt class="text-xs uppercase text-gray-500 dark:text-gray-400">Status</dt>
                        @php $isLow = $stock->product && $stock->quantity <= $stock->product->minimum_stock_alert; @endphp
                        <dd class="mt-1">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $isLow ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' : 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' }}">
                                {{ $isLow ? 'Estoque baixo' : 'Normal' }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Ajuste Rápido</h2>
                <form method="POST" action="{{ route('tenant.stocks.adjust', $stock) }}" class="mt-5 space-y-4">
                    @csrf
                    <div>
                        <label for="movement_type" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Tipo *</label>
                        <select id="movement_type" name="movement_type" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            @foreach ($movementTypes as $key => $label)
                                <option value="{{ $key }}" @selected(old('movement_type') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('movement_type') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="quantity" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Quantidade *</label>
                        <input id="quantity" name="quantity" type="number" min="0" value="{{ old('quantity') }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        @error('quantity') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="description" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Descrição</label>
                        <textarea id="description" name="description" rows="3" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">{{ old('description') }}</textarea>
                        @error('description') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" class="w-full rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">
                        Aplicar Ajuste
                    </button>
                </form>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3 dark:border-gray-800">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Últimas movimentações</h3>
                <a href="{{ route('tenant.stock-movements.index', ['product_id' => $stock->product_id]) }}" class="text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">
                    Ver todas
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
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
                            <tr>
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
                                <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Sem movimentações recentes para este produto.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
