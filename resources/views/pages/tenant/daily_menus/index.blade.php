@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Cardápios</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Gerencie os cardápios do dia do seu tenant.</p>
            </div>
            <a href="{{ route('tenant.daily-menus.create') }}"
               class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                Novo Cardápio
            </a>
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-900/40 dark:bg-green-900/20 dark:text-green-300">
                {{ session('success') }}
            </div>
        @endif

        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <form method="GET" action="{{ route('tenant.daily-menus.index') }}" class="grid grid-cols-1 gap-3 lg:grid-cols-4">
                <input type="text"
                       name="search"
                       value="{{ $search }}"
                       placeholder="Buscar por título..."
                       class="h-11 rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">

                <select name="school_id"
                        class="h-11 rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
                    <option value="">Todas as escolas</option>
                    @foreach ($schools as $school)
                        <option value="{{ $school->id }}" @selected($schoolId === (int) $school->id)>{{ $school->name }}</option>
                    @endforeach
                </select>

                <input type="date" name="menu_date" value="{{ $menuDate }}"
                       class="h-11 rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">

                <div class="flex gap-2">
                    <button type="submit"
                            class="inline-flex h-11 flex-1 items-center justify-center rounded-lg bg-brand-500 px-4 text-sm font-medium text-white hover:bg-brand-600">
                        Filtrar
                    </button>
                    <a href="{{ route('tenant.daily-menus.index') }}"
                       class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
                        Limpar
                    </a>
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            <th class="px-4 py-3">Escola</th>
                            <th class="px-4 py-3">Data</th>
                            <th class="px-4 py-3">Título</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($dailyMenus as $dailyMenu)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $dailyMenu->school?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $dailyMenu->menu_date?->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-800 dark:text-white/90">{{ $dailyMenu->title ?: '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $dailyMenu->active ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' }}">
                                        {{ $dailyMenu->active ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('tenant.daily-menus.show', $dailyMenu) }}" class="rounded-md px-3 py-1.5 text-xs font-medium text-brand-600 hover:bg-brand-50 dark:text-brand-400 dark:hover:bg-white/5">Visualizar</a>
                                        <a href="{{ route('tenant.daily-menus.edit', $dailyMenu) }}" class="rounded-md px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5">Editar</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Nenhum cardápio encontrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-100 px-4 py-3 dark:border-gray-800">
                {{ $dailyMenus->links() }}
            </div>
        </div>
    </div>
@endsection
