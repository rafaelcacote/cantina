<?php

use App\Models\ParentGuardian;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentParent;
use App\Models\StudentWallet;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('redireciona parent para portal no login', function () {
    $tenant = Tenant::factory()->create();
    $parent = User::factory()->create([
        'tenant_id' => $tenant->id,
        'user_type' => 'parent',
        'email' => 'parent-phase7@test.local',
        'password' => Hash::make('password'),
    ]);

    $this->post(route('signin.store'), [
        'email' => $parent->email,
        'password' => 'password',
    ])->assertRedirect(route('parent.dashboard'));
});

it('redireciona student para portal no login', function () {
    $tenant = Tenant::factory()->create();
    $student = User::factory()->create([
        'tenant_id' => $tenant->id,
        'user_type' => 'student',
        'email' => 'student-phase7@test.local',
        'password' => Hash::make('password'),
    ]);

    $this->post(route('signin.store'), [
        'email' => $student->email,
        'password' => 'password',
    ])->assertRedirect(route('student.dashboard'));
});

it('parent acessa dashboard inertia', function () {
    $tenant = Tenant::factory()->create();
    $school = School::factory()->create(['tenant_id' => $tenant->id]);
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'user_type' => 'parent',
        'name' => 'Maria Responsavel',
    ]);
    $parent = ParentGuardian::factory()->create([
        'tenant_id' => $tenant->id,
        'user_id' => $user->id,
        'name' => $user->name,
    ]);
    $student = Student::factory()->create([
        'tenant_id' => $tenant->id,
        'school_id' => $school->id,
        'name' => 'Joao Aluno',
    ]);
    StudentWallet::factory()->create([
        'tenant_id' => $tenant->id,
        'student_id' => $student->id,
        'balance' => 42.5,
    ]);
    StudentParent::factory()->create([
        'tenant_id' => $tenant->id,
        'parent_id' => $parent->id,
        'student_id' => $student->id,
    ]);

    $this->actingAs($user)
        ->get(route('parent.dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Parent/Dashboard')
            ->where('greeting', 'Maria')
            ->has('children', 1)
            ->where('metrics.total_balance', 42.5));
});

it('student acessa dashboard inertia', function () {
    $tenant = Tenant::factory()->create();
    $school = School::factory()->create(['tenant_id' => $tenant->id]);
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'user_type' => 'student',
        'name' => 'Pedro Aluno',
    ]);
    $student = Student::factory()->create([
        'tenant_id' => $tenant->id,
        'school_id' => $school->id,
        'user_id' => $user->id,
        'name' => 'Pedro Aluno',
    ]);
    StudentWallet::factory()->create([
        'tenant_id' => $tenant->id,
        'student_id' => $student->id,
        'balance' => 15,
    ]);

    $this->actingAs($user)
        ->get(route('student.dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Student/Dashboard')
            ->where('greeting', 'Pedro Aluno')
            ->where('student.balance', 15));
});

it('bloqueia parent no painel student', function () {
    $tenant = Tenant::factory()->create();
    $parent = User::factory()->create([
        'tenant_id' => $tenant->id,
        'user_type' => 'parent',
    ]);

    $this->actingAs($parent)
        ->get(route('student.dashboard'))
        ->assertForbidden();
});
