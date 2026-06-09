<div>
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Usuário vinculado (opcional)</label>
    <select name="user_id"
            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
        <option value="">Nenhum</option>
        @foreach($users as $user)
            <option value="{{ $user->id }}" @selected((string) old('user_id', $parent?->user_id) === (string) $user->id)>{{ $user->name }} ({{ $user->email }})</option>
        @endforeach
    </select>
</div>

<div>
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Nome</label>
    <input type="text" name="name" value="{{ old('name', $parent?->name) }}" required
           class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
</div>

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">CPF</label>
        <input type="text" name="cpf" value="{{ old('cpf', $parent?->cpf) }}"
               class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
    </div>
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Telefone</label>
        <input type="text" name="phone" value="{{ old('phone', $parent?->phone) }}"
               class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
    </div>
</div>

<div>
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
    <input type="email" name="email" value="{{ old('email', $parent?->email) }}"
           class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
</div>
