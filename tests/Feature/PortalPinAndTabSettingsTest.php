<?php

use App\Models\ParentalControl;
use App\Models\ParentGuardian;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentParent;
use App\Models\StudentTab;
use App\Models\Tenant;
use App\Models\User;
use App\Services\PinService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function parentChildSetup(): array
{
    $tenant = Tenant::factory()->create();
    $school = School::factory()->create(['tenant_id' => $tenant->id, 'active' => true]);
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
        'can_buy_on_tab' => false,
    ]);
    StudentParent::factory()->create([
        'tenant_id' => $tenant->id,
        'parent_id' => $parent->id,
        'student_id' => $student->id,
        'relationship_type' => 'Mãe',
    ]);

    return compact('tenant', 'school', 'user', 'parent', 'student');
}

it('responsável edita dados, libera fiado e define o pin do aluno', function () {
    ['user' => $user, 'student' => $student, 'school' => $school] = parentChildSetup();

    $this->actingAs($user)
        ->get(route('parent.children.edit', $student))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Parent/ChildEdit')
            ->where('child.name', 'Lucas Silva')
            ->where('has_pin', false)
            ->where('form.can_buy_on_tab', false));

    $this->actingAs($user)
        ->put(route('parent.children.update', $student), [
            'name' => 'Lucas Silva Junior',
            'school_id' => $school->id,
            'birth_date' => '2014-05-20',
            'grade' => '5o Ano',
            'classroom' => 'B',
            'shift' => 'Manhã',
            'relationship_type' => 'Pai',
            'can_buy_on_tab' => 1,
            'personal_pin' => '2580',
            'personal_pin_confirmation' => '2580',
        ])
        ->assertRedirect(route('parent.children.show', $student));

    $student->refresh();

    expect($student->name)->toBe('Lucas Silva Junior')
        ->and($student->can_buy_on_tab)->toBeTrue()
        ->and($student->grade)->toBe('5o Ano')
        ->and(app(PinService::class)->reveal($student))->toBe('2580')
        ->and(app(PinService::class)->verify($student, '2580'))->toBeTrue();

    expect(StudentTab::query()->where('student_id', $student->id)->where('active', true)->exists())->toBeTrue();

    $this->actingAs($user)
        ->get(route('parent.children.edit', $student))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('pin', '2580')
            ->where('has_pin', true)
            ->where('form.can_buy_on_tab', true));
});

it('responsável precisa definir pin ao liberar fiado', function () {
    ['user' => $user, 'student' => $student, 'school' => $school] = parentChildSetup();

    $this->actingAs($user)
        ->from(route('parent.children.edit', $student))
        ->put(route('parent.children.update', $student), [
            'name' => $student->name,
            'school_id' => $school->id,
            'can_buy_on_tab' => 1,
        ])
        ->assertRedirect(route('parent.children.edit', $student))
        ->assertSessionHasErrors('personal_pin');

    expect($student->fresh()->can_buy_on_tab)->toBeFalse();
});

it('responsável atualiza controle parental ao alterar o fiado', function () {
    ['user' => $user, 'student' => $student, 'school' => $school, 'tenant' => $tenant] = parentChildSetup();

    app(PinService::class)->assign($student, '1234');

    ParentalControl::factory()->create([
        'tenant_id' => $tenant->id,
        'student_id' => $student->id,
        'allow_tab_usage' => true,
    ]);

    $this->actingAs($user)
        ->put(route('parent.children.update', $student), [
            'name' => $student->name,
            'school_id' => $school->id,
            'can_buy_on_tab' => 0,
        ])
        ->assertRedirect(route('parent.children.show', $student));

    expect($student->fresh()->can_buy_on_tab)->toBeFalse()
        ->and(ParentalControl::query()->where('student_id', $student->id)->value('allow_tab_usage'))->toBeFalse();
});

it('aluno vê e altera o próprio pin', function () {
    $tenant = Tenant::factory()->create();
    $school = School::factory()->create(['tenant_id' => $tenant->id]);
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'user_type' => 'student',
    ]);
    $student = Student::factory()->create([
        'tenant_id' => $tenant->id,
        'school_id' => $school->id,
        'user_id' => $user->id,
        'can_buy_on_tab' => true,
    ]);

    app(PinService::class)->assign($student, '4321');

    $this->actingAs($user)
        ->get(route('student.account'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Student/Account')
            ->where('student.pin', '4321')
            ->where('student.has_pin', true)
            ->where('student.can_buy_on_tab', true));

    $this->actingAs($user)
        ->put(route('student.account.pin'), [
            'personal_pin' => '9876',
            'personal_pin_confirmation' => '9876',
        ])
        ->assertRedirect(route('student.account'));

    expect(app(PinService::class)->reveal($student->fresh()))->toBe('9876')
        ->and(app(PinService::class)->verify($student->fresh(), '9876'))->toBeTrue();
});

it('bloqueia outro responsável de editar o filho', function () {
    ['student' => $student] = parentChildSetup();

    $otherTenant = Tenant::factory()->create();
    $otherUser = User::factory()->create([
        'tenant_id' => $otherTenant->id,
        'user_type' => 'parent',
    ]);
    ParentGuardian::factory()->create([
        'tenant_id' => $otherTenant->id,
        'user_id' => $otherUser->id,
    ]);

    $this->actingAs($otherUser)
        ->get(route('parent.children.edit', $student))
        ->assertNotFound();
});

it('portal do responsavel abre mesmo com pin criptografado invalido', function () {
    ['user' => $user, 'student' => $student] = parentChildSetup();

    DB::table('students')->where('id', $student->id)->update([
        'personal_pin' => 'eyJpdiI6ImJhZCIsInZhbHVlIjoiYmFkIiwibWFjIjoiYmFkIiwidGFnIjoiIn0=',
        'personal_pin_hash' => app(PinService::class)->hash('1234'),
    ]);

    $this->actingAs($user)
        ->get(route('parent.dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Parent/Dashboard'));

    $fresh = $student->fresh();

    expect(app(PinService::class)->hasPin($fresh))->toBeTrue()
        ->and(app(PinService::class)->reveal($fresh))->toBeNull()
        ->and(app(PinService::class)->verify($fresh, '1234'))->toBeTrue();
});

it('ainda le pin legado gravado em texto puro', function () {
    ['student' => $student] = parentChildSetup();

    DB::table('students')->where('id', $student->id)->update([
        'personal_pin' => '2580',
        'personal_pin_hash' => null,
    ]);

    $fresh = $student->fresh();

    expect(app(PinService::class)->reveal($fresh))->toBe('2580')
        ->and(app(PinService::class)->verify($fresh, '2580'))->toBeTrue();
});
