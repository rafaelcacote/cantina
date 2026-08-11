<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentWallet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentWalletController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $studentId = $request->integer('student_id') ?: null;

        $wallets = StudentWallet::query()
            ->with('student')
            ->where('tenant_id', $tenantId)
            ->when($studentId, fn ($query) => $query->where('student_id', $studentId))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.tenant.student_wallets.index', [
            'title' => 'Carteiras',
            'wallets' => $wallets,
            'students' => Student::query()
                ->where('tenant_id', $tenantId)
                ->orderBy('name')
                ->get(['id', 'name']),
            'studentId' => $studentId,
        ]);
    }

    public function create(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;

        return view('pages.tenant.student_wallets.create', [
            'title' => 'Nova Carteira',
            'students' => Student::query()
                ->where('tenant_id', $tenantId)
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = $request->user()->tenant_id;
        $validated = $this->validateWallet($request, $tenantId);
        $validated['tenant_id'] = $tenantId;

        $wallet = StudentWallet::query()->create($validated);

        return redirect()
            ->route('tenant.student-wallets.show', $wallet)
            ->with('success', 'Carteira criada com sucesso.');
    }

    public function show(Request $request, StudentWallet $studentWallet): View
    {
        $this->ensureWalletBelongsToTenant($request, $studentWallet);
        $studentWallet->load(['student', 'transactions.creator']);

        return view('pages.tenant.student_wallets.show', [
            'title' => 'Detalhes da Carteira',
            'wallet' => $studentWallet,
            'transactionTypes' => [
                'credit' => 'Crédito',
                'debit' => 'Débito',
                'refund' => 'Estorno',
                'adjustment' => 'Ajuste',
            ],
        ]);
    }

    public function edit(Request $request, StudentWallet $studentWallet): View
    {
        $this->ensureWalletBelongsToTenant($request, $studentWallet);
        $tenantId = $request->user()->tenant_id;

        return view('pages.tenant.student_wallets.edit', [
            'title' => 'Editar Carteira',
            'wallet' => $studentWallet,
            'students' => Student::query()
                ->where('tenant_id', $tenantId)
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, StudentWallet $studentWallet): RedirectResponse
    {
        $this->ensureWalletBelongsToTenant($request, $studentWallet);
        $tenantId = $request->user()->tenant_id;
        $validated = $this->validateWallet($request, $tenantId, $studentWallet);
        $studentWallet->update($validated);

        return redirect()
            ->route('tenant.student-wallets.show', $studentWallet)
            ->with('success', 'Carteira atualizada com sucesso.');
    }

    public function destroy(Request $request, StudentWallet $studentWallet): RedirectResponse
    {
        $this->ensureWalletBelongsToTenant($request, $studentWallet);
        $studentWallet->delete();

        return redirect()
            ->route('tenant.student-wallets.index')
            ->with('success', 'Carteira excluída com sucesso.');
    }

    private function validateWallet(Request $request, int $tenantId, ?StudentWallet $wallet = null): array
    {
        return $request->validate([
            'student_id' => [
                'required',
                'integer',
                Rule::exists('students', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
                Rule::unique('student_wallets', 'student_id')
                    ->ignore($wallet?->id)
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'balance' => ['required', 'numeric'],
            'credit_limit' => ['required', 'numeric', 'min:0'],
            'allow_negative_balance' => ['required', 'boolean'],
        ]);
    }

    private function ensureWalletBelongsToTenant(Request $request, StudentWallet $wallet): void
    {
        if ((int) $wallet->tenant_id !== (int) $request->user()->tenant_id) {
            abort(404);
        }
    }
}
