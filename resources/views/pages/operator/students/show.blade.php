@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $student->name }}</h1>
                <p class="mt-1 text-sm text-gray-500">{{ $student->school?->name }}</p>
            </div>
            <a href="{{ route('operator.students.index') }}" class="text-sm font-medium text-brand-600">Voltar</a>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2 text-sm">
                <div><dt class="text-xs uppercase text-gray-500">Matrícula</dt><dd class="mt-1 font-medium">{{ $student->enrollment_number ?? '-' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Status</dt><dd class="mt-1 font-medium">{{ $student->status }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Carteira</dt><dd class="mt-1 font-medium">{{ $student->wallet ? 'R$ '.number_format((float) $student->wallet->balance, 2, ',', '.') : 'Sem carteira' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Fiado</dt><dd class="mt-1 font-medium">{{ $student->tab ? 'R$ '.number_format((float) $student->tab->current_balance, 2, ',', '.') : 'Sem fiado' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Pode fiado</dt><dd class="mt-1 font-medium">{{ $student->can_buy_on_tab ? 'Sim' : 'Não' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Pode carteira</dt><dd class="mt-1 font-medium">{{ $student->can_buy_on_credit ? 'Sim' : 'Não' }}</dd></div>
            </dl>
            @if ($student->wallet)
                <div class="mt-4">
                    <a href="{{ route('operator.wallets.show', $student->wallet) }}" class="text-sm font-medium text-brand-600">Ver extrato da carteira</a>
                </div>
            @endif
        </div>
    </div>
@endsection
