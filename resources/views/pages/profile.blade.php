@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        <div>
            <h1 class="flex items-center gap-2.5 text-2xl font-semibold text-gray-800 dark:text-white/90">
                <span class="inline-flex size-8 items-center justify-center text-brand-500 dark:text-brand-400">
                    {!! \App\Helpers\MenuHelper::getIconSvg('user-profile') !!}
                </span>
                Perfil
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                @if ($canEditTenant)
                    Gerencie os dados operacionais da sua cantina.
                @else
                    Dados da cantina vinculada à sua conta.
                @endif
            </p>
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-900/40 dark:bg-green-900/20 dark:text-green-300">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-300">
                {{ $errors->first() }}
            </div>
        @endif

        @if ($tenant)
            @if ($canEditTenant)
                @include('pages.profile._tenant-edit-form', ['tenant' => $tenant])
            @endif

            @include('pages.profile._tenant-details', ['tenant' => $tenant, 'canEditTenant' => $canEditTenant])
        @else
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-sm text-gray-600 dark:text-gray-400">Nenhum tenant vinculado a esta conta.</p>
            </div>
        @endif
    </div>
@endsection
