@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-4xl space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Detalhes do Aluno</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Visualize os dados completos do aluno.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.students.edit', $student) }}"
                   class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
                    Editar
                </a>
                <a href="{{ route('admin.students.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">
                    Voltar
                </a>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div><dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Nome</dt><dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $student->name }}</dd></div>
                <div><dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Tenant</dt><dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $student->tenant?->name ?? '-' }}</dd></div>
                <div><dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Escola</dt><dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $student->school?->name ?? '-' }}</dd></div>
                <div><dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Matrícula</dt><dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $student->enrollment_number ?? '-' }}</dd></div>
                <div><dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Nascimento</dt><dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $student->birth_date?->format('d/m/Y') ?? '-' }}</dd></div>
                <div><dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</dt><dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $student->status }}</dd></div>
                <div><dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Série</dt><dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $student->grade ?? '-' }}</dd></div>
                <div><dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Turma</dt><dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $student->classroom ?? '-' }}</dd></div>
                <div><dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Turno</dt><dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $student->shift ?? '-' }}</dd></div>
                <div><dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">PIN</dt><dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $student->personal_pin_hash ?? '-' }}</dd></div>
                <div class="sm:col-span-2"><dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Foto URL</dt><dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90 break-all">{{ $student->photo_url ?? '-' }}</dd></div>
                <div><dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Crédito</dt><dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $student->can_buy_on_credit ? 'Sim' : 'Não' }}</dd></div>
                <div><dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Fiado</dt><dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $student->can_buy_on_tab ? 'Sim' : 'Não' }}</dd></div>
                <div><dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Conveniência</dt><dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $student->convenience_access ? 'Sim' : 'Não' }}</dd></div>
                <div><dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Lanches</dt><dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $student->snack_access ? 'Sim' : 'Não' }}</dd></div>
            </dl>
        </div>
    </div>
@endsection
