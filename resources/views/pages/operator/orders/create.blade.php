@extends('layouts.app')

@section('content')
    @php $input = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90'; @endphp
    <div class="mx-auto max-w-xl space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Novo Pedido</h1>
            <a href="{{ route('operator.orders.index') }}" class="text-sm font-medium text-brand-600">Voltar</a>
        </div>
        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('operator.orders.store') }}" class="space-y-5 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            @csrf
            <div>
                <label class="mb-1.5 block text-sm font-medium">Escola</label>
                <select name="school_id" required class="{{ $input }}">
                    @foreach($schools as $school)
                        <option value="{{ $school->id }}" @selected((int) old('school_id', $defaultSchoolId) === $school->id)>{{ $school->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium">Aluno</label>
                <select name="student_id" required class="{{ $input }}">
                    <option value="">Selecione</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" data-school="{{ $student->school_id }}" @selected((int) old('student_id') === $student->id)>{{ $student->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium">Pagamento</label>
                <select name="payment_mode" class="{{ $input }}">
                    @foreach($paymentModes as $key => $label)
                        <option value="{{ $key }}" @selected(old('payment_mode', 'wallet') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium">Observações</label>
                <textarea name="notes" rows="2" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:text-white/90">{{ old('notes') }}</textarea>
            </div>
            <button type="submit" class="w-full rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Criar pedido</button>
        </form>
    </div>
@endsection
