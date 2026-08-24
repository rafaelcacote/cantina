@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="flex items-center gap-2.5 text-2xl font-semibold text-gray-800 dark:text-white/90">
                    <span class="inline-flex size-8 items-center justify-center text-brand-500 dark:text-brand-400">
                        {!! \App\Helpers\MenuHelper::getIconSvg('tables') !!}
                    </span>
                    Pedidos
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Acompanhe e avance os pedidos da cantina.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('operator.pos.index') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                    PDV
                </a>
                <a href="{{ route('operator.orders.create') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-transparent dark:text-gray-300 dark:hover:bg-white/5">
                    Pedido manual
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-500/30 dark:bg-green-500/15 dark:text-green-300">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-error-500 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/15 dark:text-error-400">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <form method="GET" action="{{ route('operator.orders.index') }}" class="mb-4 flex w-full flex-row flex-wrap gap-3">
                <input type="text"
                       name="search"
                       value="{{ $search }}"
                       placeholder="Buscar por aluno, responsável ou id..."
                       class="h-11 min-w-0 flex-[2] basis-0 rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">

                <select name="status"
                        class="h-11 min-w-0 flex-1 basis-0 rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
                    <option value="">Todos os status</option>
                    @foreach ($statuses as $key => $label)
                        <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
                    @endforeach
                </select>

                <select name="payment_mode"
                        class="h-11 min-w-0 flex-1 basis-0 rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
                    <option value="">Todos os pagamentos</option>
                    @foreach ($paymentModes as $key => $label)
                        <option value="{{ $key }}" @selected($paymentMode === $key)>{{ $label }}</option>
                    @endforeach
                </select>

                <button type="submit"
                        class="inline-flex h-11 shrink-0 items-center justify-center rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
                    Filtrar
                </button>
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead>
                    <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        <th class="px-4 py-3">Pedido</th>
                        <th class="px-4 py-3">Escola</th>
                        <th class="px-4 py-3">Aluno</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Pagamento</th>
                        <th class="px-4 py-3">Total</th>
                        <th class="px-4 py-3">Data</th>
                        <th class="px-4 py-3 text-right">Ações</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($orders as $order)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-800 dark:text-white/90">#{{ $order->id }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $order->school?->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $order->student?->name ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium
                                    {{ $order->status === 'delivered' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : '' }}
                                    {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' : '' }}
                                    {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300' : '' }}
                                    {{ in_array($order->status, ['confirmed', 'preparing', 'ready'], true) ? 'bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-300' : '' }}">
                                    {{ $statuses[$order->status] ?? $order->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if ($order->payment_mode)
                                    <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                        {{ $paymentModes[$order->payment_mode] ?? $order->payment_mode }}
                                    </span>
                                @else
                                    <span class="text-sm text-gray-500 dark:text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">R$ {{ number_format((float) $order->final_amount, 2, ',', '.') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $order->created_at?->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('operator.orders.show', $order) }}"
                                   title="Visualizar"
                                   class="inline-flex size-10 items-center justify-center rounded-lg text-brand-500 transition-colors hover:bg-brand-50 hover:text-brand-700 dark:text-brand-400 dark:hover:bg-white/5 dark:hover:text-brand-300">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12s-3.75 6.75-9.75 6.75S2.25 12 2.25 12Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <circle cx="12" cy="12" r="2.75" stroke="currentColor" stroke-width="1.5"/>
                                    </svg>
                                    <span class="sr-only">Visualizar</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                Nenhum pedido encontrado.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
@endsection
