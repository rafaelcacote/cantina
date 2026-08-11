<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $userId = $request->integer('user_id') ?: null;
        $action = $request->string('action')->toString();
        $entityType = $request->string('entity_type')->toString();
        $from = $request->string('from')->toString();
        $to = $request->string('to')->toString();

        $logs = AuditLog::query()
            ->with('user')
            ->where('tenant_id', $tenantId)
            ->when($userId, fn ($query) => $query->where('user_id', $userId))
            ->when($action, fn ($query) => $query->where('action', $action))
            ->when($entityType, fn ($query) => $query->where('entity_type', $entityType))
            ->when($from, fn ($query) => $query->whereDate('created_at', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('created_at', '<=', $to))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('pages.tenant.audit_logs.index', [
            'title' => 'Auditoria',
            'logs' => $logs,
            'users' => User::query()->where('tenant_id', $tenantId)->orderBy('name')->get(['id', 'name']),
            'actions' => AuditLog::query()
                ->where('tenant_id', $tenantId)
                ->select('action')
                ->distinct()
                ->orderBy('action')
                ->pluck('action'),
            'entityTypes' => AuditLog::query()
                ->where('tenant_id', $tenantId)
                ->select('entity_type')
                ->distinct()
                ->orderBy('entity_type')
                ->pluck('entity_type'),
            'userId' => $userId,
            'action' => $action,
            'entityType' => $entityType,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function show(Request $request, AuditLog $auditLog): View
    {
        if ((int) $auditLog->tenant_id !== (int) $request->user()->tenant_id) {
            abort(404);
        }

        $auditLog->load('user');

        return view('pages.tenant.audit_logs.show', [
            'title' => 'Detalhes da Auditoria',
            'log' => $auditLog,
        ]);
    }
}
