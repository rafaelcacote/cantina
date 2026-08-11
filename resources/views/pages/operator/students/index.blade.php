@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Alunos</h1>
            <p class="mt-1 text-sm text-gray-500">Consulta{{ $schoolScoped ? ' (escola vinculada)' : '' }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <form method="GET" class="mb-4">
                <input type="text" name="search" value="{{ $search }}" placeholder="Nome ou matrícula..." class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90">
            </form>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead>
                    <tr class="text-left text-xs uppercase text-gray-500">
                        <th class="px-4 py-3">Nome</th>
                        <th class="px-4 py-3">Escola</th>
                        <th class="px-4 py-3">Carteira</th>
                        <th class="px-4 py-3 text-right">Ações</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($students as $student)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium">{{ $student->name }}</td>
                            <td class="px-4 py-3 text-sm">{{ $student->school?->name }}</td>
                            <td class="px-4 py-3 text-sm">{{ $student->wallet ? 'R$ '.number_format((float) $student->wallet->balance, 2, ',', '.') : '-' }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('operator.students.show', $student) }}" class="text-xs font-medium text-brand-600">Ver</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">Nenhum aluno.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $students->links() }}</div>
        </div>
    </div>
@endsection
