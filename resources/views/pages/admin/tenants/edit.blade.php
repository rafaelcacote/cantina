@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Editar Tenant</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Atualize os dados do tenant.</p>
            </div>
            <a href="{{ route('admin.tenants.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">
                Voltar
            </a>
        </div>

        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-300">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.tenants.update', $tenant) }}" enctype="multipart/form-data" class="space-y-5 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]" novalidate>
            @csrf
            @method('PUT')

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Logo</label>
                <div class="flex items-center gap-3">
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800">
                        @if ($tenant->logoSrc())
                            <img id="tenant-logo-preview" src="{{ $tenant->logoSrc() }}" alt="Logo de {{ $tenant->name }}" class="h-full w-full object-cover">
                        @else
                            <img id="tenant-logo-preview" src="" alt="" class="hidden h-full w-full object-cover">
                            <svg id="tenant-logo-placeholder" class="h-7 w-7 text-gray-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M4 7.5A2.5 2.5 0 016.5 5h11A2.5 2.5 0 0120 7.5v9a2.5 2.5 0 01-2.5 2.5h-11A2.5 2.5 0 014 16.5v-9z" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M8.5 13.5l2.2-2.2a1 1 0 011.4 0L15 14.2l1.3-1.3a1 1 0 011.4 0l1.8 1.8M9 9.5h.01" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <input type="file"
                               name="logo"
                               id="tenant-logo-input"
                               accept="image/jpeg,image/png,image/webp,image/gif"
                               class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-brand-600 hover:file:bg-brand-100 dark:text-gray-300 dark:file:bg-brand-500/15 dark:file:text-brand-400 {{ $errors->has('logo') ? 'rounded-lg border border-error-500 p-1' : '' }}">
                        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">JPG, PNG, WEBP ou GIF até 2 MB. Deixe em branco para manter a logo atual.</p>
                        @error('logo')
                            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Nome <span class="text-error-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $tenant->name) }}"
                       class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90 {{ $errors->has('name') ? 'border-error-500' : '' }}">
                @error('name')
                    <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Slug <span class="text-error-500">*</span></label>
                <input type="text" name="slug" value="{{ old('slug', $tenant->slug) }}"
                       class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90 {{ $errors->has('slug') ? 'border-error-500' : '' }}">
                @error('slug')
                    <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <input type="email" name="email" value="{{ old('email', $tenant->email) }}"
                           class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90 {{ $errors->has('email') ? 'border-error-500' : '' }}">
                    @error('email')
                        <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Telefone</label>
                    <input type="text" name="phone" value="{{ old('phone', $tenant->phone) }}"
                           class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90 {{ $errors->has('phone') ? 'border-error-500' : '' }}">
                    @error('phone')
                        <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">PIX</label>
                <input type="text" name="pix" value="{{ old('pix', $tenant->pix) }}" placeholder="CPF, CNPJ, e-mail, telefone ou chave aleatória"
                       class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90 {{ $errors->has('pix') ? 'border-error-500' : '' }}">
                <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Chave PIX do estabelecimento.</p>
                @error('pix')
                    <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Status <span class="text-error-500">*</span></label>
                <select name="status"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90 {{ $errors->has('status') ? 'border-error-500' : '' }}">
                    <option value="active" @selected(old('status', $tenant->status) === 'active')>active</option>
                    <option value="inactive" @selected(old('status', $tenant->status) === 'inactive')>inactive</option>
                </select>
                @error('status')
                    <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end">
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                    Atualizar Tenant
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const input = document.getElementById('tenant-logo-input');
            const preview = document.getElementById('tenant-logo-preview');
            const placeholder = document.getElementById('tenant-logo-placeholder');

            if (!input || !preview) {
                return;
            }

            input.addEventListener('change', () => {
                const file = input.files?.[0];
                if (!file) {
                    return;
                }

                const url = URL.createObjectURL(file);
                preview.src = url;
                preview.classList.remove('hidden');
                placeholder?.classList.add('hidden');
            });
        });
    </script>
@endpush
