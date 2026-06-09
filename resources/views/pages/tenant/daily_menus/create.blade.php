@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-4xl space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Novo Cardápio</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Cadastre um cardápio do dia para o seu tenant.</p>
            </div>
            <a href="{{ route('tenant.daily-menus.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">
                Voltar
            </a>
        </div>

        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-300">
                Verifique os campos do formulário.
            </div>
        @endif

        <form method="POST" action="{{ route('tenant.daily-menus.store') }}" class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            @csrf
            @include('pages.tenant.daily_menus.partials.form', ['dailyMenu' => null, 'schools' => $schools])

            <div class="mt-8 flex justify-end gap-3">
                <a href="{{ route('tenant.daily-menus.index') }}" class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
                    Cancelar
                </a>
                <button type="submit" class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                    Salvar Cardápio
                </button>
            </div>
        </form>
    </div>
@endsection
