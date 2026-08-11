<?php

use App\Models\ParentGuardian;
use App\Models\Payment;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentParent;
use App\Models\StudentWallet;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WalletTopup;
use App\Models\WalletTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function parentWithChild(array $tenantOverrides = []): array
{
    $tenant = Tenant::factory()->create(array_merge([
        'pix' => '00000000000191',
        'phone' => '11999990000',
    ], $tenantOverrides));
    $school = School::factory()->create(['tenant_id' => $tenant->id]);
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'user_type' => 'parent',
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
        'balance' => 10,
    ]);
    StudentParent::factory()->create([
        'tenant_id' => $tenant->id,
        'parent_id' => $parent->id,
        'student_id' => $student->id,
    ]);

    return compact('tenant', 'user', 'parent', 'student');
}

it('responsável cria solicitação de recarga pix', function () {
    ['user' => $user, 'student' => $student] = parentWithChild();

    $this->actingAs($user)
        ->post(route('parent.topups.store', $student), [
            'amount' => '50.00',
        ])
        ->assertRedirect();

    $topup = WalletTopup::query()->first();

    expect($topup)->not->toBeNull()
        ->and($topup->status)->toBe(WalletTopup::STATUS_AWAITING_PAYMENT)
        ->and((float) $topup->amount)->toBe(50.0)
        ->and($topup->pix_key)->toBe('00000000000191')
        ->and($topup->code)->toHaveLength(4);
});

it('responsável envia comprovante e gestor credita a carteira', function () {
    Storage::fake('public');
    ['tenant' => $tenant, 'user' => $user, 'parent' => $parent, 'student' => $student] = parentWithChild();
    $admin = User::factory()->create([
        'tenant_id' => $tenant->id,
        'user_type' => 'tenant_admin',
    ]);
    $topup = WalletTopup::factory()->create([
        'tenant_id' => $tenant->id,
        'student_id' => $student->id,
        'parent_id' => $parent->id,
        'amount' => 40,
        'pix_key' => $tenant->pix,
        'status' => WalletTopup::STATUS_AWAITING_PAYMENT,
    ]);

    $this->actingAs($user)
        ->post(route('parent.topups.receipt', $topup), [
            'receipt' => UploadedFile::fake()->image('comprovante.jpg'),
        ])
        ->assertRedirect(route('parent.topups.show', $topup));

    expect($topup->fresh()->status)->toBe(WalletTopup::STATUS_PENDING_REVIEW)
        ->and($topup->fresh()->receipt_path)->not->toBeNull();

    $this->actingAs($admin)
        ->patch(route('tenant.wallet-topups.approve', $topup))
        ->assertRedirect(route('tenant.wallet-topups.show', $topup));

    $topup->refresh();

    expect($topup->status)->toBe(WalletTopup::STATUS_APPROVED)
        ->and((float) $student->fresh()->wallet->balance)->toBe(50.0)
        ->and(WalletTransaction::query()->where('reference_type', 'wallet_topup')->where('reference_id', $topup->id)->exists())->toBeTrue()
        ->and(Payment::query()->where('reference', $topup->code)->where('status', 'completed')->exists())->toBeTrue();
});

it('gestor recusa recarga sem creditar', function () {
    Storage::fake('public');
    ['tenant' => $tenant, 'parent' => $parent, 'student' => $student] = parentWithChild();
    $admin = User::factory()->create([
        'tenant_id' => $tenant->id,
        'user_type' => 'tenant_admin',
    ]);
    $topup = WalletTopup::factory()->create([
        'tenant_id' => $tenant->id,
        'student_id' => $student->id,
        'parent_id' => $parent->id,
        'amount' => 25,
        'status' => WalletTopup::STATUS_PENDING_REVIEW,
        'receipt_path' => 'wallet-topups/1/comprovante.jpg',
    ]);

    $this->actingAs($admin)
        ->patch(route('tenant.wallet-topups.reject', $topup), [
            'rejection_reason' => 'Valor não encontrado no extrato.',
        ])
        ->assertRedirect(route('tenant.wallet-topups.show', $topup));

    expect($topup->fresh()->status)->toBe(WalletTopup::STATUS_REJECTED)
        ->and((float) $student->fresh()->wallet->balance)->toBe(10.0);
});

it('não cria recarga sem chave pix no tenant', function () {
    ['user' => $user, 'student' => $student] = parentWithChild(['pix' => null]);

    $this->actingAs($user)
        ->from(route('parent.children.show', $student))
        ->post(route('parent.topups.store', $student), [
            'amount' => '20',
        ])
        ->assertRedirect()
        ->assertSessionHasErrors('amount');
});
