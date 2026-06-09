@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Detalhes do Lançamento</h1>
            <div class="flex gap-2">
                <a href="{{ route('tenant.tab-entries.edit', $entry) }}" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Editar Status</a>
                <a href="{{ route('tenant.tab-entries.index') }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Voltar</a>
            </div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <dl class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div><dt class="text-xs uppercase text-gray-500">Aluno</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $entry->student?->name ?? '-' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Conta de fiado</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">#{{ $entry->student_tab_id }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Pedido</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $entry->order_id ? '#'.$entry->order_id : '-' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Valor</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">R$ {{ number_format((float) $entry->amount, 2, ',', '.') }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Data</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $entry->entry_date?->format('d/m/Y') }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Status</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $statuses[$entry->status] ?? $entry->status }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Autorizado por PIN</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $entry->authorized_by_pin ? 'Sim' : 'Não' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Método autorização</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $entry->authorization_method ? ($authorizationMethods[$entry->authorization_method] ?? $entry->authorization_method) : '-' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Autorizado em</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $entry->authorized_at?->format('d/m/Y H:i') ?: '-' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Criado por</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $entry->creator?->name ?? '-' }}</dd></div>
                <div class="md:col-span-2"><dt class="text-xs uppercase text-gray-500">Descrição</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $entry->description ?: '-' }}</dd></div>
            </dl>
        </div>
    </div>
@endsection
