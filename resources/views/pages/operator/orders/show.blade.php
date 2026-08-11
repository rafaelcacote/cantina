@extends('layouts.app')

@section('content')
    @php $input = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90'; @endphp
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Pedido #{{ $order->id }}</h1>
                <p class="mt-1 text-sm text-gray-500">{{ $order->student?->name }} — {{ $order->school?->name }}</p>
            </div>
            <a href="{{ route('operator.orders.index') }}" class="text-sm font-medium text-brand-600">Voltar</a>
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
        @endif

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="space-y-6 xl:col-span-2">
                <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div><dt class="text-xs uppercase text-gray-500">Status</dt><dd class="mt-1 font-medium">{{ $statuses[$order->status] ?? $order->status }}</dd></div>
                        <div><dt class="text-xs uppercase text-gray-500">Pagamento</dt><dd class="mt-1 font-medium">{{ $paymentModes[$order->payment_mode] ?? $order->payment_mode }}</dd></div>
                        <div><dt class="text-xs uppercase text-gray-500">Total</dt><dd class="mt-1 font-semibold">R$ {{ number_format((float) $order->final_amount, 2, ',', '.') }}</dd></div>
                    </dl>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                    <h2 class="text-base font-semibold">Itens</h2>
                    <ul class="mt-4 divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($order->items as $item)
                            <li class="flex justify-between py-3 text-sm">
                                <span>{{ $item->quantity }}× {{ $item->item_name_snapshot }}</span>
                                <span>R$ {{ number_format((float) $item->total_price, 2, ',', '.') }}</span>
                            </li>
                        @empty
                            <li class="py-4 text-sm text-gray-500">Nenhum item.</li>
                        @endforelse
                    </ul>

                    @if ($order->status === 'pending')
                        <form method="POST" action="{{ route('operator.orders.items.store', $order) }}" class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
                            @csrf
                            <select name="product_id" required class="{{ $input }} sm:col-span-2">
                                <option value="">Produto</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }} — R$ {{ number_format((float) $product->price, 2, ',', '.') }}</option>
                                @endforeach
                            </select>
                            <input type="number" name="quantity" value="1" min="1" class="{{ $input }}">
                            <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:col-span-3">Adicionar item</button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h2 class="text-base font-semibold">Atualizar status</h2>
                <form method="POST" action="{{ route('operator.orders.status.update', $order) }}" class="mt-4 space-y-4">
                    @csrf
                    @method('PATCH')
                    <select name="status" class="{{ $input }}">
                        @foreach($statuses as $key => $label)
                            <option value="{{ $key }}" @selected(old('status', $order->status) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @if ($order->payment_mode === 'tab' && ($pinAlreadyProvided ?? false))
                        <div class="flex items-start gap-2 rounded-xl border border-brand-200 bg-brand-50 px-3 py-2.5 text-sm text-brand-800 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-200">
                            <p>Pedido solicitado mediante inserção do PIN.</p>
                        </div>
                    @elseif ($order->payment_mode === 'tab')
                        <div>
                            <label class="mb-1.5 block text-sm font-medium">PIN do aluno</label>
                            <input type="password" name="student_pin" maxlength="20" class="{{ $input }}">
                        </div>
                    @endif
                    <button type="submit" class="w-full rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Alterar</button>
                </form>
            </div>
        </div>
    </div>
@endsection
