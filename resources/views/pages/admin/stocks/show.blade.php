@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">Detalhes do Estoque</h1>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.stocks.edit', $stock) }}" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Editar</a>
                <a href="{{ route('admin.stocks.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Voltar</a>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800/40 dark:bg-green-900/20 dark:text-green-300">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800/40 dark:bg-red-900/20 dark:text-red-300">
                Verifique os campos do ajuste rápido.
            </div>
        @endif

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="xl:col-span-2 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Dados do Produto</h2>
                <dl class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div><dt class="text-xs uppercase text-gray-500">Produto</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $stock->product?->name ?? '-' }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Tenant</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $tenantName ?? '-' }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Seção</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $stock->product?->section?->name ?? '-' }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Categoria</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $stock->product?->category?->name ?? '-' }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Estoque atual</dt><dd class="mt-1 text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $stock->quantity }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Reservado</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $stock->reserved_quantity }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Mínimo configurado</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $stock->product?->minimum_stock_alert ?? '-' }}</dd></div>
                    <div>
                        <dt class="text-xs uppercase text-gray-500">Status</dt>
                        @php $isLow = $stock->product && $stock->quantity <= $stock->product->minimum_stock_alert; @endphp
                        <dd class="mt-1">
                            <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $isLow ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/20 dark:text-amber-300' : 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-300' }}">
                                {{ $isLow ? 'Estoque baixo' : 'Normal' }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Ajuste Rápido</h2>
                <form method="POST" action="{{ route('admin.stocks.adjust', $stock) }}" class="mt-5 space-y-4">
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
                <a href="{{ route('admin.stock-movements.index', ['product_id' => $stock->product_id, 'tenant_id' => $stock->tenant_id]) }}" class="text-sm text-brand-600 hover:text-brand-700">
                    Ver todas
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Tipo</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Qtd</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Anterior</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Nova</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Descrição</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Usuário</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Data</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($movements as $movement)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $movementTypes[$movement->movement_type] ?? $movement->movement_type }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $movement->quantity }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $movement->previous_quantity }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $movement->new_quantity }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $movement->description ?: '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $movement->creator?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $movement->created_at?->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Sem movimentações recentes para este produto.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
