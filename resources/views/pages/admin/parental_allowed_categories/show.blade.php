@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">Detalhes da Categoria Permitida</h1>
            <div class="flex gap-2">
                <a href="{{ route('admin.parental-allowed-categories.edit', $item) }}" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Editar</a>
                <a href="{{ route('admin.parental-allowed-categories.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Voltar</a>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <dl class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div><dt class="text-xs uppercase text-gray-500">Tenant</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $tenantName ?? '-' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Aluno</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $item->parentalControl?->student?->name ?? '-' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Controle Parental</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">#{{ $item->parental_control_id }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Categoria</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $item->category?->name ?? '-' }}</dd></div>
            </dl>
        </div>
    </div>
@endsection
