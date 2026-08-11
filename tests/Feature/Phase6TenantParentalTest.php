<?php

use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\ParentalControl;
use App\Models\PurchaseAuthorization;
use App\Models\School;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createTenantAdmin(Tenant $tenant): User
{
    return User::factory()->create([
        'tenant_id' => $tenant->id,
        'user_type' => 'tenant_admin',
    ]);
}

it('tenant admin acessa listagem de controles parentais', function () {
    $tenant = Tenant::factory()->create();
    $admin = createTenantAdmin($tenant);
    $student = Student::factory()->create(['tenant_id' => $tenant->id]);
    ParentalControl::factory()->create([
        'tenant_id' => $tenant->id,
        'student_id' => $student->id,
        'control_mode' => 'none',
    ]);

    $this->actingAs($admin)
        ->get(route('tenant.parental-controls.index'))
        ->assertOk()
        ->assertSee('Controles Parentais');
});

it('tenant admin cria controle parental', function () {
    $tenant = Tenant::factory()->create();
    $admin = createTenantAdmin($tenant);
    $student = Student::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($admin)
        ->post(route('tenant.parental-controls.store'), [
            'student_id' => $student->id,
            'control_mode' => 'allowlist',
            'daily_spending_limit' => 25,
            'weekly_spending_limit' => 100,
            'enabled' => '1',
            'allow_tab_usage' => '1',
            'allow_wallet_usage' => '1',
            'allow_snack_access' => '1',
        ])
        ->assertRedirect();

    expect(ParentalControl::query()->where('tenant_id', $tenant->id)->where('student_id', $student->id)->exists())->toBeTrue();
});

it('retorna 404 ao acessar controle parental de outro tenant', function () {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();
    $adminA = createTenantAdmin($tenantA);
    $studentB = Student::factory()->create(['tenant_id' => $tenantB->id]);
    $controlB = ParentalControl::factory()->create([
        'tenant_id' => $tenantB->id,
        'student_id' => $studentB->id,
        'control_mode' => 'none',
    ]);

    $this->actingAs($adminA)
        ->get(route('tenant.parental-controls.show', $controlB))
        ->assertNotFound();
});

it('manager acessa módulos da fase 6', function () {
    $tenant = Tenant::factory()->create();
    $manager = User::factory()->create([
        'tenant_id' => $tenant->id,
        'user_type' => 'manager',
    ]);

    $this->actingAs($manager)
        ->get(route('tenant.parental-controls.index'))
        ->assertOk();

    $this->actingAs($manager)
        ->get(route('tenant.notifications.index'))
        ->assertOk();

    $this->actingAs($manager)
        ->get(route('tenant.audit-logs.index'))
        ->assertOk();
});

it('tenant admin marca notificação como lida', function () {
    $tenant = Tenant::factory()->create();
    $admin = createTenantAdmin($tenant);
    $notification = Notification::factory()->create([
        'tenant_id' => $tenant->id,
        'user_id' => $admin->id,
        'read_at' => null,
    ]);

    $this->actingAs($admin)
        ->patch(route('tenant.notifications.mark-as-read', $notification))
        ->assertRedirect(route('tenant.notifications.show', $notification));

    expect($notification->fresh()->read_at)->not->toBeNull();
});

it('tenant admin consulta autorizações pin e auditoria', function () {
    $tenant = Tenant::factory()->create();
    $admin = createTenantAdmin($tenant);
    $school = School::factory()->create(['tenant_id' => $tenant->id]);
    $student = Student::factory()->create(['tenant_id' => $tenant->id, 'school_id' => $school->id]);

    PurchaseAuthorization::factory()->create([
        'tenant_id' => $tenant->id,
        'school_id' => $school->id,
        'student_id' => $student->id,
    ]);

    AuditLog::factory()->create([
        'tenant_id' => $tenant->id,
        'user_id' => $admin->id,
    ]);

    $this->actingAs($admin)
        ->get(route('tenant.purchase-authorizations.index'))
        ->assertOk()
        ->assertSee('Autorizações PIN');

    $this->actingAs($admin)
        ->get(route('tenant.audit-logs.index'))
        ->assertOk()
        ->assertSee('Auditoria');
});
