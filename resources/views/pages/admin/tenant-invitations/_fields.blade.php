@php $input = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90'; @endphp
<div class="mx-auto max-w-3xl space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ isset($invitation) ? 'Editar Convite' : 'Novo Convite' }}</h1>
        <a href="{{ route('admin.tenant-invitations.index') }}" class="text-sm font-medium text-brand-600">Voltar</a>
    </div>
    @if ($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif
    <form method="POST" action="{{ isset($invitation) ? route('admin.tenant-invitations.update', $invitation) : route('admin.tenant-invitations.store') }}" class="space-y-5 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
        @csrf
        @if(isset($invitation)) @method('PUT') @endif
        <div>
            <label class="mb-1.5 block text-sm font-medium">Tenant</label>
            <select name="tenant_id" required class="{{ $input }}">
                <option value="">Selecione</option>
                @foreach($tenants as $tenant)
                    <option value="{{ $tenant->id }}" @selected((int) old('tenant_id', $invitation->tenant_id ?? '') === $tenant->id)>{{ $tenant->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium">Tipo</label>
            <select name="type" class="{{ $input }}">
                @foreach($types as $key => $label)
                    <option value="{{ $key }}" @selected(old('type', $invitation->type ?? 'tenant_admin') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-sm font-medium">Expira em</label>
                <input type="datetime-local" name="expires_at" value="{{ old('expires_at', isset($invitation) && $invitation->expires_at ? $invitation->expires_at->format('Y-m-d\TH:i') : '') }}" class="{{ $input }}">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium">Máx. usos</label>
                <input type="number" min="1" name="max_uses" value="{{ old('max_uses', $invitation->max_uses ?? 1) }}" class="{{ $input }}">
            </div>
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium">Ativo</label>
            <select name="active" class="{{ $input }}">
                <option value="1" @selected((string) old('active', isset($invitation) ? (int) $invitation->active : 1) === '1')>Sim</option>
                <option value="0" @selected((string) old('active', isset($invitation) ? (int) $invitation->active : 1) === '0')>Não</option>
            </select>
        </div>
        <div class="flex justify-end">
            <button type="submit" class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Salvar</button>
        </div>
    </form>
</div>
