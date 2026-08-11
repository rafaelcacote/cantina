<?php

namespace App\Services;

use App\Models\ParentGuardian;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentParent;
use App\Models\TenantInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ParentRegistrationService
{
    public const RELATIONSHIP_TYPES = [
        'Pai',
        'Mãe',
        'Tio',
        'Tia',
        'Avó',
        'Avô',
        'Responsável',
    ];

    public const SHIFTS = [
        'Manhã',
        'Tarde',
        'Integral',
        'Noite',
    ];

    public function __construct(private readonly WalletService $walletService) {}

    /**
     * @param  array{name: string, email: string, password: string, phone?: ?string, cpf?: ?string}  $parentData
     * @param  list<array<string, mixed>>  $children
     */
    public function registerFromInvitation(TenantInvitation $invitation, array $parentData, array $children): User
    {
        if ($invitation->type !== 'parent_registration') {
            throw ValidationException::withMessages([
                'email' => 'Este convite não é para cadastro de responsável.',
            ]);
        }

        if (! $invitation->isUsable()) {
            throw ValidationException::withMessages([
                'email' => $invitation->unusableReason(),
            ]);
        }

        $children = $this->assertChildrenBelongToTenant((int) $invitation->tenant_id, $children);

        return DB::transaction(function () use ($invitation, $parentData, $children) {
            $user = User::query()->create([
                'tenant_id' => $invitation->tenant_id,
                'name' => $parentData['name'],
                'email' => $parentData['email'],
                'phone' => $parentData['phone'] ?? null,
                'cpf' => $parentData['cpf'] ?? null,
                'password' => $parentData['password'],
                'user_type' => 'parent',
                'active' => true,
            ]);

            $parent = ParentGuardian::query()->create([
                'tenant_id' => $invitation->tenant_id,
                'user_id' => $user->id,
                'name' => $parentData['name'],
                'email' => $parentData['email'],
                'phone' => $parentData['phone'] ?? null,
                'cpf' => $parentData['cpf'] ?? null,
            ]);

            foreach ($children as $child) {
                $this->createLinkedStudent($parent, $child);
            }

            $invitation->markUsed();

            return $user;
        });
    }

    /**
     * @param  array<string, mixed>  $child
     */
    public function addChild(ParentGuardian $parent, array $child): Student
    {
        $children = $this->assertChildrenBelongToTenant((int) $parent->tenant_id, [$child]);

        return DB::transaction(fn () => $this->createLinkedStudent($parent, $children[0]));
    }

    /**
     * @param  array<string, mixed>  $child
     */
    private function createLinkedStudent(ParentGuardian $parent, array $child): Student
    {
        $student = Student::query()->create([
            'tenant_id' => $parent->tenant_id,
            'school_id' => $child['school_id'],
            'name' => $child['name'],
            'birth_date' => $child['birth_date'] ?? null,
            'grade' => $child['grade'] ?? null,
            'classroom' => $child['classroom'] ?? null,
            'shift' => $child['shift'] ?? null,
            'status' => 'pending',
            'snack_access' => true,
            'convenience_access' => false,
            'can_buy_on_credit' => false,
            'can_buy_on_tab' => false,
        ]);

        $this->walletService->ensureForStudent($student);

        StudentParent::query()->create([
            'tenant_id' => $parent->tenant_id,
            'student_id' => $student->id,
            'parent_id' => $parent->id,
            'relationship_type' => $child['relationship_type'] ?? 'Responsável',
            'is_primary' => true,
            'financial_responsible' => true,
        ]);

        return $student;
    }

    /**
     * @param  list<array<string, mixed>>  $children
     * @return list<array<string, mixed>>
     */
    private function assertChildrenBelongToTenant(int $tenantId, array $children): array
    {
        if ($children === []) {
            throw ValidationException::withMessages([
                'children' => 'Cadastre pelo menos um filho para continuar.',
            ]);
        }

        $schoolIds = collect($children)->pluck('school_id')->filter()->unique()->all();

        $validSchoolIds = School::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('id', $schoolIds)
            ->pluck('id')
            ->all();

        foreach ($children as $index => $child) {
            if (! in_array((int) ($child['school_id'] ?? 0), $validSchoolIds, true)) {
                throw ValidationException::withMessages([
                    "children.{$index}.school_id" => 'Selecione uma escola válida da cantina.',
                ]);
            }
        }

        return $children;
    }
}
