@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-4xl space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="flex items-center gap-2.5 text-2xl font-semibold text-gray-800 dark:text-white/90">
                    <span class="inline-flex size-8 items-center justify-center text-brand-500 dark:text-brand-400">
                        {!! \App\Helpers\MenuHelper::getIconSvg('student') !!}
                    </span>
                    Detalhes do Aluno
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Visualize os dados completos do aluno.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('tenant.students.index') }}"
                   class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-transparent dark:text-gray-300 dark:hover:bg-white/5">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Voltar
                </a>
                <a href="{{ route('tenant.students.edit', $student) }}"
                   class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 text-sm font-medium text-white transition-colors hover:bg-brand-600">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M4 20h4l10.5-10.5a2.121 2.121 0 0 0-3-3L5 17v3Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M13.5 6.5l3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Editar
                </a>
                <form method="POST" action="{{ route('tenant.students.destroy', $student) }}" onsubmit="return confirm('Excluir este registro?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-error-300 bg-white px-4 text-sm font-medium text-error-600 transition-colors hover:bg-error-50 dark:border-error-500/40 dark:bg-transparent dark:text-error-400 dark:hover:bg-error-500/10">
                        Excluir
                    </button>
                </form>
            </div>
        </div>


        @if ($errors->any())
            <div class="rounded-xl border border-error-500 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/15 dark:text-error-400">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div><dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Nome</dt><dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $student->name }}</dd></div>
                <div><dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Escola</dt><dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $student->school?->name ?? '-' }}</dd></div>
                <div><dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Matrícula</dt><dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $student->enrollment_number ?? '-' }}</dd></div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</dt>
                    <dd class="mt-1">
                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium
                            {{ $student->status === 'active' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : '' }}
                            {{ $student->status === 'pending' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300' : '' }}
                            {{ $student->status === 'inactive' ? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' : '' }}
                            {{ $student->status === 'blocked' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' : '' }}">
                            {{ $statusOptions[$student->status] ?? $student->status }}
                        </span>
                    </dd>
                </div>
                <div><dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Nascimento</dt><dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $student->birth_date?->format('d/m/Y') ?? '-' }}</dd></div>
                <div><dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Série</dt><dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $student->grade ?? '-' }}</dd></div>
                <div><dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Turma</dt><dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $student->classroom ?? '-' }}</dd></div>
                <div><dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Turno</dt><dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $student->shift ?? '-' }}</dd></div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Foto</dt>
                    <dd class="mt-2">
                        @if ($student->photoSrc())
                            <div class="h-16 w-16 overflow-hidden rounded-full border border-gray-200 dark:border-gray-700" style="width:64px;height:64px;">
                                <img src="{{ $student->photoSrc() }}" alt="Foto de {{ $student->name }}" class="h-full w-full object-cover" style="width:64px;height:64px;object-fit:cover;">
                            </div>
                        @else
                            <span class="text-sm font-medium text-gray-800 dark:text-white/90">-</span>
                        @endif
                    </dd>
                </div>
                <div><dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">PIN</dt><dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $student->personal_pin_hash ? 'Cadastrado' : 'Não cadastrado' }}</dd></div>
                <div><dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Crédito</dt><dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $student->can_buy_on_credit ? 'Sim' : 'Não' }}</dd></div>
                <div><dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Fiado</dt><dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $student->can_buy_on_tab ? 'Sim' : 'Não' }}</dd></div>
                <div><dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Conveniência</dt><dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $student->convenience_access ? 'Sim' : 'Não' }}</dd></div>
                <div><dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Lanches</dt><dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $student->snack_access ? 'Sim' : 'Não' }}</dd></div>
            </dl>
        </div>
    </div>
@endsection
