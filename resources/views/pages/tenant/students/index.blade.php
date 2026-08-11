@extends('layouts.app')

@section('content')
    <div
        class="space-y-6"
        x-data="{
            open: false,
            loading: false,
            error: null,
            studentName: '',
            createUrl: '',
            links: [],
            async openParents(url, name) {
                this.open = true;
                this.loading = true;
                this.error = null;
                this.studentName = name;
                this.links = [];
                this.createUrl = '';
                try {
                    const response = await fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    if (! response.ok) {
                        throw new Error('Não foi possível carregar os responsáveis.');
                    }
                    const data = await response.json();
                    this.links = data.links || [];
                    this.createUrl = data.create_url || '';
                    this.studentName = data.student?.name || name;
                } catch (e) {
                    this.error = e.message || 'Erro ao carregar responsáveis.';
                } finally {
                    this.loading = false;
                }
            },
            closeParents() {
                this.open = false;
            },
        }"
        @keydown.escape.window="open && closeParents()"
        x-init="$watch('open', value => { document.body.style.overflow = value ? 'hidden' : '' })"
    >
        <style>[x-cloak]{display:none!important}</style>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="flex items-center gap-2.5 text-2xl font-semibold text-gray-800 dark:text-white/90">
                    <span class="inline-flex size-8 items-center justify-center text-brand-500 dark:text-brand-400">
                        {!! \App\Helpers\MenuHelper::getIconSvg('student') !!}
                    </span>
                    Alunos
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Gerencie os alunos do seu tenant.</p>
            </div>
            <a href="{{ route('tenant.students.create') }}"
               class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Novo Aluno
            </a>
        </div>

        @if ($errors->any())
            <div class="rounded-xl border border-error-500 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/15 dark:text-error-400">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <form method="GET" action="{{ route('tenant.students.index') }}" class="mb-4 flex w-full flex-row gap-3">
                <input type="text"
                       name="search"
                       value="{{ $search }}"
                       placeholder="Buscar por nome ou matrícula..."
                       class="h-11 min-w-0 flex-[2] basis-0 rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">

                <select name="school_id"
                        class="h-11 min-w-0 flex-1 basis-0 rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
                    <option value="">Todas as escolas</option>
                    @foreach($schools as $school)
                        <option value="{{ $school->id }}" @selected((int) $schoolId === (int) $school->id)>{{ $school->name }}</option>
                    @endforeach
                </select>

                <select name="status"
                        class="h-11 min-w-0 flex-1 basis-0 rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
                    <option value="">Todos os status</option>
                    @foreach($statusOptions as $statusValue => $statusLabel)
                        <option value="{{ $statusValue }}" @selected($status === $statusValue)>{{ $statusLabel }}</option>
                    @endforeach
                </select>

                <button type="submit"
                        class="inline-flex h-11 shrink-0 items-center justify-center rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
                    Filtrar
                </button>
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
                                    {{ $statusOptions[$student->status] ?? $student->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <button type="button"
                                            @click="openParents(@js(route('tenant.students.parents', $student)), @js($student->name))"
                                            title="Responsáveis vinculados"
                                            class="inline-flex h-10 items-center justify-center gap-1.5 rounded-lg border border-brand-200 bg-brand-50 px-3 text-xs font-medium text-brand-600 transition-colors hover:bg-brand-100 hover:text-brand-700 dark:border-brand-500/30 dark:bg-brand-500/15 dark:text-brand-400 dark:hover:bg-brand-500/25">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <path d="M16 11a3.5 3.5 0 100-7 3.5 3.5 0 000 7zM8 12a3.5 3.5 0 100-7 3.5 3.5 0 000 7zM2.5 20a5.5 5.5 0 0111 0M12.5 20a5.5 5.5 0 019 0" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                        </svg>
                                        Responsáveis
                                    </button>
                                    <a href="{{ route('tenant.students.show', $student) }}"
                                       title="Visualizar"
                                       class="inline-flex size-10 items-center justify-center rounded-lg text-brand-500 transition-colors hover:bg-brand-50 hover:text-brand-700 dark:text-brand-400 dark:hover:bg-white/5 dark:hover:text-brand-300">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <path d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12s-3.75 6.75-9.75 6.75S2.25 12 2.25 12Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <circle cx="12" cy="12" r="2.75" stroke="currentColor" stroke-width="1.5"/>
                                        </svg>
                                        <span class="sr-only">Visualizar</span>
                                    </a>
                                    <a href="{{ route('tenant.students.edit', $student) }}"
                                       title="Editar"
                                       class="inline-flex size-10 items-center justify-center rounded-lg text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <path d="M4 20h4l10.5-10.5a2.121 2.121 0 0 0-3-3L5 17v3Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M13.5 6.5l3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        <span class="sr-only">Editar</span>
                                    </a>
                                    <form method="POST" action="{{ route('tenant.students.destroy', $student) }}" onsubmit="return confirm('Excluir este registro?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                title="Excluir"
                                                class="inline-flex size-10 items-center justify-center rounded-lg text-gray-500 transition-colors hover:bg-error-50 hover:text-error-600 dark:text-gray-400 dark:hover:bg-error-500/10 dark:hover:text-error-400">
                                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                <path d="M3 6h18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                <path d="M8 6V4.5A1.5 1.5 0 0 1 9.5 3h5A1.5 1.5 0 0 1 16 4.5V6M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M10 11v6M14 11v6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                            </svg>
                                            <span class="sr-only">Excluir</span>
                                        </button>
                                    </form>
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

        {{-- Modal responsáveis --}}
        <div
            x-show="open"
            x-cloak
            class="fixed inset-0 z-99999 flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="student-parents-modal-title"
        >
            <div
                class="absolute inset-0 bg-gray-900/50 backdrop-blur-[2px]"
                @click="closeParents()"
            ></div>

            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                class="relative flex max-h-[85vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-gray-800 dark:bg-gray-900"
                @click.stop
            >
                <div class="flex items-start justify-between gap-4 border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                    <div>
                        <h2 id="student-parents-modal-title" class="text-lg font-semibold text-gray-800 dark:text-white/90">
                            Responsáveis vinculados
                        </h2>
                        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                            Aluno: <span class="font-medium text-gray-700 dark:text-gray-300" x-text="studentName"></span>
                        </p>
                    </div>
                    <button type="button"
                            @click="closeParents()"
                            class="inline-flex size-9 items-center justify-center rounded-lg text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-white/5 dark:hover:text-white"
                            aria-label="Fechar">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto px-5 py-4">
                    <template x-if="loading">
                        <p class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">Carregando responsáveis...</p>
                    </template>

                    <template x-if="!loading && error">
                        <p class="rounded-xl border border-error-500 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/15 dark:text-error-400" x-text="error"></p>
                    </template>

                    <template x-if="!loading && !error">
                        <div>
                            <template x-if="links.length === 0">
                                <p class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Nenhum responsável vinculado a este aluno.
                                </p>
                            </template>

                            <template x-if="links.length > 0">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                                        <thead>
                                        <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                            <th class="px-3 py-2">Responsável</th>
                                            <th class="px-3 py-2">Telefone</th>
                                            <th class="px-3 py-2">Relação</th>
                                            <th class="px-3 py-2">Principal</th>
                                            <th class="px-3 py-2">Financeiro</th>
                                            <th class="px-3 py-2 text-right">Ações</th>
                                        </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        <template x-for="link in links" :key="link.id">
                                            <tr>
                                                <td class="px-3 py-3 text-sm font-medium text-gray-800 dark:text-white/90" x-text="link.parent_name"></td>
                                                <td class="px-3 py-3 text-sm text-gray-600 dark:text-gray-300" x-text="link.phone"></td>
                                                <td class="px-3 py-3 text-sm text-gray-600 dark:text-gray-300" x-text="link.relationship_type"></td>
                                                <td class="px-3 py-3">
                                                    <span
                                                        class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium"
                                                        :class="link.is_primary
                                                            ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                                                            : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'"
                                                        x-text="link.is_primary ? 'Sim' : 'Não'"
                                                    ></span>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <span
                                                        class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium"
                                                        :class="link.financial_responsible
                                                            ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                                                            : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'"
                                                        x-text="link.financial_responsible ? 'Sim' : 'Não'"
                                                    ></span>
                                                </td>
                                                <td class="px-3 py-3 text-right">
                                                    <div class="inline-flex items-center justify-end gap-1">
                                                        <a :href="link.show_url"
                                                           title="Visualizar vínculo"
                                                           class="inline-flex size-9 items-center justify-center rounded-lg text-brand-500 transition-colors hover:bg-brand-50 dark:hover:bg-white/5">
                                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                                <path d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12s-3.75 6.75-9.75 6.75S2.25 12 2.25 12Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                <circle cx="12" cy="12" r="2.75" stroke="currentColor" stroke-width="1.5"/>
                                                            </svg>
                                                        </a>
                                                        <a :href="link.edit_url"
                                                           title="Editar vínculo"
                                                           class="inline-flex size-9 items-center justify-center rounded-lg text-gray-500 transition-colors hover:bg-gray-100 dark:hover:bg-white/5">
                                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                                <path d="M4 20h4l10.5-10.5a2.121 2.121 0 0 0-3-3L5 17v3Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                <path d="M13.5 6.5l3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                            </svg>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                        </tbody>
                                    </table>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                <div class="flex flex-col-reverse gap-2 border-t border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-end dark:border-gray-800">
                    <button type="button"
                            @click="closeParents()"
                            class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-transparent dark:text-gray-300 dark:hover:bg-white/5">
                        Fechar
                    </button>
                    <a x-show="createUrl"
                       :href="createUrl"
                       class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 text-sm font-medium text-white transition-colors hover:bg-brand-600">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        Adicionar responsável
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
