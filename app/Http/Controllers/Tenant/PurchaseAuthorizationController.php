<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\PurchaseAuthorization;
use App\Models\School;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseAuthorizationController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $schoolId = $request->integer('school_id') ?: null;
        $studentId = $request->integer('student_id') ?: null;
        $success = $request->string('success')->toString();
        $from = $request->string('from')->toString();
        $to = $request->string('to')->toString();

        $authorizations = PurchaseAuthorization::query()
            ->with(['school', 'student', 'order', 'tabEntry', 'creator'])
            ->where('tenant_id', $tenantId)
            ->when($schoolId, fn ($query) => $query->where('school_id', $schoolId))
            ->when($studentId, fn ($query) => $query->where('student_id', $studentId))
            ->when($success !== '', fn ($query) => $query->where('success', $success === '1'))
            ->when($from, fn ($query) => $query->whereDate('created_at', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('created_at', '<=', $to))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('pages.tenant.purchase_authorizations.index', [
            'title' => 'Autorizações PIN',
            'authorizations' => $authorizations,
            'schools' => School::query()->where('tenant_id', $tenantId)->orderBy('name')->get(['id', 'name']),
            'students' => Student::query()->where('tenant_id', $tenantId)->orderBy('name')->get(['id', 'name']),
            'authorizationTypes' => $this->authorizationTypes(),
            'schoolId' => $schoolId,
            'studentId' => $studentId,
            'success' => $success,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function show(Request $request, PurchaseAuthorization $purchaseAuthorization): View
    {
        if ((int) $purchaseAuthorization->tenant_id !== (int) $request->user()->tenant_id) {
            abort(404);
        }

        $purchaseAuthorization->load(['school', 'student', 'order', 'tabEntry', 'creator']);

        return view('pages.tenant.purchase_authorizations.show', [
            'title' => 'Detalhes da Autorização',
            'authorization' => $purchaseAuthorization,
            'authorizationTypes' => $this->authorizationTypes(),
        ]);
    }

    private function authorizationTypes(): array
    {
        return [
            'tab_confirmation' => 'Confirmação de Fiado',
            'wallet_topup' => 'Recarga de Carteira',
            'product_release' => 'Liberação de Produto',
            'purchase_approval' => 'Aprovação de Compra',
        ];
    }
}
