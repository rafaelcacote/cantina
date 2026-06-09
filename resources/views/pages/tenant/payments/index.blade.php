@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Pagamentos</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Registro de pagamentos recebidos.</p>
            </div>
            <a href="{{ route('tenant.payments.create') }}" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Novo Pagamento</a>
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-900/40 dark:bg-green-900/20 dark:text-green-300">{{ session('success') }}</div>
        @endif

        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <form method="GET" action="{{ route('tenant.payments.index') }}" class="grid grid-cols-1 gap-3 lg:grid-cols-6">
                <select name="student_id" class="h-11 rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90">
                    <option value="">Todos os alunos</option>
                    @foreach ($students as $student)
                        <option value="{{ $student->id }}" @selected($studentId === (int) $student->id)>{{ $student->name }}</option>
                    @endforeach
                </select>
                <select name="parent_id" class="h-11 rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90">
                    <option value="">Todos os responsáveis</option>
                    @foreach ($parents as $parent)
                        <option value="{{ $parent->id }}" @selected($parentId === (int) $parent->id)>{{ $parent->name }}</option>
                    @endforeach
                </select>
                <select name="payment_method" class="h-11 rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90">
                    <option value="">Todos os métodos</option>
                    @foreach ($methods as $key => $label)
                        <option value="{{ $key }}" @selected($method === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="status" class="h-11 rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90">
                    <option value="">Todos os status</option>
                    @foreach ($statuses as $key => $label)
                        <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <input type="date" name="from" value="{{ $from }}" class="h-11 rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90">
                <input type="date" name="to" value="{{ $to }}" class="h-11 rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90">
                <div class="lg:col-span-6 flex gap-2">
                    <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Filtrar</button>
                    <a href="{{ route('tenant.payments.index') }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Limpar</a>
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Aluno</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Responsável</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Valor</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Método</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Pago em</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($payments as $payment)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $payment->student?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $payment->parent?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">R$ {{ number_format((float) $payment->amount, 2, ',', '.') }}</td>
                                <td class="px-4 py-3"><span class="inline-flex rounded-full px-2 py-1 text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300">{{ $methods[$payment->payment_method] ?? $payment->payment_method }}</span></td>
                                <td class="px-4 py-3"><span class="inline-flex rounded-full px-2 py-1 text-xs font-medium bg-purple-100 text-purple-700 dark:bg-purple-900/20 dark:text-purple-300">{{ $statuses[$payment->status] ?? $payment->status }}</span></td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $payment->paid_at?->format('d/m/Y H:i') ?: '-' }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <div class="flex gap-2">
                                        <a href="{{ route('tenant.payments.show', $payment) }}" class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Visualizar</a>
                                        <a href="{{ route('tenant.payments.edit', $payment) }}" class="rounded-md bg-brand-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-brand-600">Editar</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Nenhum pagamento encontrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-100 px-4 py-3 dark:border-gray-800">{{ $payments->links() }}</div>
        </div>
    </div>
@endsection
