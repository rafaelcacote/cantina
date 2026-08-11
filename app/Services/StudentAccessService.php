<?php

namespace App\Services;

use App\Models\ParentGuardian;
use App\Models\Student;
use App\Models\StudentInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StudentAccessService
{
    public function invitationFor(ParentGuardian $parent, Student $student): StudentInvitation
    {
        if ($student->user_id) {
            throw ValidationException::withMessages([
                'access' => 'Este aluno já possui acesso ao aplicativo.',
            ]);
        }

        $existing = StudentInvitation::query()
            ->where('tenant_id', $parent->tenant_id)
            ->where('student_id', $student->id)
            ->whereNull('used_at')
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->latest()
            ->first();

        if ($existing) {
            return $existing;
        }

        return StudentInvitation::query()->create([
            'tenant_id' => $parent->tenant_id,
            'student_id' => $student->id,
            'parent_id' => $parent->id,
            'token' => Str::random(40),
            'expires_at' => now()->addDays(14),
        ]);
    }

    /**
     * @param  array{email: string, password: string}  $data
     */
    public function accept(StudentInvitation $invitation, array $data): User
    {
        if (! $invitation->isUsable()) {
            throw ValidationException::withMessages([
                'email' => $invitation->unusableReason(),
            ]);
        }

        return DB::transaction(function () use ($invitation, $data) {
            $student = Student::query()->whereKey($invitation->student_id)->lockForUpdate()->firstOrFail();

            if ($student->user_id) {
                throw ValidationException::withMessages([
                    'email' => 'Este aluno já possui acesso ao aplicativo.',
                ]);
            }

            $user = User::query()->create([
                'tenant_id' => $invitation->tenant_id,
                'name' => $student->name,
                'email' => $data['email'],
                'password' => $data['password'],
                'user_type' => 'student',
                'active' => true,
            ]);

            $student->update(['user_id' => $user->id]);
            $invitation->update(['used_at' => now()]);

            return $user;
        });
    }

    public function sharePayload(Student $student, ?StudentInvitation $invitation, string $tenantName): array
    {
        if ($student->user_id) {
            $loginUrl = route('signin');
            $email = $student->user?->email;
            $text = "Oi, {$student->name}! Seu acesso à cantina {$tenantName} já está pronto. Entre por este link: {$loginUrl}"
                .($email ? "\nE-mail: {$email}" : '');

            return [
                'has_access' => true,
                'url' => $loginUrl,
                'email' => $email,
                'whatsapp_url' => 'https://wa.me/?text='.rawurlencode($text),
                'share_text' => $text,
                'expires_at' => null,
            ];
        }

        $url = $invitation?->acceptUrl() ?? route('signin');
        $text = "Oi, {$student->name}! Seu responsável liberou o acesso à cantina {$tenantName}. Abra o link e crie sua senha:\n{$url}";

        return [
            'has_access' => false,
            'url' => $url,
            'email' => null,
            'whatsapp_url' => 'https://wa.me/?text='.rawurlencode($text),
            'share_text' => $text,
            'expires_at' => $invitation?->expires_at?->format('d/m/Y'),
        ];
    }
}
