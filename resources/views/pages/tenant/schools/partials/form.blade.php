<div>
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
        Nome <span class="text-error-500">*</span>
    </label>
    <input type="text" name="name" value="{{ old('name', $school->name ?? '') }}" required
           class="h-11 w-full rounded-lg border bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:text-white/90 {{ $errors->has('name') ? 'border-error-500 dark:border-error-500' : 'border-gray-300 dark:border-gray-700' }}">
    @error('name')
        <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
    @enderror
</div>

<div class="flex flex-row gap-3">
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Documento</label>
        <input type="text" name="document" value="{{ old('document', $school->document ?? '') }}"
               class="h-11 w-full rounded-lg border bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:text-white/90 {{ $errors->has('document') ? 'border-error-500 dark:border-error-500' : 'border-gray-300 dark:border-gray-700' }}">
        @error('document')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Telefone</label>
        <input type="text" name="phone" value="{{ old('phone', $school->phone ?? '') }}"
               class="h-11 w-full rounded-lg border bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:text-white/90 {{ $errors->has('phone') ? 'border-error-500 dark:border-error-500' : 'border-gray-300 dark:border-gray-700' }}">
        @error('phone')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
        <input type="email" name="email" value="{{ old('email', $school->email ?? '') }}"
               class="h-11 w-full rounded-lg border bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:text-white/90 {{ $errors->has('email') ? 'border-error-500 dark:border-error-500' : 'border-gray-300 dark:border-gray-700' }}">
        @error('email')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="flex w-full flex-row gap-3">
    <div class="min-w-0 flex-1 basis-0">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Logradouro / Rua</label>
        <input type="text" name="street" value="{{ old('street', $addressParts['street'] ?? '') }}"
               placeholder="Ex: Rua das Flores"
               class="h-11 w-full rounded-lg border bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:text-white/90 {{ $errors->has('street') ? 'border-error-500 dark:border-error-500' : 'border-gray-300 dark:border-gray-700' }}">
        @error('street')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
    <div class="w-24 shrink-0">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Nº</label>
        <input type="text" name="number" value="{{ old('number', $addressParts['number'] ?? '') }}"
               placeholder="123"
               class="h-11 w-full rounded-lg border bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:text-white/90 {{ $errors->has('number') ? 'border-error-500 dark:border-error-500' : 'border-gray-300 dark:border-gray-700' }}">
        @error('number')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
    <div class="min-w-0 flex-1 basis-0">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Bairro</label>
        <input type="text" name="neighborhood" value="{{ old('neighborhood', $addressParts['neighborhood'] ?? '') }}"
               placeholder="Ex: Centro"
               class="h-11 w-full rounded-lg border bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:text-white/90 {{ $errors->has('neighborhood') ? 'border-error-500 dark:border-error-500' : 'border-gray-300 dark:border-gray-700' }}">
        @error('neighborhood')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
</div>

<div>
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
        Status <span class="text-error-500">*</span>
    </label>
    <select name="active"
            class="h-11 w-full rounded-lg border bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:text-white/90 {{ $errors->has('active') ? 'border-error-500 dark:border-error-500' : 'border-gray-300 dark:border-gray-700' }}">
        <option value="1" @selected(old('active', isset($school) ? (string) (int) $school->active : '1') === '1')>ativo</option>
        <option value="0" @selected(old('active', isset($school) ? (string) (int) $school->active : '1') === '0')>inativo</option>
    </select>
    @error('active')
        <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
    @enderror
</div>
