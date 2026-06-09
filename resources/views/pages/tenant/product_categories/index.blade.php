@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Categorias de Produtos</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Categorias dentro de cada seção.</p>
            </div>
            <a href="{{ route('tenant.product-categories.create') }}"
               class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                Nova Categoria
            </a>
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-900/40 dark:bg-green-900/20 dark:text-green-300">{{ session('success') }}</div>
        @endif

        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <form method="GET" action="{{ route('tenant.product-categories.index') }}" class="grid grid-cols-1 gap-3 lg:grid-cols-4">
                <input type="text" name="search" value="{{ $search }}" placeholder="Buscar por nome ou slug"
                       class="h-11 rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90 lg:col-span-2">
                <select name="section_id" class="h-11 rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90">
                    <option value="">Todas as seções</option>
                    @foreach ($sections as $section)
                        <option value="{{ $section->id }}" @selected($sectionId === (int) $section->id)>{{ $section->name }}</option>
                    @endforeach
                </select>
                <div class="flex gap-2">
                    <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Filtrar</button>
                    <a href="{{ route('tenant.product-categories.index') }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Limpar</a>
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Nome</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Seção</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Slug</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($categories as $category)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $category->name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $category->section?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $category->slug ?: '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $category->active ? 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-300' }}">
                                        {{ $category->active ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <div class="flex gap-2">
                                        <a href="{{ route('tenant.product-categories.show', $category) }}" class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Visualizar</a>
                                        <a href="{{ route('tenant.product-categories.edit', $category) }}" class="rounded-md bg-brand-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-brand-600">Editar</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Nenhuma categoria encontrada.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-100 px-4 py-3 dark:border-gray-800">{{ $categories->links() }}</div>
        </div>
    </div>
@endsection
