@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Operadores</h1>
                <p class="mt-1 text-sm text-gray-500">Vínculo de usuários operadores às escolas.</p>
            </div>
            <a href="{{ route('admin.operators.create') }}" class="inline-flex rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Novo Operador</a>
        </div>
        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
        @endif
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <form method="GET" class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
                <input type="text" name="search" value="{{ $search }}" placeholder="Usuário ou escola..." class="h-11 rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90 sm:col-span-2">
                <select name="tenant_id" class="h-11 rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90">
                    <option value="">Todos os tenants</option>
                    @foreach($tenants as $tenant)
                        <option value="{{ $tenant->id }}" @selected($tenantId === $tenant->id)>{{ $tenant->name }}</option>
                    @endforeach
                </select>
            </form>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead>
                    <tr class="text-left text-xs uppercase text-gray-500">
                        <th class="px-4 py-3">Usuário</th>
                        <th class="px-4 py-3">Tenant</th>
                        <th class="px-4 py-3">Escola</th>
                        <th class="px-4 py-3">Papel</th>
                        <th class="px-4 py-3 text-right">Ações</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($operators as $operator)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium">{{ $operator->user?->name }} <span class="text-xs text-gray-400">{{ $operator->user?->email }}</span></td>
                            <td class="px-4 py-3 text-sm">{{ $operator->tenant?->name }}</td>
                            <td class="px-4 py-3 text-sm">{{ $operator->school?->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm">{{ $roles[$operator->role] ?? $operator->role }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.operators.show', $operator) }}" class="text-xs font-medium text-brand-600">Visualizar</a>
                                <a href="{{ route('admin.operators.edit', $operator) }}" class="text-xs font-medium text-gray-700 dark:text-gray-300">Editar</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">Nenhum operador encontrado.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $operators->links() }}</div>
        </div>
    </div>
@endsection
