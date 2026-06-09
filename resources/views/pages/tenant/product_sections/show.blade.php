@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Detalhes da Seção</h1>
            <div class="flex gap-2">
                <a href="{{ route('tenant.product-sections.edit', $section) }}" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Editar</a>
                <a href="{{ route('tenant.product-sections.index') }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Voltar</a>
            </div>
        </div>
        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-900/40 dark:bg-green-900/20 dark:text-green-300">{{ session('success') }}</div>
        @endif
        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <dl class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div><dt class="text-xs uppercase text-gray-500">Nome</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $section->name }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Slug</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $section->slug ?: '-' }}</dd></div>
                <div>
                    <dt class="text-xs uppercase text-gray-500">Status</dt>
                    <dd class="mt-1">
                        <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $section->active ? 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-300' }}">
                            {{ $section->active ? 'Ativo' : 'Inativo' }}
                        </span>
                    </dd>
                </div>
                <div class="md:col-span-2"><dt class="text-xs uppercase text-gray-500">Descrição</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $section->description ?: '-' }}</dd></div>
            </dl>
        </div>
    </div>
@endsection
