    @php
        $input = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90';
        $featuresText = old('features_text', is_array($plan->features ?? null) ? implode("\n", $plan->features) : '');
    @endphp
    <div class="mx-auto max-w-3xl space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ isset($plan) ? 'Editar Plano' : 'Novo Plano' }}</h1>
            </div>
            <a href="{{ route('admin.plans.index') }}" class="text-sm font-medium text-brand-600">Voltar</a>
        </div>

        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ isset($plan) ? route('admin.plans.update', $plan) : route('admin.plans.store') }}" class="space-y-5 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            @csrf
            @if(isset($plan)) @method('PUT') @endif

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Nome</label>
                <input type="text" name="name" value="{{ old('name', $plan->name ?? '') }}" required class="{{ $input }}">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Slug</label>
                <input type="text" name="slug" value="{{ old('slug', $plan->slug ?? '') }}" class="{{ $input }}" placeholder="Gerado automaticamente se vazio">
            </div>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Preço</label>
                    <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $plan->price ?? '0') }}" required class="{{ $input }}">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Ciclo</label>
                    <select name="billing_cycle" class="{{ $input }}">
                        @foreach($billingCycles as $key => $label)
                            <option value="{{ $key }}" @selected(old('billing_cycle', $plan->billing_cycle ?? 'monthly') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Máx. alunos</label>
                    <input type="number" min="0" name="max_students" value="{{ old('max_students', $plan->max_students ?? '') }}" class="{{ $input }}">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Máx. usuários</label>
                    <input type="number" min="0" name="max_users" value="{{ old('max_users', $plan->max_users ?? '') }}" class="{{ $input }}">
                </div>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Recursos (um por linha)</label>
                <textarea name="features_text" rows="4" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:text-white/90">{{ $featuresText }}</textarea>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Ativo</label>
                <select name="active" class="{{ $input }}">
                    <option value="1" @selected((string) old('active', isset($plan) ? (int) $plan->active : 1) === '1')>Sim</option>
                    <option value="0" @selected((string) old('active', isset($plan) ? (int) $plan->active : 1) === '0')>Não</option>
                </select>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="inline-flex rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Salvar</button>
            </div>
        </form>
    </div>
