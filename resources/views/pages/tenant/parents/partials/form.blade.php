@php
    $inputClass = fn (string $field) => 'h-11 w-full rounded-lg border bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:text-white/90 '.($errors->has($field) ? 'border-error-500 dark:border-error-500' : 'border-gray-300 dark:border-gray-700');
@endphp

<div>
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Usuário vinculado (opcional)</label>
    <select name="user_id" class="{{ $inputClass('user_id') }}">
        <option value="">Nenhum</option>
        @foreach($users as $user)
            <option value="{{ $user->id }}" @selected((string) old('user_id', $parent?->user_id) === (string) $user->id)>{{ $user->name }} ({{ $user->email }})</option>
        @endforeach
    </select>
    @error('user_id')
        <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
    @enderror
</div>

<div>
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
        Nome <span class="text-error-500">*</span>
    </label>
    <input type="text" name="name" value="{{ old('name', $parent?->name) }}" required class="{{ $inputClass('name') }}">
    @error('name')
        <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
    @enderror
</div>

<div class="flex w-full flex-row gap-3">
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">CPF</label>
        <input type="text" name="cpf" value="{{ old('cpf', $parent?->cpf) }}" class="{{ $inputClass('cpf') }}">
        @error('cpf')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Telefone</label>
        <input type="text" name="phone" value="{{ old('phone', $parent?->phone) }}" class="{{ $inputClass('phone') }}">
        @error('phone')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
        <input type="email" name="email" value="{{ old('email', $parent?->email) }}" class="{{ $inputClass('email') }}">
        @error('email')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
</div>
