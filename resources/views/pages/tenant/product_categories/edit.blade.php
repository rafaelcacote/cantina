@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Editar Categoria</h1>
            <a href="{{ route('tenant.product-categories.show', $category) }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Voltar</a>
        </div>
        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-300">Verifique os campos do formulário.</div>
        @endif
        <form method="POST" action="{{ route('tenant.product-categories.update', $category) }}" class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            @csrf
            @method('PUT')
            @include('pages.tenant.product_categories.partials.form', ['category' => $category])
            <div class="mt-8 flex gap-3">
                <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Atualizar</button>
                <a href="{{ route('tenant.product-categories.show', $category) }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
