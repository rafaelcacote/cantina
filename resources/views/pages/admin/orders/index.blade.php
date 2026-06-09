@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">Pedidos</h1>
            <a href="{{ route('admin.orders.create') }}" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">
                Novo Pedido
            </a>
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800/40 dark:bg-green-900/20 dark:text-green-300">
                {{ session('success') }}
            </div>
        @endif

        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <form method="GET" action="{{ route('admin.orders.index') }}" class="grid grid-cols-1 gap-3 lg:grid-cols-6">
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Buscar por aluno, responsável ou id"
                    class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white lg:col-span-2"
                >

                <select id="tenant_filter" name="tenant_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="">Todos os tenants</option>
                    @foreach ($tenants as $tenant)
                        <option value="{{ $tenant->id }}" @selected($tenantId === (int) $tenant->id)>{{ $tenant->name }}</option>
                    @endforeach
                </select>

                <select id="school_filter" name="school_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="">Todas as escolas</option>
                    @foreach ($schools as $school)
                        <option value="{{ $school->id }}" data-tenant-id="{{ $school->tenant_id }}" @selected($schoolId === (int) $school->id)>{{ $school->name }}</option>
                    @endforeach
                </select>

                <select name="status" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="">Todos os status</option>
                    @foreach ($statuses as $key => $label)
                        <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
                    @endforeach
                </select>

                <select name="payment_mode" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="">Todos os pagamentos</option>
                    @foreach ($paymentModes as $key => $label)
                        <option value="{{ $key }}" @selected($paymentMode === $key)>{{ $label }}</option>
                    @endforeach
                </select>

                <div class="lg:col-span-6 flex gap-2">
                    <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Filtrar</button>
                    <a href="{{ route('admin.orders.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                        Limpar
                    </a>
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Pedido</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Tenant</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Escola</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Aluno</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Pagamento</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Total</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Data</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($orders as $order)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">#{{ $order->id }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $tenantNames[$order->tenant_id] ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $order->school?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $order->student?->name ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300">
                                        {{ $statuses[$order->status] ?? $order->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    @if ($order->payment_mode)
                                        <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium bg-purple-100 text-purple-700 dark:bg-purple-900/20 dark:text-purple-300">
                                            {{ $paymentModes[$order->payment_mode] ?? $order->payment_mode }}
                                        </span>
                                    @else
                                        <span class="text-sm text-gray-500 dark:text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">R$ {{ number_format((float) $order->final_amount, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $order->created_at?->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <div class="flex gap-2">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Visualizar</a>
                                        <a href="{{ route('admin.orders.edit', $order) }}" class="rounded-md bg-brand-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-brand-600">Editar</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Nenhum pedido encontrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-100 px-4 py-3 dark:border-gray-800">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const tenant = document.getElementById('tenant_filter');
            const school = document.getElementById('school_filter');
            if (!tenant || !school) return;

            const syncSchools = () => {
                const tenantId = tenant.value;
                [...school.options].forEach((opt, idx) => {
                    if (idx === 0) return;
                    const match = !tenantId || opt.dataset.tenantId === tenantId;
                    opt.hidden = !match;
                    opt.disabled = !match;
                });
                if (school.selectedOptions[0]?.disabled) school.value = '';
            };

            tenant.addEventListener('change', syncSchools);
            syncSchools();
        })();
    </script>
@endpush
