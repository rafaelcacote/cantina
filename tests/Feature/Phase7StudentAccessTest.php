<?php

use App\Models\ParentGuardian;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentInvitation;
use App\Models\StudentParent;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('responsável gera convite de acesso do filho', function () {
    $tenant = Tenant::factory()->create(['name' => 'Cantina Demo']);
    $school = School::factory()->create(['tenant_id' => $tenant->id]);
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'user_type' => 'parent',
    ]);
    $parent = ParentGuardian::factory()->create([
        'tenant_id' => $tenant->id,
        'user_id' => $user->id,
    ]);
    $student = Student::factory()->create([
        'tenant_id' => $tenant->id,
        'school_id' => $school->id,
        'name' => 'Lucas Silva',
        'user_id' => null,
    ]);
    StudentParent::factory()->create([
        'tenant_id' => $tenant->id,
        'parent_id' => $parent->id,
        'student_id' => $student->id,
    ]);

    $this->actingAs($user)
        ->get(route('parent.children.access', $student))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Parent/ChildAccess')
            ->where('share.has_access', false)
            ->where('child.name', 'Lucas Silva'));

    expect(StudentInvitation::query()->where('student_id', $student->id)->count())->toBe(1);
});

it('aluno cria acesso a partir do convite do responsável', function () {
    $tenant = Tenant::factory()->create();
    $school = School::factory()->create(['tenant_id' => $tenant->id]);
    $parent = ParentGuardian::factory()->create(['tenant_id' => $tenant->id]);
    $student = Student::factory()->create([
        'tenant_id' => $tenant->id,
        'school_id' => $school->id,
        'name' => 'Clara Silva',
        'user_id' => null,
    ]);
    $invitation = StudentInvitation::factory()->create([
        'tenant_id' => $tenant->id,
        'student_id' => $student->id,
        'parent_id' => $parent->id,
        'token' => 'student-access-token',
        'expires_at' => now()->addDays(7),
        'used_at' => null,
    ]);

    $this->get(route('student-invitations.accept', $invitation->token))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Invite/StudentAccept')
            ->where('studentName', 'Clara Silva'));

    $this->post(route('student-invitations.accept.store', $invitation->token), [
        'email' => 'clara.aluno@teste.local',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('student.dashboard'));

    expect(User::query()->where('email', 'clara.aluno@teste.local')->value('user_type'))->toBe('student')
        ->and($student->fresh()->user_id)->not->toBeNull()
        ->and($invitation->fresh()->used_at)->not->toBeNull()
        ->and(auth()->user()?->email)->toBe('clara.aluno@teste.local');
});
