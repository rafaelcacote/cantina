<div>
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Nome</label>
    <input type="text" name="name" value="{{ old('name', $school->name ?? '') }}" required
           class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
</div>

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Documento</label>
        <input type="text" name="document" value="{{ old('document', $school->document ?? '') }}"
               class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
    </div>
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Telefone</label>
        <input type="text" name="phone" value="{{ old('phone', $school->phone ?? '') }}"
               class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
    </div>
</div>

<div>
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
    <input type="email" name="email" value="{{ old('email', $school->email ?? '') }}"
           class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
</div>

<div>
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Endereço</label>
    <textarea name="address" rows="3"
              class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">{{ old('address', $school->address ?? '') }}</textarea>
</div>

<div>
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
    <select name="active"
            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
        <option value="1" @selected(old('active', isset($school) ? (string) (int) $school->active : '1') === '1')>ativo</option>
        <option value="0" @selected(old('active', isset($school) ? (string) (int) $school->active : '1') === '0')>inativo</option>
    </select>
</div>
