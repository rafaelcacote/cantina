<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentWallet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentWalletController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = $request->integer('tenant_id') ?: null;
        $studentId = $request->integer('student_id') ?: null;

        $wallets = StudentWallet::query()
            ->with('student')
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->when($studentId, fn ($query) => $query->where('student_id', $studentId))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.admin.student_wallets.index', [
            'title' => 'Carteiras',
            'wallets' => $wallets,
            'tenants' => DB::table('tenants')->select(['id', 'name'])->orderBy('name')->get(),
            'tenantNames' => DB::table('tenants')->pluck('name', 'id'),
            'students' => Student::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
            'tenantId' => $tenantId,
            'studentId' => $studentId,
        ]);
    }

    public function create(): View
    {
        return view('pages.admin.student_wallets.create', [
            'title' => 'Nova Carteira',
            'tenants' => DB::table('tenants')->select(['id', 'name'])->orderBy('name')->get(),
            'students' => Student::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $validated['allow_negative_balance'] = $request->boolean('allow_negative_balance');

        $wallet = StudentWallet::create($validated);

        return redirect()
            ->route('admin.student-wallets.show', $wallet)
            ->with('success', 'Carteira criada com sucesso.');
    }

    public function show(StudentWallet $studentWallet): View
    {
        $studentWallet->load(['student', 'transactions.creator']);

        return view('pages.admin.student_wallets.show', [
            'title' => 'Detalhes da Carteira',
            'wallet' => $studentWallet,
            'tenantName' => DB::table('tenants')->where('id', $studentWallet->tenant_id)->value('name'),
        ]);
    }

    public function edit(StudentWallet $studentWallet): View
    {
        return view('pages.admin.student_wallets.edit', [
            'title' => 'Editar Carteira',
            'wallet' => $studentWallet,
            'tenants' => DB::table('tenants')->select(['id', 'name'])->orderBy('name')->get(),
            'students' => Student::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, StudentWallet $studentWallet): RedirectResponse
    {
        $validated = $request->validate($this->rules($studentWallet));
        $validated['allow_negative_balance'] = $request->boolean('allow_negative_balance');
        $studentWallet->update($validated);

        return redirect()
            ->route('admin.student-wallets.show', $studentWallet)
            ->with('success', 'Carteira atualizada com sucesso.');
    }

    private function rules(?StudentWallet $wallet = null): array
    {
        return [
            'tenant_id' => ['required', 'integer', Rule::exists('tenants', 'id')],
            'student_id' => [
                'required',
                'integer',
                Rule::exists('students', 'id')->where(fn ($query) => $query->where('tenant_id', request('tenant_id'))),
                Rule::unique('student_wallets', 'student_id')
                    ->ignore($wallet?->id)
                    ->where(fn ($query) => $query->where('tenant_id', request('tenant_id'))),
            ],
            'balance' => ['required', 'numeric'],
            'credit_limit' => ['required', 'numeric', 'min:0'],
            'allow_negative_balance' => ['nullable', 'boolean'],
        ];
    }
}
