@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="flex items-center gap-2.5 text-2xl font-semibold text-gray-800 dark:text-white/90">
                    <span class="inline-flex size-8 items-center justify-center text-brand-500 dark:text-brand-400">
                        {!! \App\Helpers\MenuHelper::getIconSvg('charts') !!}
                    </span>
                    Recargas Pix
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Confira o comprovante e libere o crédito na carteira do aluno.
                    @if ($pendingCount)
                        <span class="font-medium text-brand-600">{{ $pendingCount }} aguardando conferência.</span>
                    @endif
                </p>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <form method="GET" action="{{ route('tenant.wallet-topups.index') }}" class="mb-4 flex w-full flex-row gap-3">
                <input type="text"
                       name="search"
                       value="{{ $search }}"
                       placeholder="Código, aluno ou responsável..."
                       class="h-11 min-w-0 flex-1 basis-0 rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">

                <select name="status"
                        class="h-11 min-w-0 flex-1 basis-0 rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
                    <option value="">Todos os status</option>
                    @foreach ($statuses as $key => $label)
                        <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
                    @endforeach
                </select>

                <button type="submit"
                        class="inline-flex h-11 shrink-0 items-center justify-center rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
                    Filtrar
                </button>
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead>
                    <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        <th class="px-4 py-3">Código</th>
                        <th class="px-4 py-3">Aluno</th>
                        <th class="px-4 py-3">Responsável</th>
                        <th class="px-4 py-3">Valor</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Ações</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($topups as $topup)
                        @php
                            $badge = match ($topup->status) {
                                'approved' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                                'rejected' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                                'pending_review' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                                default => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
                            };
                        @endphp
                        <tr>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-800 dark:text-white/90">#{{ $topup->code }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $topup->student?->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $topup->parent?->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-800 dark:text-white/90">{{ $topup->formattedAmount() }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $badge }}">
                                    {{ $statuses[$topup->status] ?? $topup->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('tenant.wallet-topups.show', $topup) }}"
                                   title="Conferir"
                                   class="inline-flex h-10 items-center justify-center rounded-lg border border-brand-200 bg-brand-50 px-3 text-xs font-medium text-brand-600 hover:bg-brand-100 dark:border-brand-500/30 dark:bg-brand-500/15 dark:text-brand-400">
                                    Conferir
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                Nenhuma recarga encontrada.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $topups->links() }}
            </div>
        </div>
    </div>
@endsection
