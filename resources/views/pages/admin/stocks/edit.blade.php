@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">Editar Estoque</h1>
            <a href="{{ route('admin.stocks.show', $stock) }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                Voltar
            </a>
        </div>

        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800/40 dark:bg-red-900/20 dark:text-red-300">
                Verifique os campos do formulário.
            </div>
        @endif

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <form method="POST" action="{{ route('admin.stocks.update', $stock) }}" class="xl:col-span-2 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                @csrf
                @method('PUT')

                <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Atualização de Quantidades</h2>
                <div class="mt-5 grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Produto</label>
                        <p class="rounded-lg border border-gray-200 px-4 py-3 text-sm text-gray-700 dark:border-gray-800 dark:text-gray-300">{{ $stock->product?->name ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tenant</label>
                        <p class="rounded-lg border border-gray-200 px-4 py-3 text-sm text-gray-700 dark:border-gray-800 dark:text-gray-300">{{ $tenantName ?? '-' }}</p>
                    </div>

                    <div>
                        <label for="quantity" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Quantidade atual *</label>
                        <input id="quantity" name="quantity" type="number" min="0" value="{{ old('quantity', $stock->quantity) }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        @error('quantity') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="reserved_quantity" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Quantidade reservada *</label>
                        <input id="reserved_quantity" name="reserved_quantity" type="number" min="0" value="{{ old('reserved_quantity', $stock->reserved_quantity) }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        @error('reserved_quantity') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-8 flex items-center gap-3">
                    <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Salvar alterações</button>
                    <a href="{{ route('admin.stocks.show', $stock) }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Cancelar</a>
                </div>
            </form>

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
                    </div>
                    <div>
                        <label for="adjust_quantity" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Quantidade *</label>
                        <input id="adjust_quantity" name="quantity" type="number" min="0" value="{{ old('quantity') }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label for="adjust_description" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Descrição</label>
                        <textarea id="adjust_description" name="description" rows="3" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">{{ old('description') }}</textarea>
                    </div>
                    <button type="submit" class="w-full rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">
                        Aplicar ajuste
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
