<?php

namespace App\Services;

use App\Models\ParentGuardian;
use App\Models\School;
use App\Models\Student;
use App\Models\TenantInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdultConsumerService
{
    public const PROFILE_STUDENT = 'student';

    public const PROFILE_ADULT = 'adult';

    public function __construct(private readonly WalletService $walletService) {}

    /**
     * @param  array{name: string, email: string, password: string, phone?: ?string, cpf?: ?string, school_id: int}  $data
     */
    public function registerRequesterFromInvitation(TenantInvitation $invitation, array $data): User
    {
        if ($invitation->type !== 'requester_registration') {
            throw ValidationException::withMessages([
                'email' => 'Este convite não é para cadastro de solicitante.',
            ]);
        }

        if (! $invitation->isUsable()) {
            throw ValidationException::withMessages([
                'email' => $invitation->unusableReason(),
            ]);
        }

        $school = School::query()
            ->where('tenant_id', $invitation->tenant_id)
            ->whereKey($data['school_id'])
            ->where('active', true)
            ->first();

        if (! $school) {
            throw ValidationException::withMessages([
                'school_id' => 'Escola inválida para este convite.',
            ]);
        }

        return DB::transaction(function () use ($invitation, $data, $school) {
            $user = User::query()->create([
                'tenant_id' => $invitation->tenant_id,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'cpf' => $data['cpf'] ?? null,
                'password' => $data['password'],
                'user_type' => 'requester',
                'active' => true,
            ]);

            $this->createAdultStudent(
                tenantId: (int) $invitation->tenant_id,
                schoolId: (int) $school->id,
                userId: (int) $user->id,
                name: $data['name'],
                status: 'active',
            );

            $invitation->markUsed();

            return $user;
        });
    }

    public function ensureForParent(ParentGuardian $parent, int $schoolId): Student
    {
        $parent->loadMissing('selfStudent');

        if ($parent->selfStudent) {
            if ((int) $parent->selfStudent->school_id !== $schoolId) {
                $parent->selfStudent->update(['school_id' => $schoolId]);
            }

            return $parent->selfStudent->fresh(['school', 'wallet']);
        }

        $school = School::query()
            ->where('tenant_id', $parent->tenant_id)
            ->whereKey($schoolId)
            ->first();

        if (! $school) {
            throw ValidationException::withMessages([
                'school_id' => 'Escola inválida.',
            ]);
        }

        return DB::transaction(function () use ($parent, $school) {
            $student = $this->createAdultStudent(
                tenantId: (int) $parent->tenant_id,
                schoolId: (int) $school->id,
                userId: null,
                name: $parent->name,
                status: 'active',
            );

            $parent->update(['self_student_id' => $student->id]);

            return $student->fresh(['school', 'wallet']);
        });
    }

    private function createAdultStudent(
        int $tenantId,
        int $schoolId,
        ?int $userId,
        string $name,
        string $status = 'active',
    ): Student {
        $student = Student::query()->create([
            'tenant_id' => $tenantId,
            'school_id' => $schoolId,
            'user_id' => $userId,
            'profile_kind' => self::PROFILE_ADULT,
            'name' => $name,
            'status' => $status,
            'snack_access' => true,
            'convenience_access' => true,
            'can_buy_on_credit' => false,
            'can_buy_on_tab' => false,
        ]);

        $this->walletService->ensureForStudent($student);

        return $student;
    }
}
