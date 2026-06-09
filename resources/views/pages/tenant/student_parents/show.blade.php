@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Detalhes do Vínculo</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Visualize os dados da associação aluno-responsável.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('tenant.student-parents.edit', $studentParent) }}"
                   class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
                    Editar
                </a>
                <a href="{{ route('tenant.student-parents.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">
                    Voltar
                </a>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div><dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Aluno</dt><dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $studentParent->student?->name ?? '-' }}</dd></div>
                <div><dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Responsável</dt><dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $studentParent->parent?->name ?? '-' }}</dd></div>
                <div><dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Relação</dt><dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $studentParent->relationship_type ?? '-' }}</dd></div>
                <div><dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Principal</dt><dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $studentParent->is_primary ? 'Sim' : 'Não' }}</dd></div>
                <div><dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Responsável financeiro</dt><dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $studentParent->financial_responsible ? 'Sim' : 'Não' }}</dd></div>
            </dl>
        </div>
    </div>
@endsection
