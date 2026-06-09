@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">Detalhes do Pedido Pré-definido #{{ $order->id }}</h1>
            <div class="flex gap-2">
                <a href="{{ route('admin.parental-preselected-orders.edit', $order) }}" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Editar</a>
                <a href="{{ route('admin.parental-preselected-orders.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Voltar</a>
            </div>
        </div>
        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800/40 dark:bg-green-900/20 dark:text-green-300">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800/40 dark:bg-red-900/20 dark:text-red-300">Verifique os campos do item.</div>
        @endif

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="xl:col-span-2 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Dados do Pedido Pré-definido</h2>
                <dl class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div><dt class="text-xs uppercase text-gray-500">Tenant</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $tenantName ?? '-' }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Escola</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $order->school?->name ?? '-' }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Responsável</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $order->parent?->name ?? '-' }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Aluno</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $order->student?->name ?? '-' }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Data</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $order->order_date?->format('d/m/Y') }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Status</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $statuses[$order->status] ?? $order->status }}</dd></div>
                    <div class="md:col-span-2"><dt class="text-xs uppercase text-gray-500">Observações</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $order->notes ?: '-' }}</dd></div>
                </dl>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Adicionar Item</h2>
                <form method="POST" action="{{ route('admin.parental-preselected-orders.items.store', $order) }}" class="mt-4 space-y-3">
                    @csrf
                    <select name="product_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">Selecione o produto</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                    <input type="number" min="1" name="quantity" value="1" class="w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" placeholder="Quantidade">
                    <textarea name="notes" rows="2" class="w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" placeholder="Observação"></textarea>
                    <button type="submit" class="w-full rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Adicionar item</button>
                </form>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Produto</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Quantidade</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Observações</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($order->items as $item)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $item->product?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $item->quantity }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $item->notes ?: '-' }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <div class="flex gap-2">
                                        <form method="POST" action="{{ route('admin.parental-preselected-orders.items.update', [$order, $item]) }}" class="flex items-center gap-2">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="product_id" value="{{ $item->product_id }}">
                                            <input type="number" min="1" name="quantity" value="{{ $item->quantity }}" class="w-20 rounded-md border border-gray-300 px-2 py-1 text-xs dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                            <button type="submit" class="rounded-md bg-brand-500 px-2 py-1 text-xs font-medium text-white hover:bg-brand-600">Salvar</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.parental-preselected-orders.items.destroy', [$order, $item]) }}" onsubmit="return confirm('Remover este item?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-md border border-red-300 px-2 py-1 text-xs font-medium text-red-700 hover:bg-red-50 dark:border-red-700 dark:text-red-300 dark:hover:bg-red-900/20">Remover</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Nenhum item adicionado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
