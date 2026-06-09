<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = $request->integer('tenant_id') ?: null;
        $userId = $request->integer('user_id') ?: null;
        $studentId = $request->integer('student_id') ?: null;
        $type = $request->string('notification_type')->toString();
        $readStatus = $request->string('read_status')->toString();

        $notifications = Notification::query()
            ->with(['user', 'student'])
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->when($userId, fn ($query) => $query->where('user_id', $userId))
            ->when($studentId, fn ($query) => $query->where('student_id', $studentId))
            ->when($type, fn ($query) => $query->where('notification_type', $type))
            ->when($readStatus === 'read', fn ($query) => $query->whereNotNull('read_at'))
            ->when($readStatus === 'unread', fn ($query) => $query->whereNull('read_at'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('pages.admin.notifications.index', [
            'title' => 'Notificações',
            'notifications' => $notifications,
            'tenants' => DB::table('tenants')->select(['id', 'name'])->orderBy('name')->get(),
            'tenantNames' => DB::table('tenants')->pluck('name', 'id'),
            'users' => User::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
            'students' => Student::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
            'types' => Notification::query()
                ->select('notification_type')
                ->distinct()
                ->orderBy('notification_type')
                ->pluck('notification_type'),
            'tenantId' => $tenantId,
            'userId' => $userId,
            'studentId' => $studentId,
            'type' => $type,
            'readStatus' => $readStatus,
        ]);
    }

    public function show(Notification $notification): View
    {
        $notification->load(['user', 'student']);

        return view('pages.admin.notifications.show', [
            'title' => 'Detalhes da Notificação',
            'notification' => $notification,
            'tenantName' => DB::table('tenants')->where('id', $notification->tenant_id)->value('name'),
        ]);
    }

    public function markAsRead(Notification $notification): RedirectResponse
    {
        $notification->update(['read_at' => now()]);

        return redirect()
            ->route('admin.notifications.show', $notification)
            ->with('success', 'Notificação marcada como lida.');
    }

    public function markAsUnread(Notification $notification): RedirectResponse
    {
        $notification->update(['read_at' => null]);

        return redirect()
            ->route('admin.notifications.show', $notification)
            ->with('success', 'Notificação marcada como não lida.');
    }
}
