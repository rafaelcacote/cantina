@php
    $inputClass = fn (string $field) => 'h-11 w-full rounded-lg border bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:text-white/90 '.($errors->has($field) ? 'border-error-500 dark:border-error-500' : 'border-gray-300 dark:border-gray-700');
@endphp

<div class="flex w-full flex-row gap-3">
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Escola <span class="text-error-500">*</span>
        </label>
        <select name="school_id" required class="{{ $inputClass('school_id') }}">
            <option value="">Selecione</option>
            @foreach($schools as $schoolOption)
                <option value="{{ $schoolOption->id }}" @selected((string) old('school_id', $student?->school_id ?? '') === (string) $schoolOption->id)>{{ $schoolOption->name }}</option>
            @endforeach
        </select>
        @error('school_id')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Nome <span class="text-error-500">*</span>
        </label>
        <input type="text" name="name" value="{{ old('name', $student?->name ?? '') }}" required class="{{ $inputClass('name') }}">
        @error('name')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="flex w-full flex-row gap-3">
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Matrícula</label>
        <input type="text" name="enrollment_number" value="{{ old('enrollment_number', $student?->enrollment_number ?? '') }}" class="{{ $inputClass('enrollment_number') }}">
        @error('enrollment_number')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Data de nascimento</label>
        <input type="date" name="birth_date" value="{{ old('birth_date', $student?->birth_date?->format('Y-m-d')) }}" class="{{ $inputClass('birth_date') }}">
        @error('birth_date')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
        <select name="status" class="{{ $inputClass('status') }}">
            @foreach($statusOptions as $statusValue => $statusLabel)
                <option value="{{ $statusValue }}" @selected(old('status', $student?->status ?? 'pending') === $statusValue)>{{ $statusLabel }}</option>
            @endforeach
        </select>
        @error('status')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="flex w-full flex-row gap-3">
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Série</label>
        <input type="text" name="grade" value="{{ old('grade', $student?->grade ?? '') }}" class="{{ $inputClass('grade') }}">
        @error('grade')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Turma</label>
        <input type="text" name="classroom" value="{{ old('classroom', $student?->classroom ?? '') }}" class="{{ $inputClass('classroom') }}">
        @error('classroom')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Turno</label>
        <input type="text" name="shift" value="{{ old('shift', $student?->shift ?? '') }}" class="{{ $inputClass('shift') }}">
        @error('shift')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="flex w-full flex-row gap-3">
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Foto</label>
        <div class="flex items-center gap-3">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-full border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800">
                @if ($student?->photoSrc())
                    <img id="student-photo-preview" src="{{ $student->photoSrc() }}" alt="Foto do aluno" class="h-full w-full object-cover">
                @else
                    <img id="student-photo-preview" src="" alt="" class="hidden h-full w-full object-cover">
                    <svg id="student-photo-placeholder" class="h-6 w-6 text-gray-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M12 12a4 4 0 100-8 4 4 0 000 8zM4 20a8 8 0 0116 0" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                @endif
            </div>
            <div class="min-w-0 flex-1">
                <input type="file"
                       name="photo"
                       id="student-photo-input"
                       accept="image/jpeg,image/png,image/webp,image/gif"
                       class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-brand-600 hover:file:bg-brand-100 dark:text-gray-300 dark:file:bg-brand-500/15 dark:file:text-brand-400 {{ $errors->has('photo') ? 'rounded-lg border border-error-500 p-1' : '' }}">
                <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">JPG, PNG, WEBP ou GIF até 2 MB.</p>
                @error('photo')
                    <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">PIN pessoal</label>
        <input type="password"
               name="personal_pin"
               value=""
               autocomplete="new-password"
               maxlength="20"
               placeholder="{{ $student?->personal_pin_hash ? 'Deixe em branco para manter o PIN atual' : 'Defina um PIN numérico' }}"
               class="{{ $inputClass('personal_pin') }}">
        @error('personal_pin')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Usado para autorizar compras no fiado.</p>
    </div>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const input = document.getElementById('student-photo-input');
                const preview = document.getElementById('student-photo-preview');
                const placeholder = document.getElementById('student-photo-placeholder');

                if (!input || !preview) {
                    return;
                }

                input.addEventListener('change', () => {
                    const file = input.files?.[0];
                    if (!file) {
                        return;
                    }

                    const url = URL.createObjectURL(file);
                    preview.src = url;
                    preview.classList.remove('hidden');
                    placeholder?.classList.add('hidden');
                });
            });
        </script>
    @endpush
@endonce

<div class="flex w-full flex-row gap-3">
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Pode comprar no crédito <span class="text-error-500">*</span>
        </label>
        <select name="can_buy_on_credit" class="{{ $inputClass('can_buy_on_credit') }}">
            <option value="1" @selected(old('can_buy_on_credit', (string) (int) ($student?->can_buy_on_credit ?? 0)) === '1')>Sim</option>
            <option value="0" @selected(old('can_buy_on_credit', (string) (int) ($student?->can_buy_on_credit ?? 0)) === '0')>Não</option>
        </select>
        @error('can_buy_on_credit')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Pode comprar no fiado <span class="text-error-500">*</span>
        </label>
        <select name="can_buy_on_tab" class="{{ $inputClass('can_buy_on_tab') }}">
            <option value="1" @selected(old('can_buy_on_tab', (string) (int) ($student?->can_buy_on_tab ?? 0)) === '1')>Sim</option>
            <option value="0" @selected(old('can_buy_on_tab', (string) (int) ($student?->can_buy_on_tab ?? 0)) === '0')>Não</option>
        </select>
        @error('can_buy_on_tab')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Acesso conveniência <span class="text-error-500">*</span>
        </label>
        <select name="convenience_access" class="{{ $inputClass('convenience_access') }}">
            <option value="1" @selected(old('convenience_access', (string) (int) ($student?->convenience_access ?? 0)) === '1')>Sim</option>
            <option value="0" @selected(old('convenience_access', (string) (int) ($student?->convenience_access ?? 0)) === '0')>Não</option>
        </select>
        @error('convenience_access')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Acesso lanches <span class="text-error-500">*</span>
        </label>
        <select name="snack_access" class="{{ $inputClass('snack_access') }}">
            <option value="1" @selected(old('snack_access', (string) (int) ($student?->snack_access ?? 1)) === '1')>Sim</option>
            <option value="0" @selected(old('snack_access', (string) (int) ($student?->snack_access ?? 1)) === '0')>Não</option>
        </select>
        @error('snack_access')
            <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
</div>
