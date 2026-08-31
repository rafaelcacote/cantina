@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="flex items-center gap-2.5 text-2xl font-semibold text-gray-800 dark:text-white/90">
                    <span class="inline-flex size-8 items-center justify-center text-brand-500 dark:text-brand-400">
                        {!! \App\Helpers\MenuHelper::getIconSvg('parent') !!}
                    </span>
                    Convites de solicitantes
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Gere um link para o solicitante criar o acesso e pedir na cantina (sem filhos).
                </p>
            </div>
            <a href="{{ route('tenant.requester-invitations.create') }}"
               class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Novo convite
            </a>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead>
                    <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        <th class="px-4 py-3">Link</th>
                        <th class="px-4 py-3">Usos</th>
                        <th class="px-4 py-3">Expira em</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Ações</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($invitations as $invitation)
                        @php $url = $invitation->acceptUrl(); @endphp
                        <tr>
                            <td class="px-4 py-3">
                                <p class="max-w-xs truncate text-sm font-medium text-gray-800 dark:text-white/90" title="{{ $url }}">
                                    {{ $url }}
                                </p>
                                <p class="mt-0.5 text-xs text-gray-500">
                                    Criado {{ $invitation->created_at?->format('d/m/Y') }}
                                    @if ($invitation->creator)
                                        · {{ $invitation->creator->name }}
                                    @endif
                                </p>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                {{ $invitation->used_count }}{{ $invitation->max_uses ? ' / '.$invitation->max_uses : ' · ilimitado' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                {{ $invitation->expires_at?->format('d/m/Y H:i') ?? 'Sem expiração' }}
                            </td>
                            <td class="px-4 py-3">
                                @if ($invitation->isUsable())
                                    <span class="inline-flex rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-300">Ativo</span>
                                @else
                                    <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">Inativo</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('tenant.requester-invitations.show', $invitation) }}"
                                   title="Abrir e enviar"
                                   class="inline-flex h-10 items-center justify-center rounded-lg border border-brand-200 bg-brand-50 px-3 text-xs font-medium text-brand-600 hover:bg-brand-100 dark:border-brand-500/30 dark:bg-brand-500/15 dark:text-brand-400">
                                    Enviar
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                Nenhum convite ainda. Gere um link para enviar aos solicitantes.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $invitations->links() }}
            </div>
        </div>
    </div>
@endsection
