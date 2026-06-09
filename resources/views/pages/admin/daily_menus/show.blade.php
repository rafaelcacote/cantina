@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">Detalhes do Cardápio</h1>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.daily-menus.edit', $dailyMenu) }}" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">
                    Editar
                </a>
                <a href="{{ route('admin.daily-menus.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
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
                <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Dados do Cardápio</h2>
                <dl class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div><dt class="text-xs uppercase text-gray-500">Tenant</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $tenantName ?? '-' }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Escola</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $dailyMenu->school?->name ?? '-' }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Data</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $dailyMenu->menu_date?->format('d/m/Y') }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Título</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $dailyMenu->title ?: '-' }}</dd></div>
                    <div class="md:col-span-2"><dt class="text-xs uppercase text-gray-500">Descrição</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $dailyMenu->description ?: '-' }}</dd></div>
                    <div>
                        <dt class="text-xs uppercase text-gray-500">Status</dt>
                        <dd class="mt-1">
                            <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $dailyMenu->active ? 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-300' }}">
                                {{ $dailyMenu->active ? 'Ativo' : 'Inativo' }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Adicionar Produto</h2>
                <form method="POST" action="{{ route('admin.daily-menus.items.store', $dailyMenu) }}" class="mt-5 space-y-4">
                    @csrf
                    <div>
                        <label for="product_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Produto *</label>
                        <select id="product_id" name="product_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            <option value="">Selecione</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('product_id') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="planned_quantity" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Qtd planejada</label>
                            <input id="planned_quantity" name="planned_quantity" type="number" min="0" value="{{ old('planned_quantity') }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label for="available_quantity" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Qtd disponível</label>
                            <input id="available_quantity" name="available_quantity" type="number" min="0" value="{{ old('available_quantity') }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="price_override" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Preço sobrescrito</label>
                            <input id="price_override" name="price_override" type="number" step="0.01" min="0" value="{{ old('price_override') }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label for="sort_order" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Ordem</label>
                            <input id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', 0) }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        </div>
                    </div>

                    <label class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-700 dark:border-gray-700 dark:text-gray-300">
                        <input type="checkbox" name="active" value="1" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" @checked(old('active', true))>
                        Ativo
                    </label>

                    <button type="submit" class="w-full rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">
                        Adicionar item
                    </button>
                </form>
            </div>
        </div>

        @include('pages.admin.daily_menus.partials.items')
    </div>
@endsection
