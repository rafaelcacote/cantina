@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">Detalhes do Pedido #{{ $order->id }}</h1>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.orders.edit', $order) }}" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">
                    Editar
                </a>
                <a href="{{ route('admin.orders.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                    Voltar
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800/40 dark:bg-green-900/20 dark:text-green-300">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800/40 dark:bg-red-900/20 dark:text-red-300">
                Verifique os campos do formulário de itens.
            </div>
        @endif

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="xl:col-span-2 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Dados do Pedido</h2>
                <dl class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div><dt class="text-xs uppercase text-gray-500">Tenant</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $tenantName ?? '-' }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Escola</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $order->school?->name ?? '-' }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Aluno</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $order->student?->name ?? '-' }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Responsável</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $order->parent?->name ?? '-' }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Canal</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $order->order_channel }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Tipo</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $order->order_type }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Status</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $statuses[$order->status] ?? $order->status }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Pagamento</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $order->payment_mode ? ($paymentModes[$order->payment_mode] ?? $order->payment_mode) : '-' }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Total</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">R$ {{ number_format((float) $order->total_amount, 2, ',', '.') }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Desconto</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">R$ {{ number_format((float) $order->discount_amount, 2, ',', '.') }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Final</dt><dd class="mt-1 text-sm font-semibold text-gray-800 dark:text-gray-200">R$ {{ number_format((float) $order->final_amount, 2, ',', '.') }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Agendado para</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $order->scheduled_for?->format('d/m/Y H:i') ?: '-' }}</dd></div>
                    <div class="md:col-span-2"><dt class="text-xs uppercase text-gray-500">Observações</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $order->notes ?: '-' }}</dd></div>
                </dl>
            </div>

            <div class="space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Atualizar Status</h2>
                    <form method="POST" action="{{ route('admin.orders.status.update', $order) }}" class="mt-4 space-y-3">
                        @csrf
                        @method('PATCH')
                        <select name="status" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            @foreach ($statuses as $key => $label)
                                <option value="{{ $key }}" @selected(old('status', $order->status) === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status')
                            <p class="text-sm text-error-500">{{ $message }}</p>
                        @enderror
                        @if ($order->payment_mode === 'tab')
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">PIN do aluno</label>
                                <input type="password" name="student_pin" maxlength="20" autocomplete="one-time-code"
                                       class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                @error('student_pin')
                                    <p class="mt-1 text-sm text-error-500">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif
                        <button type="submit" class="w-full rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">
                            Alterar status
                        </button>
                    </form>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Adicionar Item</h2>
                    <form method="POST" action="{{ route('admin.orders.items.store', $order) }}" class="mt-4 space-y-3">
                        @csrf
                        <select name="product_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            <option value="">Selecione o produto</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="number" min="1" name="quantity" placeholder="Quantidade" value="1" class="w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            <input type="number" min="0" step="0.01" name="unit_price" placeholder="Preço unitário" class="w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        </div>
                        <textarea name="observation" rows="2" placeholder="Observação" class="w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white"></textarea>
                        <textarea name="custom_request_text" rows="2" placeholder="Pedido customizado" class="w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white"></textarea>
                        <button type="submit" class="w-full rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">
                            Adicionar item
                        </button>
                    </form>
                </div>
            </div>
        </div>

        @include('pages.admin.orders.partials.items')
    </div>
@endsection
