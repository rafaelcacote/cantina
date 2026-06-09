<?php

namespace Database\Seeders;

use App\Models\Operator;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ParentalControl;
use App\Models\ParentalControlAllowedCategory;
use App\Models\ParentalControlBlockedProduct;
use App\Models\ParentalPreselectedOrder;
use App\Models\ParentalPreselectedOrderItem;
use App\Models\Payment;
use App\Models\ParentGuardian;
use App\Models\Plan;
use App\Models\DailyMenu;
use App\Models\DailyMenuItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductSection;
use App\Models\School;
use App\Models\Stock;
use App\Models\Student;
use App\Models\StudentTab;
use App\Models\StudentWallet;
use App\Models\StudentParent;
use App\Models\Subscription;
use App\Models\TabEntry;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\PurchaseAuthorization;
use App\Models\Notification;
use App\Models\AuditLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SaasInitialSeeder extends Seeder
{
    public function run(): void
    {
        $plan = Plan::query()->updateOrCreate(
            ['slug' => 'basico'],
            [
                'name' => 'Plano Basico',
                'price' => 99.90,
                'billing_cycle' => 'monthly',
                'max_students' => 500,
                'max_users' => 20,
                'features' => [
                    'reports' => true,
                    'mobile_app' => true,
                    'parental_control' => true,
                ],
                'active' => true,
            ]
        );

        $tenant = Tenant::query()->updateOrCreate(
            ['slug' => 'demo-cantina'],
            [
                'name' => 'Cantina Demo',
                'document' => '00000000000191',
                'email' => 'demo@cantina.local',
                'phone' => '(11) 99999-0000',
                'status' => 'active',
                'trial_ends_at' => now()->addDays(15),
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'superadmin@cantina.local'],
            [
                'tenant_id' => null,
                'name' => 'Super Admin',
                'phone' => '(11) 90000-0001',
                'cpf' => '00000000000',
                'user_type' => 'super_admin',
                'active' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'admin@demo.local'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Tenant Admin Demo',
                'phone' => '(11) 90000-0002',
                'cpf' => '11111111111',
                'user_type' => 'tenant_admin',
                'active' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );

        Subscription::query()->updateOrCreate(
            ['tenant_id' => $tenant->id, 'plan_id' => $plan->id],
            [
                'status' => 'active',
                'starts_at' => now(),
                'trial_ends_at' => now()->addDays(15),
                'next_billing_at' => now()->addMonth(),
                'cancelled_at' => null,
            ]
        );

        $school = School::query()->updateOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Escola Demo'],
            [
                'document' => '12345678000199',
                'phone' => '(11) 4002-8922',
                'email' => 'escola@demo.local',
                'address' => 'Rua da Escola, 100 - Centro',
                'active' => true,
            ]
        );

        $studentOne = Student::query()->updateOrCreate(
            ['tenant_id' => $tenant->id, 'enrollment_number' => 'MAT001'],
            [
                'school_id' => $school->id,
                'name' => 'Aluno Demo 1',
                'birth_date' => '2014-03-12',
                'grade' => '5o Ano',
                'classroom' => 'A',
                'shift' => 'morning',
                'status' => 'active',
                'can_buy_on_credit' => false,
                'can_buy_on_tab' => false,
                'convenience_access' => false,
                'snack_access' => true,
            ]
        );

        $studentTwo = Student::query()->updateOrCreate(
            ['tenant_id' => $tenant->id, 'enrollment_number' => 'MAT002'],
            [
                'school_id' => $school->id,
                'name' => 'Aluno Demo 2',
                'birth_date' => '2015-07-20',
                'grade' => '4o Ano',
                'classroom' => 'B',
                'shift' => 'afternoon',
                'status' => 'active',
                'can_buy_on_credit' => true,
                'can_buy_on_tab' => false,
                'convenience_access' => true,
                'snack_access' => true,
            ]
        );

        $parentUser = User::query()->updateOrCreate(
            ['email' => 'parent@demo.local'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Responsavel Demo',
                'phone' => '(11) 90000-0003',
                'cpf' => '22222222222',
                'user_type' => 'parent',
                'active' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );

        $parent = ParentGuardian::query()->updateOrCreate(
            ['tenant_id' => $tenant->id, 'user_id' => $parentUser->id],
            [
                'name' => $parentUser->name,
                'cpf' => $parentUser->cpf,
                'phone' => $parentUser->phone,
                'email' => $parentUser->email,
            ]
        );

        StudentParent::query()->updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'student_id' => $studentOne->id,
                'parent_id' => $parent->id,
            ],
            [
                'relationship_type' => 'mother',
                'is_primary' => true,
                'financial_responsible' => true,
            ]
        );

        $operatorUser = User::query()->updateOrCreate(
            ['email' => 'operator@demo.local'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Operador Demo',
                'phone' => '(11) 90000-0004',
                'cpf' => '33333333333',
                'user_type' => 'operator',
                'active' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );

        Operator::query()->updateOrCreate(
            ['tenant_id' => $tenant->id, 'user_id' => $operatorUser->id],
            [
                'school_id' => $school->id,
                'role' => 'operator',
            ]
        );

        $sectionLanches = ProductSection::query()->updateOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Lanches'],
            [
                'slug' => 'lanches',
                'description' => 'Produtos preparados e lanches',
                'active' => true,
            ]
        );

        $sectionConveniencia = ProductSection::query()->updateOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Conveniência'],
            [
                'slug' => 'conveniencia',
                'description' => 'Produtos de conveniência',
                'active' => true,
            ]
        );

        $categoriaSanduiches = ProductCategory::query()->updateOrCreate(
            ['tenant_id' => $tenant->id, 'section_id' => $sectionLanches->id, 'name' => 'Sanduíches'],
            [
                'slug' => 'sanduiches',
                'description' => 'Categoria de sanduíches',
                'active' => true,
            ]
        );

        $categoriaSucos = ProductCategory::query()->updateOrCreate(
            ['tenant_id' => $tenant->id, 'section_id' => $sectionLanches->id, 'name' => 'Sucos'],
            [
                'slug' => 'sucos',
                'description' => 'Categoria de sucos',
                'active' => true,
            ]
        );

        $categoriaDoces = ProductCategory::query()->updateOrCreate(
            ['tenant_id' => $tenant->id, 'section_id' => $sectionConveniencia->id, 'name' => 'Doces'],
            [
                'slug' => 'doces',
                'description' => 'Categoria de doces',
                'active' => true,
            ]
        );

        $sanduiche = Product::query()->updateOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Sanduíche natural'],
            [
                'section_id' => $sectionLanches->id,
                'category_id' => $categoriaSanduiches->id,
                'description' => 'Sanduíche natural de frango',
                'sku' => 'LAN-001',
                'product_type' => 'resale',
                'sale_type' => 'unit',
                'price' => 12.50,
                'cost_price' => 7.20,
                'active' => true,
                'visible_in_app' => true,
                'allow_custom_request' => true,
                'requires_preparation' => true,
                'stock_controlled' => true,
                'minimum_stock_alert' => 5,
            ]
        );

        $suco = Product::query()->updateOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Suco de laranja'],
            [
                'section_id' => $sectionLanches->id,
                'category_id' => $categoriaSucos->id,
                'description' => 'Suco natural 300ml',
                'sku' => 'BEB-001',
                'product_type' => 'resale',
                'sale_type' => 'unit',
                'price' => 6.00,
                'cost_price' => 3.20,
                'active' => true,
                'visible_in_app' => true,
                'allow_custom_request' => false,
                'requires_preparation' => false,
                'stock_controlled' => true,
                'minimum_stock_alert' => 5,
            ]
        );

        $picole = Product::query()->updateOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Picolé'],
            [
                'section_id' => $sectionConveniencia->id,
                'category_id' => $categoriaDoces->id,
                'description' => 'Picolé sabores variados',
                'sku' => 'DOC-001',
                'product_type' => 'resale',
                'sale_type' => 'unit',
                'price' => 4.50,
                'cost_price' => 2.00,
                'active' => true,
                'visible_in_app' => true,
                'allow_custom_request' => false,
                'requires_preparation' => false,
                'stock_controlled' => true,
                'minimum_stock_alert' => 5,
            ]
        );

        foreach ([
            ['product' => $sanduiche, 'qty' => 30],
            ['product' => $suco, 'qty' => 50],
            ['product' => $picole, 'qty' => 40],
        ] as $item) {
            Stock::query()->updateOrCreate(
                ['product_id' => $item['product']->id],
                [
                    'tenant_id' => $tenant->id,
                    'quantity' => $item['qty'],
                    'reserved_quantity' => 0,
                ]
            );
        }

        $todayMenu = DailyMenu::query()->updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'school_id' => $school->id,
                'menu_date' => now()->toDateString(),
            ],
            [
                'title' => 'Cardápio de Hoje',
                'description' => 'Cardápio diário demonstrativo',
                'active' => true,
            ]
        );

        foreach ([
            ['product' => $sanduiche, 'sort' => 1],
            ['product' => $suco, 'sort' => 2],
            ['product' => $picole, 'sort' => 3],
        ] as $item) {
            DailyMenuItem::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'daily_menu_id' => $todayMenu->id,
                    'product_id' => $item['product']->id,
                ],
                [
                    'planned_quantity' => 50,
                    'available_quantity' => 40,
                    'price_override' => null,
                    'sort_order' => $item['sort'],
                    'active' => true,
                ]
            );
        }

        $demoOrder = Order::query()->updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'school_id' => $school->id,
                'student_id' => $studentOne->id,
                'order_type' => 'immediate',
                'order_channel' => 'app',
                'status' => 'pending',
            ],
            [
                'parent_id' => $parent->id,
                'placed_by_user_id' => $parentUser->id,
                'payment_mode' => 'wallet',
                'total_amount' => 18.50,
                'discount_amount' => 0,
                'final_amount' => 18.50,
                'scheduled_for' => null,
                'notes' => 'Pedido demo',
            ]
        );

        foreach ([
            ['product' => $sanduiche, 'qty' => 1],
            ['product' => $suco, 'qty' => 1],
        ] as $item) {
            $unitPrice = (float) $item['product']->price;

            OrderItem::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'order_id' => $demoOrder->id,
                    'product_id' => $item['product']->id,
                ],
                [
                    'item_name_snapshot' => $item['product']->name,
                    'unit_price' => $unitPrice,
                    'quantity' => $item['qty'],
                    'total_price' => $unitPrice * $item['qty'],
                    'observation' => null,
                    'custom_request_text' => null,
                    'item_status' => 'pending',
                ]
            );
        }

        $walletStudentOne = StudentWallet::query()->updateOrCreate(
            ['tenant_id' => $tenant->id, 'student_id' => $studentOne->id],
            [
                'balance' => 41.50,
                'credit_limit' => 0,
                'allow_negative_balance' => false,
            ]
        );

        StudentWallet::query()->updateOrCreate(
            ['tenant_id' => $tenant->id, 'student_id' => $studentTwo->id],
            [
                'balance' => 20.00,
                'credit_limit' => 10.00,
                'allow_negative_balance' => true,
            ]
        );

        WalletTransaction::query()->updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'wallet_id' => $walletStudentOne->id,
                'student_id' => $studentOne->id,
                'transaction_type' => 'topup',
                'reference_type' => 'payment',
            ],
            [
                'amount' => 60.00,
                'balance_before' => 0,
                'balance_after' => 60.00,
                'reference_id' => null,
                'description' => 'Recarga inicial',
                'created_by' => $parentUser->id,
            ]
        );

        WalletTransaction::query()->updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'wallet_id' => $walletStudentOne->id,
                'student_id' => $studentOne->id,
                'transaction_type' => 'purchase',
                'reference_type' => 'order',
                'reference_id' => $demoOrder->id,
            ],
            [
                'amount' => -18.50,
                'balance_before' => 60.00,
                'balance_after' => 41.50,
                'description' => 'Compra no app',
                'created_by' => $parentUser->id,
            ]
        );

        $studentTab = StudentTab::query()->updateOrCreate(
            ['tenant_id' => $tenant->id, 'student_id' => $studentTwo->id],
            [
                'current_balance' => 12.00,
                'billing_cycle_type' => 'monthly',
                'due_day' => 10,
                'active' => true,
            ]
        );

        $tabEntry = TabEntry::query()->updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'student_tab_id' => $studentTab->id,
                'student_id' => $studentTwo->id,
                'order_id' => $demoOrder->id,
            ],
            [
                'amount' => 12.00,
                'description' => 'Consumo no fiado',
                'entry_date' => now()->toDateString(),
                'status' => 'open',
                'authorized_by_pin' => true,
                'authorization_method' => 'student_pin',
                'authorized_at' => now(),
                'created_by' => $operatorUser->id,
            ]
        );

        Payment::query()->updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'student_id' => $studentOne->id,
                'parent_id' => $parent->id,
                'reference' => 'PAY-DEMO-001',
            ],
            [
                'amount' => 60.00,
                'payment_method' => 'pix',
                'status' => 'completed',
                'paid_at' => now(),
                'created_by' => $parentUser->id,
            ]
        );

        $parentalControl = ParentalControl::query()->updateOrCreate(
            ['tenant_id' => $tenant->id, 'student_id' => $studentOne->id],
            [
                'enabled' => true,
                'control_mode' => 'partial',
                'daily_spending_limit' => 25.00,
                'weekly_spending_limit' => 120.00,
                'allow_tab_usage' => true,
                'allow_wallet_usage' => true,
                'allow_convenience_access' => false,
                'allow_snack_access' => true,
                'notes' => 'Controle parental demo',
            ]
        );

        ParentalControlAllowedCategory::query()->updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'parental_control_id' => $parentalControl->id,
                'category_id' => $categoriaSanduiches->id,
            ],
            []
        );

        ParentalControlAllowedCategory::query()->updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'parental_control_id' => $parentalControl->id,
                'category_id' => $categoriaSucos->id,
            ],
            []
        );

        ParentalControlBlockedProduct::query()->updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'parental_control_id' => $parentalControl->id,
                'product_id' => $picole->id,
            ],
            []
        );

        $preselectedOrder = ParentalPreselectedOrder::query()->updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'school_id' => $school->id,
                'parent_id' => $parent->id,
                'student_id' => $studentOne->id,
                'order_date' => now()->toDateString(),
            ],
            [
                'status' => 'active',
                'notes' => 'Pedido pre-definido demo',
            ]
        );

        ParentalPreselectedOrderItem::query()->updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'parental_preselected_order_id' => $preselectedOrder->id,
                'product_id' => $sanduiche->id,
            ],
            [
                'quantity' => 1,
                'notes' => null,
            ]
        );

        PurchaseAuthorization::query()->updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'school_id' => $school->id,
                'student_id' => $studentTwo->id,
                'tab_entry_id' => $tabEntry->id,
            ],
            [
                'order_id' => null,
                'authorization_type' => 'tab_confirmation',
                'auth_method' => 'pin',
                'success' => true,
                'failure_reason' => null,
                'device_type' => 'terminal',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Seeder Demo Agent',
                'created_by' => $operatorUser->id,
            ]
        );

        Notification::query()->updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'user_id' => $parentUser->id,
                'student_id' => $studentOne->id,
                'notification_type' => 'purchase',
                'title' => 'Compra realizada',
            ],
            [
                'message' => 'Compra de Sanduíche natural processada.',
                'payload' => ['order_id' => $demoOrder->id],
                'read_at' => null,
            ]
        );

        Notification::query()->updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'user_id' => $operatorUser->id,
                'student_id' => $studentTwo->id,
                'notification_type' => 'authorization',
                'title' => 'Autorização de fiado',
            ],
            [
                'message' => 'Fiado autorizado via PIN do aluno.',
                'payload' => ['student_id' => $studentTwo->id],
                'read_at' => null,
            ]
        );

        AuditLog::query()->updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'user_id' => $tenant->users()->where('email', 'admin@demo.local')->value('id'),
                'action' => 'create',
                'entity_type' => 'parental_control',
                'entity_id' => $parentalControl->id,
            ],
            [
                'old_data' => null,
                'new_data' => [
                    'enabled' => true,
                    'control_mode' => 'partial',
                ],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Seeder Demo Agent',
            ]
        );
    }
}
