@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Detalhes do Operador</h1>
            <div class="flex gap-2">
                <a href="{{ route('admin.operators.edit', $operator) }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm dark:border-gray-700">Editar</a>
                <a href="{{ route('admin.operators.index') }}" class="text-sm font-medium text-brand-600">Voltar</a>
            </div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div><dt class="text-xs uppercase text-gray-500">Usuário</dt><dd class="mt-1 text-sm font-medium">{{ $operator->user?->name }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Email</dt><dd class="mt-1 text-sm font-medium">{{ $operator->user?->email }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Tenant</dt><dd class="mt-1 text-sm font-medium">{{ $operator->tenant?->name }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Escola</dt><dd class="mt-1 text-sm font-medium">{{ $operator->school?->name ?? '-' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Papel</dt><dd class="mt-1 text-sm font-medium">{{ $roles[$operator->role] ?? $operator->role }}</dd></div>
            </dl>
        </div>
    </div>
@endsection
