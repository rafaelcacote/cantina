@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Detalhes da Escola</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Visualize os dados da unidade.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.schools.edit', $school) }}"
                   class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
                    Editar
                </a>
                <a href="{{ route('admin.schools.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">
                    Voltar
                </a>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Tenant</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $school->tenant?->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Nome</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $school->name }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Documento</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $school->document ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Email</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $school->email ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Telefone</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $school->phone ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</dt>
                    <dd class="mt-1">
                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $school->active ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                            {{ $school->active ? 'ativo' : 'inativo' }}
                        </span>
                    </dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Endereço</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $school->address ?? '-' }}</dd>
                </div>
            </dl>
        </div>
    </div>
@endsection
