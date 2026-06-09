@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Editar Escola</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Atualize os dados da unidade.</p>
            </div>
            <a href="{{ route('admin.schools.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">
                Voltar
            </a>
        </div>

        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-300">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.schools.update', $school) }}" class="space-y-5 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            @csrf
            @method('PUT')

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Tenant</label>
                <select name="tenant_id" required
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
                    @foreach($tenants as $tenant)
                        <option value="{{ $tenant->id }}" @selected((string) old('tenant_id', $school->tenant_id) === (string) $tenant->id)>{{ $tenant->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Nome</label>
                <input type="text" name="name" value="{{ old('name', $school->name) }}" required
                       class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Documento</label>
                    <input type="text" name="document" value="{{ old('document', $school->document) }}"
                           class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Telefone</label>
                    <input type="text" name="phone" value="{{ old('phone', $school->phone) }}"
                           class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
                </div>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                <input type="email" name="email" value="{{ old('email', $school->email) }}"
                       class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Endereço</label>
                <textarea name="address" rows="3"
                          class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">{{ old('address', $school->address) }}</textarea>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                <select name="active"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
                    <option value="1" @selected(old('active', (string) (int) $school->active) === '1')>ativo</option>
                    <option value="0" @selected(old('active', (string) (int) $school->active) === '0')>inativo</option>
                </select>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                    Atualizar Escola
                </button>
            </div>
        </form>
    </div>
@endsection
