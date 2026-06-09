@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-4xl space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Editar Aluno</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Atualize os dados do aluno.</p>
            </div>
            <a href="{{ route('tenant.students.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">
                Voltar
            </a>
        </div>

        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-300">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('tenant.students.update', $student) }}" class="space-y-5 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            @csrf
            @method('PUT')

            @include('pages.tenant.students.partials.form', [
                'student' => $student,
                'schools' => $schools,
                'statusOptions' => $statusOptions,
            ])

            <div class="flex justify-end">
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                    Atualizar Aluno
                </button>
            </div>
        </form>
    </div>
@endsection
