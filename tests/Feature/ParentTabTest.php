<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ParentGuardian;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentParent;
use App\Models\StudentTab;
use App\Models\StudentWallet;
use App\Models\TabEntry;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function parentTabContext(): array
{
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
        'can_buy_on_tab' => true,
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
    $tab = StudentTab::factory()->create([
        'tenant_id' => $tenant->id,
        'student_id' => $student->id,
        'current_balance' => 18,
        'active' => true,
    ]);

    $augustOrder = Order::factory()->create([
        'tenant_id' => $tenant->id,
        'school_id' => $school->id,
        'student_id' => $student->id,
        'payment_mode' => 'tab',
        'status' => 'completed',
        'total_amount' => 12,
        'final_amount' => 12,
    ]);
    OrderItem::factory()->create([
        'tenant_id' => $tenant->id,
        'order_id' => $augustOrder->id,
        'product_id' => null,
        'item_name_snapshot' => 'Coxinha',
        'quantity' => 2,
        'unit_price' => 6,
        'total_price' => 12,
    ]);
    TabEntry::factory()->create([
        'tenant_id' => $tenant->id,
        'student_tab_id' => $tab->id,
        'student_id' => $student->id,
        'order_id' => $augustOrder->id,
        'amount' => 12,
        'description' => 'Lançamento do pedido #'.$augustOrder->id,
        'entry_date' => '2026-08-05',
        'status' => 'open',
        'created_by' => null,
    ]);

    $julyOrder = Order::factory()->create([
        'tenant_id' => $tenant->id,
        'school_id' => $school->id,
        'student_id' => $student->id,
        'payment_mode' => 'tab',
        'status' => 'completed',
        'total_amount' => 8,
        'final_amount' => 8,
    ]);
    TabEntry::factory()->create([
        'tenant_id' => $tenant->id,
        'student_tab_id' => $tab->id,
        'student_id' => $student->id,
        'order_id' => $julyOrder->id,
        'amount' => 8,
        'description' => 'Lançamento do pedido #'.$julyOrder->id,
        'entry_date' => '2026-07-20',
        'status' => 'paid',
        'created_by' => null,
    ]);

    return compact('tenant', 'school', 'user', 'parent', 'student', 'tab');
}

it('responsavel ve o fiado do filho filtrado por mes', function () {
    $ctx = parentTabContext();

    $this->actingAs($ctx['user'])
        ->get(route('parent.children.tab', ['student' => $ctx['student'], 'month' => '2026-08']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Parent/ChildTab')
            ->where('child.name', 'Joao Aluno')
            ->where('month.key', '2026-08')
            ->where('month.label', 'Agosto 2026')
            ->where('summary.charged', 12)
            ->where('summary.count', 1)
            ->where('tab_balance', 18)
            ->has('entries', 1)
            ->where('entries.0.preview', 'Coxinha')
            ->where('entries.0.amount', 12));

    $this->actingAs($ctx['user'])
        ->get(route('parent.children.tab', ['student' => $ctx['student'], 'month' => '2026-07']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Parent/ChildTab')
            ->where('month.key', '2026-07')
            ->where('summary.charged', 8)
            ->has('entries', 1));
});

it('responsavel ve o resumo mensal de fiado de todos os filhos', function () {
    $ctx = parentTabContext();

    $this->actingAs($ctx['user'])
        ->get(route('parent.tab.index', ['month' => '2026-08']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Parent/Tabs')
            ->where('month.label', 'Agosto 2026')
            ->where('summary.charged', 12)
            ->has('by_student', 1)
            ->where('by_student.0.name', 'Joao Aluno')
            ->where('by_student.0.summary.charged', 12)
            ->has('entries', 1));
});

it('ficha do filho mostra quanto pegou de fiado no mes atual', function () {
    $ctx = parentTabContext();

    $this->travelTo(now()->setDate(2026, 8, 11));

    $this->actingAs($ctx['user'])
        ->get(route('parent.children.show', $ctx['student']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Parent/ChildShow')
            ->where('tab.month.key', '2026-08')
            ->where('tab.summary.charged', 12)
            ->where('tab.open_balance', 18)
            ->where('child.tab_balance', 18));
});

it('bloqueia fiado de aluno que nao e do responsavel', function () {
    $ctx = parentTabContext();
    $other = Student::factory()->create([
        'tenant_id' => $ctx['tenant']->id,
        'school_id' => $ctx['school']->id,
        'name' => 'Outro Aluno',
    ]);

    $this->actingAs($ctx['user'])
        ->get(route('parent.children.tab', $other))
        ->assertNotFound();
});
