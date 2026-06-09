<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseAuthorization;
use App\Models\School;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchaseAuthorizationController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = $request->integer('tenant_id') ?: null;
        $schoolId = $request->integer('school_id') ?: null;
        $studentId = $request->integer('student_id') ?: null;
        $success = $request->string('success')->toString();
        $from = $request->string('from')->toString();
        $to = $request->string('to')->toString();

        $authorizations = PurchaseAuthorization::query()
            ->with(['school', 'student', 'order', 'tabEntry', 'creator'])
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->when($schoolId, fn ($query) => $query->where('school_id', $schoolId))
            ->when($studentId, fn ($query) => $query->where('student_id', $studentId))
            ->when($success !== '', fn ($query) => $query->where('success', $success === '1'))
            ->when($from, fn ($query) => $query->whereDate('created_at', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('created_at', '<=', $to))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('pages.admin.purchase_authorizations.index', [
            'title' => 'Autorizações PIN',
            'authorizations' => $authorizations,
            'tenants' => DB::table('tenants')->select(['id', 'name'])->orderBy('name')->get(),
            'tenantNames' => DB::table('tenants')->pluck('name', 'id'),
            'schools' => School::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
            'students' => Student::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
            'authorizationTypes' => $this->authorizationTypes(),
            'tenantId' => $tenantId,
            'schoolId' => $schoolId,
            'studentId' => $studentId,
            'success' => $success,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function show(PurchaseAuthorization $purchaseAuthorization): View
    {
        $purchaseAuthorization->load(['school', 'student', 'order', 'tabEntry', 'creator']);

        return view('pages.admin.purchase_authorizations.show', [
            'title' => 'Detalhes da Autorização',
            'authorization' => $purchaseAuthorization,
            'tenantName' => DB::table('tenants')->where('id', $purchaseAuthorization->tenant_id)->value('name'),
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
