@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">Movimentações de Estoque</h1>
            <a href="{{ route('admin.stocks.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                Ver Estoque
            </a>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <form method="GET" action="{{ route('admin.stock-movements.index') }}" class="grid grid-cols-1 gap-3 lg:grid-cols-6">
                <select id="tenant_filter" name="tenant_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="">Todos os tenants</option>
                    @foreach ($tenants as $tenant)
                        <option value="{{ $tenant->id }}" @selected($tenantId === (int) $tenant->id)>{{ $tenant->name }}</option>
                    @endforeach
                </select>

                <select id="product_filter" name="product_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="">Todos os produtos</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}" data-tenant-id="{{ $product->tenant_id }}" @selected($productId === (int) $product->id)>{{ $product->name }}</option>
                    @endforeach
                </select>

                <select name="movement_type" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="">Todos os tipos</option>
                    @foreach ($movementTypes as $key => $label)
                        <option value="{{ $key }}" @selected($movementType === $key)>{{ $label }}</option>
                    @endforeach
                </select>

                <input type="date" name="from" value="{{ $from }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                <input type="date" name="to" value="{{ $to }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">

                <div class="flex gap-2">
                    <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Filtrar</button>
                    <a href="{{ route('admin.stock-movements.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Limpar</a>
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Produto</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Tenant</th>
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
                            @php
                                $stockId = $movement->product?->stock?->id;
                            @endphp
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                    @if ($stockId)
                                        <a class="text-brand-600 hover:text-brand-700" href="{{ route('admin.stocks.show', ['stock' => $stockId]) }}">
                                            {{ $movement->product?->name ?? '-' }}
                                        </a>
                                    @else
                                        {{ $movement->product?->name ?? '-' }}
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $tenantNames[$movement->tenant_id] ?? '-' }}</td>
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
                                <td colspan="9" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Nenhuma movimentação encontrada.</td>
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

@push('scripts')
    <script>
        (function () {
            const tenant = document.getElementById('tenant_filter');
            const product = document.getElementById('product_filter');
            if (!tenant || !product) return;

            const syncProducts = () => {
                const tenantId = tenant.value;
                [...product.options].forEach((opt, idx) => {
                    if (idx === 0) return;
                    const match = !tenantId || opt.dataset.tenantId === tenantId;
                    opt.hidden = !match;
                    opt.disabled = !match;
                });
                if (product.selectedOptions[0]?.disabled) product.value = '';
            };

            tenant.addEventListener('change', syncProducts);
            syncProducts();
        })();
    </script>
@endpush
