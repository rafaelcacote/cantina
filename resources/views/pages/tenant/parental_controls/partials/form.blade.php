@php($control = $control ?? null)
<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    <div>
        <label for="student_id" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Aluno *</label>
        <select id="student_id" name="student_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            <option value="">Selecione</option>
            @foreach ($students as $student)
                <option value="{{ $student->id }}" @selected(old('student_id', $control?->student_id) == $student->id)>{{ $student->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="control_mode" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Modo de controle *</label>
        <select id="control_mode" name="control_mode" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            @foreach ($controlModes as $key => $label)
                <option value="{{ $key }}" @selected(old('control_mode', $control?->control_mode ?? 'none') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="daily_spending_limit" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Limite diário</label>
        <input id="daily_spending_limit" name="daily_spending_limit" type="number" step="0.01" min="0" value="{{ old('daily_spending_limit', $control?->daily_spending_limit) }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
    </div>
    <div>
        <label for="weekly_spending_limit" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Limite semanal</label>
        <input id="weekly_spending_limit" name="weekly_spending_limit" type="number" step="0.01" min="0" value="{{ old('weekly_spending_limit', $control?->weekly_spending_limit) }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
    </div>
    <div class="md:col-span-2">
        <label for="notes" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Observações</label>
        <textarea id="notes" name="notes" rows="3" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">{{ old('notes', $control?->notes) }}</textarea>
    </div>
</div>
<div class="mt-6 grid grid-cols-1 gap-3 md:grid-cols-2">
    <label class="inline-flex items-center gap-3 rounded-lg border border-gray-200 px-4 py-3 dark:border-gray-800">
        <input type="checkbox" name="enabled" value="1" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" @checked(old('enabled', $control?->enabled ?? false))>
        <span class="text-sm text-gray-700 dark:text-gray-300">Controle ativo</span>
    </label>
    <label class="inline-flex items-center gap-3 rounded-lg border border-gray-200 px-4 py-3 dark:border-gray-800">
        <input type="checkbox" name="allow_tab_usage" value="1" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" @checked(old('allow_tab_usage', $control?->allow_tab_usage ?? true))>
        <span class="text-sm text-gray-700 dark:text-gray-300">Permitir fiado</span>
    </label>
    <label class="inline-flex items-center gap-3 rounded-lg border border-gray-200 px-4 py-3 dark:border-gray-800">
        <input type="checkbox" name="allow_wallet_usage" value="1" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" @checked(old('allow_wallet_usage', $control?->allow_wallet_usage ?? true))>
        <span class="text-sm text-gray-700 dark:text-gray-300">Permitir carteira</span>
    </label>
    <label class="inline-flex items-center gap-3 rounded-lg border border-gray-200 px-4 py-3 dark:border-gray-800">
        <input type="checkbox" name="allow_convenience_access" value="1" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" @checked(old('allow_convenience_access', $control?->allow_convenience_access ?? false))>
        <span class="text-sm text-gray-700 dark:text-gray-300">Permitir conveniência</span>
    </label>
    <label class="inline-flex items-center gap-3 rounded-lg border border-gray-200 px-4 py-3 dark:border-gray-800">
        <input type="checkbox" name="allow_snack_access" value="1" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" @checked(old('allow_snack_access', $control?->allow_snack_access ?? true))>
        <span class="text-sm text-gray-700 dark:text-gray-300">Permitir lanche</span>
    </label>
</div>
