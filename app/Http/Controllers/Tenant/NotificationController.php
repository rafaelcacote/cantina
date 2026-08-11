<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $studentId = $request->integer('student_id') ?: null;
        $type = $request->string('notification_type')->toString();
        $readStatus = $request->string('read_status')->toString();

        $notifications = Notification::query()
            ->with(['user', 'student'])
            ->where('tenant_id', $tenantId)
            ->when($studentId, fn ($query) => $query->where('student_id', $studentId))
            ->when($type, fn ($query) => $query->where('notification_type', $type))
            ->when($readStatus === 'read', fn ($query) => $query->whereNotNull('read_at'))
            ->when($readStatus === 'unread', fn ($query) => $query->whereNull('read_at'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('pages.tenant.notifications.index', [
            'title' => 'Notificações',
            'notifications' => $notifications,
            'students' => Student::query()->where('tenant_id', $tenantId)->orderBy('name')->get(['id', 'name']),
            'types' => Notification::query()
                ->where('tenant_id', $tenantId)
                ->select('notification_type')
                ->distinct()
                ->orderBy('notification_type')
                ->pluck('notification_type'),
            'studentId' => $studentId,
            'type' => $type,
            'readStatus' => $readStatus,
        ]);
    }

    public function show(Request $request, Notification $notification): View
    {
        if ((int) $notification->tenant_id !== (int) $request->user()->tenant_id) {
            abort(404);
        }

        $notification->load(['user', 'student']);

        return view('pages.tenant.notifications.show', [
            'title' => 'Detalhes da Notificação',
            'notification' => $notification,
        ]);
    }

    public function markAsRead(Request $request, Notification $notification): RedirectResponse
    {
        if ((int) $notification->tenant_id !== (int) $request->user()->tenant_id) {
            abort(404);
        }

        $notification->update(['read_at' => now()]);

        return redirect()
            ->route('tenant.notifications.show', $notification)
            ->with('success', 'Notificação marcada como lida.');
    }

    public function markAsUnread(Request $request, Notification $notification): RedirectResponse
    {
        if ((int) $notification->tenant_id !== (int) $request->user()->tenant_id) {
            abort(404);
        }

        $notification->update(['read_at' => null]);

        return redirect()
            ->route('tenant.notifications.show', $notification)
            ->with('success', 'Notificação marcada como não lida.');
    }
}
