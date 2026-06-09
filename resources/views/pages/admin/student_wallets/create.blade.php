@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">Nova Carteira</h1>
            <a href="{{ route('admin.student-wallets.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Voltar</a>
        </div>
        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800/40 dark:bg-red-900/20 dark:text-red-300">Verifique os campos do formulário.</div>
        @endif
        <form method="POST" action="{{ route('admin.student-wallets.store') }}" class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            @csrf
            @include('pages.admin.student_wallets.partials.form')
            <div class="mt-8 flex gap-3">
                <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Salvar</button>
                <a href="{{ route('admin.student-wallets.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Cancelar</a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const tenant = document.getElementById('tenant_id');
            const student = document.getElementById('student_id');
            if (!tenant || !student) return;
            const syncStudents = () => {
                const tenantId = tenant.value;
                [...student.options].forEach((opt, idx) => {
                    if (idx === 0) return;
                    const match = !!tenantId && opt.dataset.tenantId === tenantId;
                    opt.hidden = !match;
                    opt.disabled = !match;
                });
                if (!tenantId || student.selectedOptions[0]?.disabled) student.value = '';
            };
            tenant.addEventListener('change', syncStudents);
            syncStudents();
        })();
    </script>
@endpush
