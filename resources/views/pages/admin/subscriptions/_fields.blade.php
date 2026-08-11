    @php $input = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90'; @endphp
    <div class="mx-auto max-w-3xl space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ isset($subscription) ? 'Editar Assinatura' : 'Nova Assinatura' }}</h1>
            <a href="{{ route('admin.subscriptions.index') }}" class="text-sm font-medium text-brand-600">Voltar</a>
        </div>
        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ isset($subscription) ? route('admin.subscriptions.update', $subscription) : route('admin.subscriptions.store') }}" class="space-y-5 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            @csrf
            @if(isset($subscription)) @method('PUT') @endif
            <div>
                <label class="mb-1.5 block text-sm font-medium">Tenant</label>
                <select name="tenant_id" required class="{{ $input }}">
                    <option value="">Selecione</option>
                    @foreach($tenants as $tenant)
                        <option value="{{ $tenant->id }}" @selected((int) old('tenant_id', $subscription->tenant_id ?? '') === $tenant->id)>{{ $tenant->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium">Plano</label>
                <select name="plan_id" required class="{{ $input }}">
                    <option value="">Selecione</option>
                    @foreach($plans as $plan)
                        <option value="{{ $plan->id }}" @selected((int) old('plan_id', $subscription->plan_id ?? '') === $plan->id)>{{ $plan->name }} — R$ {{ number_format((float) $plan->price, 2, ',', '.') }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium">Status</label>
                <select name="status" class="{{ $input }}">
                    @foreach($statuses as $key => $label)
                        <option value="{{ $key }}" @selected(old('status', $subscription->status ?? 'active') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium">Início</label>
                    <input type="datetime-local" name="starts_at" value="{{ old('starts_at', isset($subscription) && $subscription->starts_at ? $subscription->starts_at->format('Y-m-d\TH:i') : '') }}" class="{{ $input }}">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium">Fim</label>
                    <input type="datetime-local" name="ends_at" value="{{ old('ends_at', isset($subscription) && $subscription->ends_at ? $subscription->ends_at->format('Y-m-d\TH:i') : '') }}" class="{{ $input }}">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium">Trial até</label>
                    <input type="datetime-local" name="trial_ends_at" value="{{ old('trial_ends_at', isset($subscription) && $subscription->trial_ends_at ? $subscription->trial_ends_at->format('Y-m-d\TH:i') : '') }}" class="{{ $input }}">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium">Próxima cobrança</label>
                    <input type="datetime-local" name="next_billing_at" value="{{ old('next_billing_at', isset($subscription) && $subscription->next_billing_at ? $subscription->next_billing_at->format('Y-m-d\TH:i') : '') }}" class="{{ $input }}">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium">Cancelada em</label>
                    <input type="datetime-local" name="cancelled_at" value="{{ old('cancelled_at', isset($subscription) && $subscription->cancelled_at ? $subscription->cancelled_at->format('Y-m-d\TH:i') : '') }}" class="{{ $input }}">
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Salvar</button>
            </div>
        </form>
    </div>
