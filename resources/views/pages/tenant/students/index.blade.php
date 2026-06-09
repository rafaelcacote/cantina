@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Alunos</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Gerencie os alunos do seu tenant.</p>
            </div>
            <a href="{{ route('tenant.students.create') }}"
               class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                Novo Aluno
            </a>
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-900/40 dark:bg-green-900/20 dark:text-green-300">
                {{ session('success') }}
            </div>
        @endif

        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <form method="GET" action="{{ route('tenant.students.index') }}" class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-4">
                <input type="text"
                       name="search"
                       value="{{ $search }}"
                       placeholder="Buscar por nome ou matrícula..."
                       class="h-11 rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90 md:col-span-2">

                <select name="school_id"
                        class="h-11 rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
                    <option value="">Todas as escolas</option>
                    @foreach($schools as $school)
                        <option value="{{ $school->id }}" @selected((int) $schoolId === (int) $school->id)>{{ $school->name }}</option>
                    @endforeach
                </select>

                <select name="status"
                        class="h-11 rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
                    <option value="">Todos os status</option>
                    @foreach($statusOptions as $statusOption)
                        <option value="{{ $statusOption }}" @selected($status === $statusOption)>{{ $statusOption }}</option>
                    @endforeach
                </select>

                <div class="md:col-span-4 flex justify-end">
                    <button type="submit"
                            class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
                        Filtrar
                    </button>
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead>
                    <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        <th class="px-4 py-3">Nome</th>
                        <th class="px-4 py-3">Escola</th>
                        <th class="px-4 py-3">Matrícula</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Ações</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($students as $student)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-800 dark:text-white/90">{{ $student->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $student->school?->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $student->enrollment_number ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium
                                    {{ $student->status === 'active' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : '' }}
                                    {{ $student->status === 'pending' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300' : '' }}
                                    {{ $student->status === 'inactive' ? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' : '' }}
                                    {{ $student->status === 'blocked' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' : '' }}">
                                    {{ $student->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('tenant.students.show', $student) }}" class="rounded-md px-3 py-1.5 text-xs font-medium text-brand-600 hover:bg-brand-50 dark:text-brand-400 dark:hover:bg-white/5">Visualizar</a>
                                    <a href="{{ route('tenant.students.edit', $student) }}" class="rounded-md px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5">Editar</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                Nenhum aluno encontrado.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $students->links() }}
            </div>
        </div>
    </div>
@endsection
