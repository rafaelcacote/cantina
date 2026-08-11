@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Detalhes da Assinatura</h1>
            <div class="flex gap-2">
                <a href="{{ route('admin.subscriptions.edit', $subscription) }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm dark:border-gray-700">Editar</a>
                <a href="{{ route('admin.subscriptions.index') }}" class="text-sm font-medium text-brand-600">Voltar</a>
            </div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div><dt class="text-xs uppercase text-gray-500">Tenant</dt><dd class="mt-1 text-sm font-medium">{{ $subscription->tenant?->name }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Plano</dt><dd class="mt-1 text-sm font-medium">{{ $subscription->plan?->name }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Status</dt><dd class="mt-1 text-sm font-medium">{{ $statuses[$subscription->status] ?? $subscription->status }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Início</dt><dd class="mt-1 text-sm font-medium">{{ $subscription->starts_at?->format('d/m/Y H:i') ?? '-' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Fim</dt><dd class="mt-1 text-sm font-medium">{{ $subscription->ends_at?->format('d/m/Y H:i') ?? '-' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Trial até</dt><dd class="mt-1 text-sm font-medium">{{ $subscription->trial_ends_at?->format('d/m/Y H:i') ?? '-' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Próxima cobrança</dt><dd class="mt-1 text-sm font-medium">{{ $subscription->next_billing_at?->format('d/m/Y H:i') ?? '-' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Cancelada em</dt><dd class="mt-1 text-sm font-medium">{{ $subscription->cancelled_at?->format('d/m/Y H:i') ?? '-' }}</dd></div>
            </dl>
        </div>
    </div>
@endsection
