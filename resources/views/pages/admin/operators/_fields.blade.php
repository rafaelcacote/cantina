@php $input = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90'; @endphp
<div class="mx-auto max-w-3xl space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ isset($operator) ? 'Editar Operador' : 'Novo Operador' }}</h1>
        <a href="{{ route('admin.operators.index') }}" class="text-sm font-medium text-brand-600">Voltar</a>
    </div>
    @if ($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif
    <form method="POST" action="{{ isset($operator) ? route('admin.operators.update', $operator) : route('admin.operators.store') }}" class="space-y-5 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
        @csrf
        @if(isset($operator)) @method('PUT') @endif
        <div>
            <label class="mb-1.5 block text-sm font-medium">Tenant</label>
            <select name="tenant_id" required class="{{ $input }}">
                <option value="">Selecione</option>
                @foreach($tenants as $tenant)
                    <option value="{{ $tenant->id }}" @selected((int) old('tenant_id', $operator->tenant_id ?? $tenantId ?? '') === $tenant->id)>{{ $tenant->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium">Usuário</label>
            <select name="user_id" required class="{{ $input }}">
                <option value="">Selecione</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" @selected((int) old('user_id', $operator->user_id ?? '') === $user->id)>{{ $user->name }} ({{ $user->email }})</option>
                @endforeach
            </select>
            <p class="mt-1.5 text-xs text-gray-500">Liste usuários com tipo operator/manager do tenant.</p>
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium">Escola (opcional)</label>
            <select name="school_id" class="{{ $input }}">
                <option value="">Nenhuma</option>
                @foreach($schools as $school)
                    <option value="{{ $school->id }}" @selected((int) old('school_id', $operator->school_id ?? '') === $school->id)>{{ $school->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium">Papel</label>
            <select name="role" class="{{ $input }}">
                @foreach($roles as $key => $label)
                    <option value="{{ $key }}" @selected(old('role', $operator->role ?? 'operator') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex justify-end">
            <button type="submit" class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Salvar</button>
        </div>
    </form>
</div>
