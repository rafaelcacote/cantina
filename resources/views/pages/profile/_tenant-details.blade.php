@php
    use App\Support\Phone;

    $statusLabels = [
        'active' => 'ativo',
        'inactive' => 'inativo',
        'suspended' => 'suspenso',
        'trial' => 'trial',
    ];
@endphp

<div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
        {{ $canEditTenant ? 'Informações administrativas' : 'Dados da cantina' }}
    </h2>

    <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        @unless ($canEditTenant)
            <div class="sm:col-span-2">
                <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Logo</dt>
                <dd class="mt-2">
                    @if ($tenant->logoSrc())
                        <img src="{{ $tenant->logoSrc() }}"
                             alt="Logo de {{ $tenant->name }}"
                             class="h-20 w-20 rounded-lg border border-gray-200 object-contain dark:border-gray-700">
                    @else
                        <span class="inline-flex size-20 items-center justify-center rounded-lg border border-gray-200 bg-gray-50 font-outfit text-2xl font-semibold text-brand-600 dark:border-gray-700 dark:bg-gray-800 dark:text-brand-400">
                            {{ mb_strtoupper(mb_substr($tenant->name, 0, 1)) }}
                        </span>
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Nome</dt>
                <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $tenant->name }}</dd>
            </div>
        @endunless

        <div>
            <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Slug</dt>
            <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $tenant->slug }}</dd>
        </div>
        <div>
            <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Documento</dt>
            <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $tenant->document ?: '-' }}</dd>
        </div>

        @unless ($canEditTenant)
            <div>
                <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Email</dt>
                <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $tenant->email ?: '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Telefone</dt>
                <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ Phone::format($tenant->phone) ?: ($tenant->phone ?: '-') }}</dd>
            </div>
            <div>
                <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">PIX</dt>
                <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $tenant->pix ?: '-' }}</dd>
            </div>
        @endunless

        <div>
            <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</dt>
            <dd class="mt-1">
                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $tenant->status === 'active' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                    {{ $statusLabels[$tenant->status] ?? $tenant->status }}
                </span>
            </dd>
        </div>
        <div>
            <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Trial até</dt>
            <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $tenant->trial_ends_at?->format('d/m/Y H:i') ?: '-' }}</dd>
        </div>
        <div>
            <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Assinatura até</dt>
            <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $tenant->subscription_ends_at?->format('d/m/Y H:i') ?: '-' }}</dd>
        </div>
        <div>
            <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Criado em</dt>
            <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $tenant->created_at?->format('d/m/Y H:i') ?: '-' }}</dd>
        </div>
    </dl>
</div>
