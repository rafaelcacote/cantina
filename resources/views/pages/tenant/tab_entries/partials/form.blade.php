@php($entry = $entry ?? null)
<div class="grid grid-cols-1 gap-6 md:grid-cols-3">
    <div>
        <label for="student_id" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Aluno *</label>
        <select id="student_id" name="student_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            <option value="">Selecione</option>
            @foreach ($students as $student)
                <option value="{{ $student->id }}" @selected(old('student_id', $entry?->student_id) == $student->id)>{{ $student->name }}</option>
            @endforeach
        </select>
        @error('student_id') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="student_tab_id" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Conta de fiado *</label>
        <select id="student_tab_id" name="student_tab_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            <option value="">Selecione</option>
            @foreach ($studentTabs as $tab)
                <option value="{{ $tab->id }}" data-student-id="{{ $tab->student_id }}" @selected(old('student_tab_id', $entry?->student_tab_id) == $tab->id)>
                    #{{ $tab->id }} — {{ $tab->student?->name ?? 'Aluno '.$tab->student_id }}
                </option>
            @endforeach
        </select>
        @error('student_tab_id') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="order_id" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Pedido</label>
        <select id="order_id" name="order_id" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            <option value="">Selecione</option>
            @foreach ($orders as $order)
                <option value="{{ $order->id }}" @selected(old('order_id', $entry?->order_id) == $order->id)>#{{ $order->id }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="amount" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Valor *</label>
        <input id="amount" name="amount" type="number" min="0" step="0.01" value="{{ old('amount', $entry?->amount) }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
        @error('amount') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="entry_date" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Data *</label>
        <input id="entry_date" name="entry_date" type="date" value="{{ old('entry_date', $entry?->entry_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
    </div>
    <div>
        <label for="status" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Status *</label>
        <select id="status" name="status" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            @foreach ($statuses as $key => $label)
                <option value="{{ $key }}" @selected(old('status', $entry?->status ?? 'open') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="authorization_method" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Método autorização</label>
        <select id="authorization_method" name="authorization_method" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            <option value="">Selecione</option>
            @foreach ($authorizationMethods as $key => $label)
                <option value="{{ $key }}" @selected(old('authorization_method', $entry?->authorization_method) === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="authorized_at" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Autorizado em</label>
        <input id="authorized_at" name="authorized_at" type="datetime-local" value="{{ old('authorized_at', $entry?->authorized_at?->format('Y-m-d\TH:i')) }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
    </div>
    <div class="md:col-span-3">
        <label for="description" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Descrição</label>
        <input id="description" name="description" type="text" value="{{ old('description', $entry?->description) }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
    </div>
    <div class="md:col-span-3">
        <label for="created_by" class="mb-2.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Criado por</label>
        <select id="created_by" name="created_by" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            <option value="">Selecione</option>
            @foreach ($users as $user)
                <option value="{{ $user->id }}" @selected(old('created_by', $entry?->created_by ?? auth()->id()) == $user->id)>{{ $user->name }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="mt-6">
    <label class="inline-flex items-center gap-3 rounded-lg border border-gray-200 px-4 py-3 dark:border-gray-800">
        <input type="checkbox" name="authorized_by_pin" value="1" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" @checked(old('authorized_by_pin', $entry?->authorized_by_pin ?? false))>
        <span class="text-sm text-gray-700 dark:text-gray-300">Autorizado por PIN</span>
    </label>
</div>
