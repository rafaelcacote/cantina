@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Detalhes do Plano</h1>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.plans.edit', $plan) }}" class="inline-flex rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium dark:border-gray-700 dark:text-gray-300">Editar</a>
                <a href="{{ route('admin.plans.index') }}" class="text-sm font-medium text-brand-600">Voltar</a>
            </div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div><dt class="text-xs uppercase text-gray-500">Nome</dt><dd class="mt-1 text-sm font-medium">{{ $plan->name }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Slug</dt><dd class="mt-1 text-sm font-medium">{{ $plan->slug }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Preço</dt><dd class="mt-1 text-sm font-medium">R$ {{ number_format((float) $plan->price, 2, ',', '.') }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Ciclo</dt><dd class="mt-1 text-sm font-medium">{{ $billingCycles[$plan->billing_cycle] ?? $plan->billing_cycle }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Máx. alunos</dt><dd class="mt-1 text-sm font-medium">{{ $plan->max_students ?? '-' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Máx. usuários</dt><dd class="mt-1 text-sm font-medium">{{ $plan->max_users ?? '-' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Ativo</dt><dd class="mt-1 text-sm font-medium">{{ $plan->active ? 'Sim' : 'Não' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Assinaturas</dt><dd class="mt-1 text-sm font-medium">{{ $plan->subscriptions_count }}</dd></div>
                <div class="sm:col-span-2">
                    <dt class="text-xs uppercase text-gray-500">Recursos</dt>
                    <dd class="mt-1 text-sm font-medium">
                        @if(is_array($plan->features) && count($plan->features))
                            <ul class="list-disc pl-5">@foreach($plan->features as $feature)<li>{{ $feature }}</li>@endforeach</ul>
                        @else
                            -
                        @endif
                    </dd>
                </div>
            </dl>
        </div>
    </div>
@endsection
