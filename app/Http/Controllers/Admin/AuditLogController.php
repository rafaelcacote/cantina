<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = $request->integer('tenant_id') ?: null;
        $userId = $request->integer('user_id') ?: null;
        $action = $request->string('action')->toString();
        $entityType = $request->string('entity_type')->toString();
        $from = $request->string('from')->toString();
        $to = $request->string('to')->toString();

        $logs = AuditLog::query()
            ->with('user')
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->when($userId, fn ($query) => $query->where('user_id', $userId))
            ->when($action, fn ($query) => $query->where('action', $action))
            ->when($entityType, fn ($query) => $query->where('entity_type', $entityType))
            ->when($from, fn ($query) => $query->whereDate('created_at', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('created_at', '<=', $to))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('pages.admin.audit_logs.index', [
            'title' => 'Auditoria',
            'logs' => $logs,
            'tenants' => DB::table('tenants')->select(['id', 'name'])->orderBy('name')->get(),
            'tenantNames' => DB::table('tenants')->pluck('name', 'id'),
            'users' => User::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
            'actions' => AuditLog::query()->select('action')->distinct()->orderBy('action')->pluck('action'),
            'entityTypes' => AuditLog::query()->select('entity_type')->distinct()->orderBy('entity_type')->pluck('entity_type'),
            'tenantId' => $tenantId,
            'userId' => $userId,
            'action' => $action,
            'entityType' => $entityType,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function show(AuditLog $auditLog): View
    {
        $auditLog->load('user');

        return view('pages.admin.audit_logs.show', [
            'title' => 'Detalhes da Auditoria',
            'log' => $auditLog,
            'tenantName' => $auditLog->tenant_id
                ? DB::table('tenants')->where('id', $auditLog->tenant_id)->value('name')
                : null,
        ]);
    }
}
