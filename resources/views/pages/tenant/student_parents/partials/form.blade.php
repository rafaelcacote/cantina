@php
    $selectedStudent = old('student_id', $studentParent?->student_id ?? ($selectedStudentId ?? null));
    $selectedParent = old('parent_id', $studentParent?->parent_id ?? ($selectedParentId ?? null));
    $lockParent = filled($selectedParentId ?? null) && ! $studentParent;
    $lockStudent = filled($selectedStudentId ?? null) && ! $studentParent;
    $relationshipOptions = ['Pai', 'Mãe', 'Tio', 'Tia', 'Avó', 'Avô'];
    $selectedRelationship = old('relationship_type', $studentParent?->relationship_type);
@endphp

@if ($lockStudent)
    <input type="hidden" name="_context" value="student">
@elseif ($lockParent)
    <input type="hidden" name="_context" value="parent">
@endif

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Aluno *</label>
        @if ($lockStudent)
            <input type="hidden" name="student_id" value="{{ $selectedStudent }}">
            <select disabled
                    class="h-11 w-full cursor-not-allowed rounded-lg border border-gray-300 bg-gray-50 px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90">
                @foreach ($students as $student)
                    <option value="{{ $student->id }}" @selected((string) $selectedStudent === (string) $student->id)>{{ $student->name }}</option>
                @endforeach
            </select>
        @else
            <select id="student_id" name="student_id" required
                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
                <option value="">Selecione</option>
                @foreach ($students as $student)
                    <option value="{{ $student->id }}" @selected((string) $selectedStudent === (string) $student->id)>{{ $student->name }}</option>
                @endforeach
            </select>
        @endif
        @error('student_id') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Responsável *</label>
        @if ($lockParent)
            <input type="hidden" name="parent_id" value="{{ $selectedParent }}">
            <select disabled
                    class="h-11 w-full cursor-not-allowed rounded-lg border border-gray-300 bg-gray-50 px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90">
                @foreach ($parents as $parent)
                    <option value="{{ $parent->id }}" @selected((string) $selectedParent === (string) $parent->id)>{{ $parent->name }}</option>
                @endforeach
            </select>
        @else
            <select id="parent_id" name="parent_id" required
                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
                <option value="">Selecione</option>
                @foreach ($parents as $parent)
                    <option value="{{ $parent->id }}" @selected((string) $selectedParent === (string) $parent->id)>{{ $parent->name }}</option>
                @endforeach
            </select>
        @endif
        @error('parent_id') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
    </div>
</div>

<div class="flex flex-col gap-5 lg:flex-row">
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de relação</label>
        <select name="relationship_type"
                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
            <option value="">Selecione</option>
            @foreach ($relationshipOptions as $option)
                <option value="{{ $option }}" @selected((string) $selectedRelationship === (string) $option)>{{ $option }}</option>
            @endforeach
            @if (filled($selectedRelationship) && ! in_array($selectedRelationship, $relationshipOptions, true))
                <option value="{{ $selectedRelationship }}" selected>{{ $selectedRelationship }}</option>
            @endif
        </select>
        @error('relationship_type') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
    </div>
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Principal *</label>
        <select name="is_primary"
                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
            <option value="1" @selected(old('is_primary', (string) (int) ($studentParent?->is_primary ?? 0)) === '1')>Sim</option>
            <option value="0" @selected(old('is_primary', (string) (int) ($studentParent?->is_primary ?? 0)) === '0')>Não</option>
        </select>
    </div>
    <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Responsável financeiro *</label>
        <select name="financial_responsible"
                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:text-white/90">
            <option value="1" @selected(old('financial_responsible', (string) (int) ($studentParent?->financial_responsible ?? 0)) === '1')>Sim</option>
            <option value="0" @selected(old('financial_responsible', (string) (int) ($studentParent?->financial_responsible ?? 0)) === '0')>Não</option>
        </select>
    </div>
</div>
