<?php

use App\Models\ParentalControl;
use App\Models\ParentalControlBlockedProduct;
use App\Models\ParentGuardian;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductSection;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentParent;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function parentControlContext(): array
{
    $tenant = Tenant::factory()->create();
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
        'status' => 'active',
        'can_buy_on_tab' => false,
        'snack_access' => true,
        'convenience_access' => false,
    ]);
    StudentParent::factory()->create([
        'tenant_id' => $tenant->id,
        'parent_id' => $parent->id,
        'student_id' => $student->id,
    ]);

    $section = ProductSection::factory()->create([
        'tenant_id' => $tenant->id,
        'slug' => 'lanches',
    ]);
    $category = ProductCategory::factory()->create([
        'tenant_id' => $tenant->id,
        'section_id' => $section->id,
    ]);
    $product = Product::factory()->create([
        'tenant_id' => $tenant->id,
        'section_id' => $section->id,
        'category_id' => $category->id,
        'name' => 'Refrigerante',
        'price' => 5,
        'active' => true,
        'visible_in_app' => true,
    ]);

    return compact('tenant', 'school', 'user', 'parent', 'student', 'product');
}

it('responsavel abre a tela de controle parental do filho', function () {
    $ctx = parentControlContext();

    $this->actingAs($ctx['user'])
        ->get(route('parent.children.controls', $ctx['student']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Parent/ChildControls')
            ->where('child.id', $ctx['student']->id)
            ->where('control.enabled', false)
            ->has('products', 1)
            ->where('products.0.name', 'Refrigerante'));
});

it('responsavel salva limites secoes e produto bloqueado', function () {
    $ctx = parentControlContext();

    $this->actingAs($ctx['user'])
        ->put(route('parent.children.controls.update', $ctx['student']), [
            'enabled' => 1,
            'daily_spending_limit' => 20,
            'weekly_spending_limit' => 80,
            'allow_tab_usage' => 1,
            'allow_wallet_usage' => 1,
            'allow_convenience_access' => 0,
            'allow_snack_access' => 1,
            'blocked_product_ids' => [$ctx['product']->id],
        ])
        ->assertRedirect(route('parent.children.controls', $ctx['student']));

    $control = ParentalControl::query()
        ->where('student_id', $ctx['student']->id)
        ->first();

    expect($control)->not->toBeNull()
        ->and($control->enabled)->toBeTrue()
        ->and($control->control_mode)->toBe('blocklist')
        ->and((float) $control->daily_spending_limit)->toBe(20.0)
        ->and((float) $control->weekly_spending_limit)->toBe(80.0)
        ->and($control->allow_tab_usage)->toBeTrue()
        ->and($control->allow_convenience_access)->toBeFalse()
        ->and($control->allow_snack_access)->toBeTrue();

    expect(ParentalControlBlockedProduct::query()
        ->where('parental_control_id', $control->id)
        ->where('product_id', $ctx['product']->id)
        ->exists())->toBeTrue();

    $student = $ctx['student']->fresh();

    expect($student->can_buy_on_tab)->toBeTrue()
        ->and($student->convenience_access)->toBeFalse()
        ->and($student->snack_access)->toBeTrue();
});

it('bloqueia controle parental de aluno que nao e do responsavel', function () {
    $ctx = parentControlContext();
    $other = Student::factory()->create([
        'tenant_id' => $ctx['tenant']->id,
        'school_id' => $ctx['school']->id,
        'status' => 'active',
    ]);

    $this->actingAs($ctx['user'])
        ->get(route('parent.children.controls', $other))
        ->assertNotFound();

    $this->actingAs($ctx['user'])
        ->put(route('parent.children.controls.update', $other), [
            'enabled' => 1,
            'allow_tab_usage' => 0,
            'allow_wallet_usage' => 1,
            'allow_convenience_access' => 0,
            'allow_snack_access' => 1,
            'blocked_product_ids' => [],
        ])
        ->assertNotFound();
});
