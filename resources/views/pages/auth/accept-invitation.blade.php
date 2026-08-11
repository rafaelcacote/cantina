@extends('layouts.fullscreen-layout')

@section('content')
    <div class="flex min-h-screen items-center justify-center bg-gray-50 px-4 py-10 dark:bg-gray-950">
        <div class="w-full max-w-md space-y-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white">Aceitar convite</h1>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $types[$invitation->type] ?? $invitation->type }} — {{ $invitation->tenant?->name }}
                </p>
            </div>

            @if ($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('invitations.accept.store', $invitation->token) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Nome</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">E-mail</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white">
                </div>
                @if ($invitation->type === 'parent_registration')
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Telefone</label>
                        <input type="text" name="phone" value="{{ old('phone') }}"
                               class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white">
                    </div>
                @endif
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Senha</label>
                    <input type="password" name="password" required
                           class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Confirmar senha</label>
                    <input type="password" name="password_confirmation" required
                           class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white">
                </div>
                <button type="submit" class="inline-flex h-11 w-full items-center justify-center rounded-lg bg-brand-500 text-sm font-medium text-white hover:bg-brand-600">
                    Criar conta
                </button>
            </form>
        </div>
    </div>
@endsection
