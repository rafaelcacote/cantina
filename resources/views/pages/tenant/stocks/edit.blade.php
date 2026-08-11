@extends('layouts.app')

@section('content')
    @php
        $inputClass = fn (string $field) => 'h-11 w-full rounded-lg border bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:text-white/90 '.($errors->has($field) ? 'border-error-500 dark:border-error-500' : 'border-gray-300 dark:border-gray-700');
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="flex items-center gap-2.5 text-2xl font-semibold text-gray-800 dark:text-white/90">
                    <span class="inline-flex size-8 items-center justify-center text-brand-500 dark:text-brand-400">
                        {!! \App\Helpers\MenuHelper::getIconSvg('tables') !!}
                    </span>
                    Editar Estoque
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Atualize as quantidades do estoque.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('tenant.stocks.show', $stock) }}"
                   class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-transparent dark:text-gray-300 dark:hover:bg-white/5">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12s-3.75 6.75-9.75 6.75S2.25 12 2.25 12Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="12" cy="12" r="2.75" stroke="currentColor" stroke-width="1.5"/>
                    </svg>
                    Visualizar
                </a>
                <a href="{{ route('tenant.stocks.index') }}"
                   class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-transparent dark:text-gray-300 dark:hover:bg-white/5">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Voltar
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div class="rounded-xl border border-error-500 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/15 dark:text-error-400">
                Verifique os campos obrigatórios destacados abaixo.
            </div>
        @endif

        <div class="flex flex-col gap-6 xl:flex-row">
            <form method="POST" action="{{ route('tenant.stocks.update', $stock) }}" class="min-w-0 flex-[2] overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]" novalidate>
                @csrf
                @method('PUT')
                <div class="space-y-5 p-6">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Produto</label>
                        <p class="flex h-11 items-center rounded-lg border border-gray-200 bg-gray-50 px-4 text-sm text-gray-700 dark:border-gray-700 dark:bg-white/[0.03] dark:text-gray-300">
                            {{ $stock->product?->name ?? '-' }}
                        </p>
                    </div>

                    <div class="flex w-full flex-row gap-3">
                        <div class="min-w-0 flex-1">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Quantidade atual <span class="text-error-500">*</span>
                            </label>
                            <input type="number" name="quantity" min="0" value="{{ old('quantity', $stock->quantity) }}" required class="{{ $inputClass('quantity') }}">
                            @error('quantity')
                                <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="min-w-0 flex-1">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Quantidade reservada <span class="text-error-500">*</span>
                            </label>
                            <input type="number" name="reserved_quantity" min="0" value="{{ old('reserved_quantity', $stock->reserved_quantity) }}" required class="{{ $inputClass('reserved_quantity') }}">
                            @error('reserved_quantity')
                                <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-2 border-t border-gray-200 bg-gray-50 px-6 py-4 sm:flex-row sm:items-center sm:justify-end dark:border-gray-800 dark:bg-white/[0.02]">
                    <a href="{{ route('tenant.stocks.show', $stock) }}"
                       class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-transparent dark:text-gray-300 dark:hover:bg-white/5">
                        Cancelar
                    </a>
                    <button type="submit"
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 text-sm font-medium text-white transition-colors hover:bg-brand-600">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Salvar alterações
                    </button>
                </div>
            </form>

            <div class="min-w-0 flex-1 overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Ajuste rápido</h2>
                </div>
                <form method="POST" action="{{ route('tenant.stocks.adjust', $stock) }}" class="space-y-4 p-6" novalidate>
                    @csrf
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Tipo <span class="text-error-500">*</span>
                        </label>
                        <select name="movement_type" class="{{ $inputClass('movement_type') }}">
                            @foreach ($movementTypes as $key => $label)
                                <option value="{{ $key }}" @selected(old('movement_type') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Quantidade <span class="text-error-500">*</span>
                        </label>
                        <input type="number" name="quantity" min="0" value="{{ old('quantity') }}" required class="{{ $inputClass('quantity') }}">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Descrição</label>
                        <textarea name="description" rows="3"
                                  class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">{{ old('description') }}</textarea>
                    </div>
                    <button type="submit"
                            class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 text-sm font-medium text-white transition-colors hover:bg-brand-600">
                        Aplicar ajuste
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
