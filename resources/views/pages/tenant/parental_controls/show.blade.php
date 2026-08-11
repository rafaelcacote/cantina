@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">Detalhes do Controle Parental</h1>
            <div class="flex gap-2">
                <a href="{{ route('tenant.parental-controls.edit', $control) }}" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Editar</a>
                <a href="{{ route('tenant.parental-controls.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Voltar</a>
            </div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <dl class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div><dt class="text-xs uppercase text-gray-500">Aluno</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $control->student?->name ?? '-' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Status</dt><dd class="mt-1"><span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $control->enabled ? 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-300' }}">{{ $control->enabled ? 'Ativo' : 'Inativo' }}</span></dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Modo</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $controlModes[$control->control_mode] ?? $control->control_mode }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Limite diário</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $control->daily_spending_limit !== null ? 'R$ ' . number_format((float) $control->daily_spending_limit, 2, ',', '.') : '-' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Limite semanal</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $control->weekly_spending_limit !== null ? 'R$ ' . number_format((float) $control->weekly_spending_limit, 2, ',', '.') : '-' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Permitir fiado</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $control->allow_tab_usage ? 'Sim' : 'Não' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Permitir carteira</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $control->allow_wallet_usage ? 'Sim' : 'Não' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Permitir conveniência</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $control->allow_convenience_access ? 'Sim' : 'Não' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Permitir lanche</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $control->allow_snack_access ? 'Sim' : 'Não' }}</dd></div>
                <div class="md:col-span-2"><dt class="text-xs uppercase text-gray-500">Observações</dt><dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $control->notes ?: '-' }}</dd></div>
            </dl>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Categorias Permitidas</h2>
                <ul class="mt-4 space-y-2 text-sm text-gray-700 dark:text-gray-300">
                    @forelse ($control->allowedCategories as $allowed)
                        <li>{{ $allowed->category?->name ?? '-' }}</li>
                    @empty
                        <li class="text-gray-500 dark:text-gray-400">Sem categorias vinculadas.</li>
                    @endforelse
                </ul>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Produtos Bloqueados</h2>
                <ul class="mt-4 space-y-2 text-sm text-gray-700 dark:text-gray-300">
                    @forelse ($control->blockedProducts as $blocked)
                        <li>{{ $blocked->product?->name ?? '-' }}</li>
                    @empty
                        <li class="text-gray-500 dark:text-gray-400">Sem produtos bloqueados.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection
