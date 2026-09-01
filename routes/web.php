<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\DailyMenuController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OperatorController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ParentalAllowedCategoryController;
use App\Http\Controllers\Admin\ParentalBlockedProductController;
use App\Http\Controllers\Admin\ParentalControlController;
use App\Http\Controllers\Admin\ParentalPreselectedOrderController;
use App\Http\Controllers\Admin\ParentController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductSectionController;
use App\Http\Controllers\Admin\PurchaseAuthorizationController;
use App\Http\Controllers\Admin\SchoolController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\StudentParentController;
use App\Http\Controllers\Admin\StudentTabController;
use App\Http\Controllers\Admin\StudentWalletController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\TabEntryController;
use App\Http\Controllers\Admin\TenantController;
use App\Http\Controllers\Admin\TenantInvitationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WalletTransactionController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\InvitationAcceptController;
use App\Http\Controllers\Operator\DashboardController as OperatorDashboardController;
use App\Http\Controllers\Operator\OrderController as OperatorOrderController;
use App\Http\Controllers\Operator\PosController as OperatorPosController;
use App\Http\Controllers\Operator\StudentController as OperatorStudentController;
use App\Http\Controllers\Operator\WalletController as OperatorWalletController;
use App\Http\Controllers\ParentPortal\AccountController as ParentAccountController;
use App\Http\Controllers\ParentPortal\ChildAccessController as ParentChildAccessController;
use App\Http\Controllers\ParentPortal\ChildControlController as ParentChildControlController;
use App\Http\Controllers\ParentPortal\ChildController as ParentChildController;
use App\Http\Controllers\ParentPortal\DashboardController as ParentDashboardController;
use App\Http\Controllers\ParentPortal\OrderController as ParentOrderController;
use App\Http\Controllers\ParentPortal\SelfOrderController as ParentSelfOrderController;
use App\Http\Controllers\ParentPortal\TabController as ParentTabController;
use App\Http\Controllers\ParentPortal\WalletTopupController as ParentWalletTopupController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RequesterPortal\DashboardController as RequesterDashboardController;
use App\Http\Controllers\RequesterPortal\OrderController as RequesterOrderController;
use App\Http\Controllers\StudentInviteAcceptController;
use App\Http\Controllers\StudentPortal\DashboardController as StudentDashboardController;
use App\Http\Controllers\StudentPortal\OrderController as StudentOrderController;
use App\Http\Controllers\Tenant\AuditLogController as TenantAuditLogController;
use App\Http\Controllers\Tenant\DailyMenuController as TenantDailyMenuController;
use App\Http\Controllers\Tenant\DashboardController as TenantDashboardController;
use App\Http\Controllers\Tenant\NotificationController as TenantNotificationController;
use App\Http\Controllers\Tenant\OrderController as TenantOrderController;
use App\Http\Controllers\Tenant\ParentalAllowedCategoryController as TenantParentalAllowedCategoryController;
use App\Http\Controllers\Tenant\ParentalBlockedProductController as TenantParentalBlockedProductController;
use App\Http\Controllers\Tenant\ParentalControlController as TenantParentalControlController;
use App\Http\Controllers\Tenant\ParentController as TenantParentController;
use App\Http\Controllers\Tenant\ParentInvitationController as TenantParentInvitationController;
use App\Http\Controllers\Tenant\PaymentController as TenantPaymentController;
use App\Http\Controllers\Tenant\ProductCategoryController as TenantProductCategoryController;
use App\Http\Controllers\Tenant\ProductController as TenantProductController;
use App\Http\Controllers\Tenant\ProductSectionController as TenantProductSectionController;
use App\Http\Controllers\Tenant\PurchaseAuthorizationController as TenantPurchaseAuthorizationController;
use App\Http\Controllers\Tenant\RequesterInvitationController as TenantRequesterInvitationController;
use App\Http\Controllers\Tenant\SchoolController as TenantSchoolController;
use App\Http\Controllers\Tenant\StockController as TenantStockController;
use App\Http\Controllers\Tenant\StudentController as TenantStudentController;
use App\Http\Controllers\Tenant\StudentParentController as TenantStudentParentController;
use App\Http\Controllers\Tenant\StudentTabController as TenantStudentTabController;
use App\Http\Controllers\Tenant\StudentWalletController as TenantStudentWalletController;
use App\Http\Controllers\Tenant\TabEntryController as TenantTabEntryController;
use App\Http\Controllers\Tenant\WalletTopupController as TenantWalletTopupController;
use App\Http\Controllers\Tenant\WalletTransactionController as TenantWalletTransactionController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::redirect('/login', '/signin')->name('login');

    // authentication pages
    Route::get('/signin', [AuthenticatedSessionController::class, 'create'])->name('signin');
    Route::post('/signin', [AuthenticatedSessionController::class, 'store'])->name('signin.store');

    Route::get('/signup', function () {
        return view('pages.auth.signup', ['title' => 'Sign Up']);
    })->name('signup');

    Route::get('/invite/{token}', [InvitationAcceptController::class, 'show'])->name('invitations.accept');
    Route::post('/invite/{token}', [InvitationAcceptController::class, 'store'])->name('invitations.accept.store');
    Route::get('/aluno/convite/{token}', [StudentInviteAcceptController::class, 'show'])->name('student-invitations.accept');
    Route::post('/aluno/convite/{token}', [StudentInviteAcceptController::class, 'store'])->name('student-invitations.accept.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'tenant.context'])->group(function () {
    // dashboard pages
    Route::get('/', function () {
        $userType = Auth::user()?->user_type;

        return match ($userType) {
            'tenant_admin', 'manager' => redirect()->route('tenant.dashboard'),
            'operator' => redirect()->route('operator.dashboard'),
            'parent' => redirect()->route('parent.dashboard'),
            'student' => redirect()->route('student.dashboard'),
            'requester' => redirect()->route('requester.dashboard'),
            default => view('pages.dashboard.ecommerce', ['title' => 'E-commerce Dashboard']),
        };
    })->name('dashboard');

    // calender pages
    Route::get('/calendar', function () {
        return view('pages.calender', ['title' => 'Calendar']);
    })->name('calendar');

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // form pages
    Route::get('/form-elements', function () {
        return view('pages.form.form-elements', ['title' => 'Form Elements']);
    })->name('form-elements');

    // tables pages
    Route::get('/basic-tables', function () {
        return view('pages.tables.basic-tables', ['title' => 'Basic Tables']);
    })->name('basic-tables');

    // pages

    Route::get('/blank', function () {
        return view('pages.blank', ['title' => 'Blank']);
    })->name('blank');

    // error pages
    Route::get('/error-404', function () {
        return view('pages.errors.error-404', ['title' => 'Error 404']);
    })->name('error-404');

    // chart pages
    Route::get('/line-chart', function () {
        return view('pages.chart.line-chart', ['title' => 'Line Chart']);
    })->name('line-chart');

    Route::get('/bar-chart', function () {
        return view('pages.chart.bar-chart', ['title' => 'Bar Chart']);
    })->name('bar-chart');

    // ui elements pages
    Route::get('/alerts', function () {
        return view('pages.ui-elements.alerts', ['title' => 'Alerts']);
    })->name('alerts');

    Route::get('/avatars', function () {
        return view('pages.ui-elements.avatars', ['title' => 'Avatars']);
    })->name('avatars');

    Route::get('/badge', function () {
        return view('pages.ui-elements.badges', ['title' => 'Badges']);
    })->name('badges');

    Route::get('/buttons', function () {
        return view('pages.ui-elements.buttons', ['title' => 'Buttons']);
    })->name('buttons');

    Route::get('/image', function () {
        return view('pages.ui-elements.images', ['title' => 'Images']);
    })->name('images');

    Route::get('/videos', function () {
        return view('pages.ui-elements.videos', ['title' => 'Videos']);
    })->name('videos');
});

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'tenant.context', 'super.admin'])
    ->group(function () {
        Route::get('/tenants', [TenantController::class, 'index'])->name('tenants.index');
        Route::get('/tenants/create', [TenantController::class, 'create'])->name('tenants.create');
        Route::post('/tenants', [TenantController::class, 'store'])->name('tenants.store');
        Route::get('/tenants/{tenant}', [TenantController::class, 'show'])->name('tenants.show');
        Route::get('/tenants/{tenant}/edit', [TenantController::class, 'edit'])->name('tenants.edit');
        Route::put('/tenants/{tenant}', [TenantController::class, 'update'])->name('tenants.update');

        Route::get('/plans', [PlanController::class, 'index'])->name('plans.index');
        Route::get('/plans/create', [PlanController::class, 'create'])->name('plans.create');
        Route::post('/plans', [PlanController::class, 'store'])->name('plans.store');
        Route::get('/plans/{plan}', [PlanController::class, 'show'])->name('plans.show');
        Route::get('/plans/{plan}/edit', [PlanController::class, 'edit'])->name('plans.edit');
        Route::put('/plans/{plan}', [PlanController::class, 'update'])->name('plans.update');

        Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::get('/subscriptions/create', [SubscriptionController::class, 'create'])->name('subscriptions.create');
        Route::post('/subscriptions', [SubscriptionController::class, 'store'])->name('subscriptions.store');
        Route::get('/subscriptions/{subscription}', [SubscriptionController::class, 'show'])->name('subscriptions.show');
        Route::get('/subscriptions/{subscription}/edit', [SubscriptionController::class, 'edit'])->name('subscriptions.edit');
        Route::put('/subscriptions/{subscription}', [SubscriptionController::class, 'update'])->name('subscriptions.update');

        Route::get('/tenant-invitations', [TenantInvitationController::class, 'index'])->name('tenant-invitations.index');
        Route::get('/tenant-invitations/create', [TenantInvitationController::class, 'create'])->name('tenant-invitations.create');
        Route::post('/tenant-invitations', [TenantInvitationController::class, 'store'])->name('tenant-invitations.store');
        Route::get('/tenant-invitations/{tenant_invitation}', [TenantInvitationController::class, 'show'])->name('tenant-invitations.show');
        Route::get('/tenant-invitations/{tenant_invitation}/edit', [TenantInvitationController::class, 'edit'])->name('tenant-invitations.edit');
        Route::put('/tenant-invitations/{tenant_invitation}', [TenantInvitationController::class, 'update'])->name('tenant-invitations.update');

        Route::get('/operators', [OperatorController::class, 'index'])->name('operators.index');
        Route::get('/operators/create', [OperatorController::class, 'create'])->name('operators.create');
        Route::post('/operators', [OperatorController::class, 'store'])->name('operators.store');
        Route::get('/operators/{operator}', [OperatorController::class, 'show'])->name('operators.show');
        Route::get('/operators/{operator}/edit', [OperatorController::class, 'edit'])->name('operators.edit');
        Route::put('/operators/{operator}', [OperatorController::class, 'update'])->name('operators.update');

        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');

        Route::get('/schools', [SchoolController::class, 'index'])->name('schools.index');
        Route::get('/schools/create', [SchoolController::class, 'create'])->name('schools.create');
        Route::post('/schools', [SchoolController::class, 'store'])->name('schools.store');
        Route::get('/schools/{school}', [SchoolController::class, 'show'])->name('schools.show');
        Route::get('/schools/{school}/edit', [SchoolController::class, 'edit'])->name('schools.edit');
        Route::put('/schools/{school}', [SchoolController::class, 'update'])->name('schools.update');

        Route::get('/students', [StudentController::class, 'index'])->name('students.index');
        Route::get('/students/create', [StudentController::class, 'create'])->name('students.create');
        Route::post('/students', [StudentController::class, 'store'])->name('students.store');
        Route::get('/students/{student}', [StudentController::class, 'show'])->name('students.show');
        Route::get('/students/{student}/edit', [StudentController::class, 'edit'])->name('students.edit');
        Route::put('/students/{student}', [StudentController::class, 'update'])->name('students.update');

        Route::get('/parents', [ParentController::class, 'index'])->name('parents.index');
        Route::get('/parents/create', [ParentController::class, 'create'])->name('parents.create');
        Route::post('/parents', [ParentController::class, 'store'])->name('parents.store');
        Route::get('/parents/{parent}', [ParentController::class, 'show'])->name('parents.show');
        Route::get('/parents/{parent}/edit', [ParentController::class, 'edit'])->name('parents.edit');
        Route::put('/parents/{parent}', [ParentController::class, 'update'])->name('parents.update');

        Route::get('/student-parents', [StudentParentController::class, 'index'])->name('student-parents.index');
        Route::get('/student-parents/create', [StudentParentController::class, 'create'])->name('student-parents.create');
        Route::post('/student-parents', [StudentParentController::class, 'store'])->name('student-parents.store');
        Route::get('/student-parents/{studentParent}', [StudentParentController::class, 'show'])->name('student-parents.show');
        Route::get('/student-parents/{studentParent}/edit', [StudentParentController::class, 'edit'])->name('student-parents.edit');
        Route::put('/student-parents/{studentParent}', [StudentParentController::class, 'update'])->name('student-parents.update');

        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');

        Route::get('/product-sections', [ProductSectionController::class, 'index'])->name('product-sections.index');
        Route::get('/product-sections/create', [ProductSectionController::class, 'create'])->name('product-sections.create');
        Route::post('/product-sections', [ProductSectionController::class, 'store'])->name('product-sections.store');
        Route::get('/product-sections/{productSection}', [ProductSectionController::class, 'show'])->name('product-sections.show');
        Route::get('/product-sections/{productSection}/edit', [ProductSectionController::class, 'edit'])->name('product-sections.edit');
        Route::put('/product-sections/{productSection}', [ProductSectionController::class, 'update'])->name('product-sections.update');

        Route::get('/product-categories', [ProductCategoryController::class, 'index'])->name('product-categories.index');
        Route::get('/product-categories/create', [ProductCategoryController::class, 'create'])->name('product-categories.create');
        Route::post('/product-categories', [ProductCategoryController::class, 'store'])->name('product-categories.store');
        Route::get('/product-categories/{productCategory}', [ProductCategoryController::class, 'show'])->name('product-categories.show');
        Route::get('/product-categories/{productCategory}/edit', [ProductCategoryController::class, 'edit'])->name('product-categories.edit');
        Route::put('/product-categories/{productCategory}', [ProductCategoryController::class, 'update'])->name('product-categories.update');

        Route::get('/stocks', [StockController::class, 'index'])->name('stocks.index');
        Route::get('/stocks/{stock}', [StockController::class, 'show'])->name('stocks.show');
        Route::get('/stocks/{stock}/edit', [StockController::class, 'edit'])->name('stocks.edit');
        Route::put('/stocks/{stock}', [StockController::class, 'update'])->name('stocks.update');
        Route::post('/stocks/{stock}/adjust', [StockController::class, 'adjust'])->name('stocks.adjust');

        Route::get('/stock-movements', [StockController::class, 'movements'])->name('stock-movements.index');

        Route::get('/daily-menus', [DailyMenuController::class, 'index'])->name('daily-menus.index');
        Route::get('/daily-menus/create', [DailyMenuController::class, 'create'])->name('daily-menus.create');
        Route::post('/daily-menus', [DailyMenuController::class, 'store'])->name('daily-menus.store');
        Route::get('/daily-menus/{dailyMenu}', [DailyMenuController::class, 'show'])->name('daily-menus.show');
        Route::get('/daily-menus/{dailyMenu}/edit', [DailyMenuController::class, 'edit'])->name('daily-menus.edit');
        Route::put('/daily-menus/{dailyMenu}', [DailyMenuController::class, 'update'])->name('daily-menus.update');
        Route::post('/daily-menus/{dailyMenu}/items', [DailyMenuController::class, 'addItem'])->name('daily-menus.items.store');
        Route::put('/daily-menus/{dailyMenu}/items/{item}', [DailyMenuController::class, 'updateItem'])->name('daily-menus.items.update');
        Route::delete('/daily-menus/{dailyMenu}/items/{item}', [DailyMenuController::class, 'removeItem'])->name('daily-menus.items.destroy');

        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::get('/orders/{order}/edit', [OrderController::class, 'edit'])->name('orders.edit');
        Route::put('/orders/{order}', [OrderController::class, 'update'])->name('orders.update');
        Route::post('/orders/{order}/items', [OrderController::class, 'addItem'])->name('orders.items.store');
        Route::put('/orders/{order}/items/{item}', [OrderController::class, 'updateItem'])->name('orders.items.update');
        Route::delete('/orders/{order}/items/{item}', [OrderController::class, 'removeItem'])->name('orders.items.destroy');
        Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status.update');

        Route::get('/student-wallets', [StudentWalletController::class, 'index'])->name('student-wallets.index');
        Route::get('/student-wallets/create', [StudentWalletController::class, 'create'])->name('student-wallets.create');
        Route::post('/student-wallets', [StudentWalletController::class, 'store'])->name('student-wallets.store');
        Route::get('/student-wallets/{studentWallet}', [StudentWalletController::class, 'show'])->name('student-wallets.show');
        Route::get('/student-wallets/{studentWallet}/edit', [StudentWalletController::class, 'edit'])->name('student-wallets.edit');
        Route::put('/student-wallets/{studentWallet}', [StudentWalletController::class, 'update'])->name('student-wallets.update');

        Route::get('/wallet-transactions', [WalletTransactionController::class, 'index'])->name('wallet-transactions.index');
        Route::get('/wallet-transactions/{walletTransaction}', [WalletTransactionController::class, 'show'])->name('wallet-transactions.show');

        Route::get('/student-tabs', [StudentTabController::class, 'index'])->name('student-tabs.index');
        Route::get('/student-tabs/create', [StudentTabController::class, 'create'])->name('student-tabs.create');
        Route::post('/student-tabs', [StudentTabController::class, 'store'])->name('student-tabs.store');
        Route::get('/student-tabs/{studentTab}', [StudentTabController::class, 'show'])->name('student-tabs.show');
        Route::get('/student-tabs/{studentTab}/edit', [StudentTabController::class, 'edit'])->name('student-tabs.edit');
        Route::put('/student-tabs/{studentTab}', [StudentTabController::class, 'update'])->name('student-tabs.update');

        Route::get('/tab-entries', [TabEntryController::class, 'index'])->name('tab-entries.index');
        Route::get('/tab-entries/create', [TabEntryController::class, 'create'])->name('tab-entries.create');
        Route::post('/tab-entries', [TabEntryController::class, 'store'])->name('tab-entries.store');
        Route::get('/tab-entries/{tabEntry}', [TabEntryController::class, 'show'])->name('tab-entries.show');
        Route::get('/tab-entries/{tabEntry}/edit', [TabEntryController::class, 'edit'])->name('tab-entries.edit');
        Route::put('/tab-entries/{tabEntry}', [TabEntryController::class, 'update'])->name('tab-entries.update');
        Route::delete('/tab-entries/{tabEntry}', [TabEntryController::class, 'destroy'])->name('tab-entries.destroy');

        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/create', [PaymentController::class, 'create'])->name('payments.create');
        Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
        Route::get('/payments/{payment}/edit', [PaymentController::class, 'edit'])->name('payments.edit');
        Route::put('/payments/{payment}', [PaymentController::class, 'update'])->name('payments.update');
        Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');

        Route::get('/parental-controls', [ParentalControlController::class, 'index'])->name('parental-controls.index');
        Route::get('/parental-controls/create', [ParentalControlController::class, 'create'])->name('parental-controls.create');
        Route::post('/parental-controls', [ParentalControlController::class, 'store'])->name('parental-controls.store');
        Route::get('/parental-controls/{parentalControl}', [ParentalControlController::class, 'show'])->name('parental-controls.show');
        Route::get('/parental-controls/{parentalControl}/edit', [ParentalControlController::class, 'edit'])->name('parental-controls.edit');
        Route::put('/parental-controls/{parentalControl}', [ParentalControlController::class, 'update'])->name('parental-controls.update');

        Route::get('/parental-allowed-categories', [ParentalAllowedCategoryController::class, 'index'])->name('parental-allowed-categories.index');
        Route::get('/parental-allowed-categories/create', [ParentalAllowedCategoryController::class, 'create'])->name('parental-allowed-categories.create');
        Route::post('/parental-allowed-categories', [ParentalAllowedCategoryController::class, 'store'])->name('parental-allowed-categories.store');
        Route::get('/parental-allowed-categories/{parentalAllowedCategory}', [ParentalAllowedCategoryController::class, 'show'])->name('parental-allowed-categories.show');
        Route::get('/parental-allowed-categories/{parentalAllowedCategory}/edit', [ParentalAllowedCategoryController::class, 'edit'])->name('parental-allowed-categories.edit');
        Route::put('/parental-allowed-categories/{parentalAllowedCategory}', [ParentalAllowedCategoryController::class, 'update'])->name('parental-allowed-categories.update');

        Route::get('/parental-blocked-products', [ParentalBlockedProductController::class, 'index'])->name('parental-blocked-products.index');
        Route::get('/parental-blocked-products/create', [ParentalBlockedProductController::class, 'create'])->name('parental-blocked-products.create');
        Route::post('/parental-blocked-products', [ParentalBlockedProductController::class, 'store'])->name('parental-blocked-products.store');
        Route::get('/parental-blocked-products/{parentalBlockedProduct}', [ParentalBlockedProductController::class, 'show'])->name('parental-blocked-products.show');
        Route::get('/parental-blocked-products/{parentalBlockedProduct}/edit', [ParentalBlockedProductController::class, 'edit'])->name('parental-blocked-products.edit');
        Route::put('/parental-blocked-products/{parentalBlockedProduct}', [ParentalBlockedProductController::class, 'update'])->name('parental-blocked-products.update');

        Route::get('/parental-preselected-orders', [ParentalPreselectedOrderController::class, 'index'])->name('parental-preselected-orders.index');
        Route::get('/parental-preselected-orders/create', [ParentalPreselectedOrderController::class, 'create'])->name('parental-preselected-orders.create');
        Route::post('/parental-preselected-orders', [ParentalPreselectedOrderController::class, 'store'])->name('parental-preselected-orders.store');
        Route::get('/parental-preselected-orders/{parentalPreselectedOrder}', [ParentalPreselectedOrderController::class, 'show'])->name('parental-preselected-orders.show');
        Route::get('/parental-preselected-orders/{parentalPreselectedOrder}/edit', [ParentalPreselectedOrderController::class, 'edit'])->name('parental-preselected-orders.edit');
        Route::put('/parental-preselected-orders/{parentalPreselectedOrder}', [ParentalPreselectedOrderController::class, 'update'])->name('parental-preselected-orders.update');
        Route::post('/parental-preselected-orders/{parentalPreselectedOrder}/items', [ParentalPreselectedOrderController::class, 'addItem'])->name('parental-preselected-orders.items.store');
        Route::put('/parental-preselected-orders/{parentalPreselectedOrder}/items/{item}', [ParentalPreselectedOrderController::class, 'updateItem'])->name('parental-preselected-orders.items.update');
        Route::delete('/parental-preselected-orders/{parentalPreselectedOrder}/items/{item}', [ParentalPreselectedOrderController::class, 'removeItem'])->name('parental-preselected-orders.items.destroy');

        Route::get('/purchase-authorizations', [PurchaseAuthorizationController::class, 'index'])->name('purchase-authorizations.index');
        Route::get('/purchase-authorizations/{purchaseAuthorization}', [PurchaseAuthorizationController::class, 'show'])->name('purchase-authorizations.show');

        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/{notification}', [NotificationController::class, 'show'])->name('notifications.show');
        Route::patch('/notifications/{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
        Route::patch('/notifications/{notification}/mark-as-unread', [NotificationController::class, 'markAsUnread'])->name('notifications.mark-as-unread');

        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('/audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit-logs.show');
    });

Route::prefix('tenant')
    ->name('tenant.')
    ->middleware(['auth', 'tenant.context', 'tenant.admin'])
    ->group(function () {
        Route::get('/dashboard', [TenantDashboardController::class, 'index'])->name('dashboard');
        Route::get('/schools', [TenantSchoolController::class, 'index'])->name('schools.index');
        Route::get('/schools/create', [TenantSchoolController::class, 'create'])->name('schools.create');
        Route::post('/schools', [TenantSchoolController::class, 'store'])->name('schools.store');
        Route::get('/schools/{school}', [TenantSchoolController::class, 'show'])->name('schools.show');
        Route::get('/schools/{school}/edit', [TenantSchoolController::class, 'edit'])->name('schools.edit');
        Route::put('/schools/{school}', [TenantSchoolController::class, 'update'])->name('schools.update');
        Route::delete('/schools/{school}', [TenantSchoolController::class, 'destroy'])->name('schools.destroy');
        Route::get('/students', [TenantStudentController::class, 'index'])->name('students.index');
        Route::get('/students/create', [TenantStudentController::class, 'create'])->name('students.create');
        Route::post('/students', [TenantStudentController::class, 'store'])->name('students.store');
        Route::get('/students/{student}/parents', [TenantStudentController::class, 'parents'])->name('students.parents');
        Route::get('/students/{student}', [TenantStudentController::class, 'show'])->name('students.show');
        Route::get('/students/{student}/edit', [TenantStudentController::class, 'edit'])->name('students.edit');
        Route::put('/students/{student}', [TenantStudentController::class, 'update'])->name('students.update');
        Route::delete('/students/{student}', [TenantStudentController::class, 'destroy'])->name('students.destroy');
        Route::get('/parent-invitations', [TenantParentInvitationController::class, 'index'])->name('parent-invitations.index');
        Route::get('/parent-invitations/create', [TenantParentInvitationController::class, 'create'])->name('parent-invitations.create');
        Route::post('/parent-invitations', [TenantParentInvitationController::class, 'store'])->name('parent-invitations.store');
        Route::get('/parent-invitations/{parentInvitation}', [TenantParentInvitationController::class, 'show'])->name('parent-invitations.show');
        Route::patch('/parent-invitations/{parentInvitation}/toggle', [TenantParentInvitationController::class, 'toggle'])->name('parent-invitations.toggle');
        Route::get('/requester-invitations', [TenantRequesterInvitationController::class, 'index'])->name('requester-invitations.index');
        Route::get('/requester-invitations/create', [TenantRequesterInvitationController::class, 'create'])->name('requester-invitations.create');
        Route::post('/requester-invitations', [TenantRequesterInvitationController::class, 'store'])->name('requester-invitations.store');
        Route::get('/requester-invitations/{requesterInvitation}', [TenantRequesterInvitationController::class, 'show'])->name('requester-invitations.show');
        Route::patch('/requester-invitations/{requesterInvitation}/toggle', [TenantRequesterInvitationController::class, 'toggle'])->name('requester-invitations.toggle');
        Route::get('/parents', [TenantParentController::class, 'index'])->name('parents.index');
        Route::get('/parents/create', [TenantParentController::class, 'create'])->name('parents.create');
        Route::post('/parents', [TenantParentController::class, 'store'])->name('parents.store');
        Route::get('/parents/{parent}/students', [TenantParentController::class, 'students'])->name('parents.students');
        Route::get('/parents/{parent}', [TenantParentController::class, 'show'])->name('parents.show');
        Route::get('/parents/{parent}/edit', [TenantParentController::class, 'edit'])->name('parents.edit');
        Route::put('/parents/{parent}', [TenantParentController::class, 'update'])->name('parents.update');
        Route::delete('/parents/{parent}', [TenantParentController::class, 'destroy'])->name('parents.destroy');
        Route::get('/student-parents', [TenantStudentParentController::class, 'index'])->name('student-parents.index');
        Route::get('/student-parents/create', [TenantStudentParentController::class, 'create'])->name('student-parents.create');
        Route::post('/student-parents', [TenantStudentParentController::class, 'store'])->name('student-parents.store');
        Route::get('/student-parents/{studentParent}', [TenantStudentParentController::class, 'show'])->name('student-parents.show');
        Route::get('/student-parents/{studentParent}/edit', [TenantStudentParentController::class, 'edit'])->name('student-parents.edit');
        Route::put('/student-parents/{studentParent}', [TenantStudentParentController::class, 'update'])->name('student-parents.update');
        Route::delete('/student-parents/{studentParent}', [TenantStudentParentController::class, 'destroy'])->name('student-parents.destroy');
        Route::get('/product-sections', [TenantProductSectionController::class, 'index'])->name('product-sections.index');
        Route::get('/product-sections/create', [TenantProductSectionController::class, 'create'])->name('product-sections.create');
        Route::post('/product-sections', [TenantProductSectionController::class, 'store'])->name('product-sections.store');
        Route::get('/product-sections/{productSection}', [TenantProductSectionController::class, 'show'])->name('product-sections.show');
        Route::get('/product-sections/{productSection}/edit', [TenantProductSectionController::class, 'edit'])->name('product-sections.edit');
        Route::put('/product-sections/{productSection}', [TenantProductSectionController::class, 'update'])->name('product-sections.update');
        Route::delete('/product-sections/{productSection}', [TenantProductSectionController::class, 'destroy'])->name('product-sections.destroy');
        Route::get('/product-categories', [TenantProductCategoryController::class, 'index'])->name('product-categories.index');
        Route::get('/product-categories/create', [TenantProductCategoryController::class, 'create'])->name('product-categories.create');
        Route::post('/product-categories', [TenantProductCategoryController::class, 'store'])->name('product-categories.store');
        Route::get('/product-categories/{productCategory}', [TenantProductCategoryController::class, 'show'])->name('product-categories.show');
        Route::get('/product-categories/{productCategory}/edit', [TenantProductCategoryController::class, 'edit'])->name('product-categories.edit');
        Route::put('/product-categories/{productCategory}', [TenantProductCategoryController::class, 'update'])->name('product-categories.update');
        Route::delete('/product-categories/{productCategory}', [TenantProductCategoryController::class, 'destroy'])->name('product-categories.destroy');
        Route::get('/products', [TenantProductController::class, 'index'])->name('products.index');
        Route::get('/products/create', [TenantProductController::class, 'create'])->name('products.create');
        Route::post('/products', [TenantProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/duplicate', [TenantProductController::class, 'duplicate'])->name('products.duplicate');
        Route::get('/products/{product}', [TenantProductController::class, 'show'])->name('products.show');
        Route::get('/products/{product}/edit', [TenantProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [TenantProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [TenantProductController::class, 'destroy'])->name('products.destroy');
        Route::get('/stocks', [TenantStockController::class, 'index'])->name('stocks.index');
        Route::get('/stocks/{stock}', [TenantStockController::class, 'show'])->name('stocks.show');
        Route::get('/stocks/{stock}/edit', [TenantStockController::class, 'edit'])->name('stocks.edit');
        Route::put('/stocks/{stock}', [TenantStockController::class, 'update'])->name('stocks.update');
        Route::delete('/stocks/{stock}', [TenantStockController::class, 'destroy'])->name('stocks.destroy');
        Route::post('/stocks/{stock}/adjust', [TenantStockController::class, 'adjust'])->name('stocks.adjust');
        Route::get('/stock-movements', [TenantStockController::class, 'movements'])->name('stock-movements.index');
        Route::get('/daily-menus', [TenantDailyMenuController::class, 'index'])->name('daily-menus.index');
        Route::get('/daily-menus/create', [TenantDailyMenuController::class, 'create'])->name('daily-menus.create');
        Route::post('/daily-menus', [TenantDailyMenuController::class, 'store'])->name('daily-menus.store');
        Route::get('/daily-menus/{dailyMenu}', [TenantDailyMenuController::class, 'show'])->name('daily-menus.show');
        Route::get('/daily-menus/{dailyMenu}/edit', [TenantDailyMenuController::class, 'edit'])->name('daily-menus.edit');
        Route::put('/daily-menus/{dailyMenu}', [TenantDailyMenuController::class, 'update'])->name('daily-menus.update');
        Route::delete('/daily-menus/{dailyMenu}', [TenantDailyMenuController::class, 'destroy'])->name('daily-menus.destroy');
        Route::post('/daily-menus/{dailyMenu}/items', [TenantDailyMenuController::class, 'addItem'])->name('daily-menus.items.store');
        Route::put('/daily-menus/{dailyMenu}/items/{item}', [TenantDailyMenuController::class, 'updateItem'])->name('daily-menus.items.update');
        Route::delete('/daily-menus/{dailyMenu}/items/{item}', [TenantDailyMenuController::class, 'removeItem'])->name('daily-menus.items.destroy');
        Route::get('/orders', [TenantOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/create', [TenantOrderController::class, 'create'])->name('orders.create');
        Route::post('/orders', [TenantOrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/{order}', [TenantOrderController::class, 'show'])->name('orders.show');
        Route::get('/orders/{order}/edit', [TenantOrderController::class, 'edit'])->name('orders.edit');
        Route::put('/orders/{order}', [TenantOrderController::class, 'update'])->name('orders.update');
        Route::delete('/orders/{order}', [TenantOrderController::class, 'destroy'])->name('orders.destroy');
        Route::post('/orders/{order}/items', [TenantOrderController::class, 'addItem'])->name('orders.items.store');
        Route::put('/orders/{order}/items/{item}', [TenantOrderController::class, 'updateItem'])->name('orders.items.update');
        Route::delete('/orders/{order}/items/{item}', [TenantOrderController::class, 'removeItem'])->name('orders.items.destroy');
        Route::patch('/orders/{order}/status', [TenantOrderController::class, 'updateStatus'])->name('orders.status.update');
        Route::get('/student-wallets', [TenantStudentWalletController::class, 'index'])->name('student-wallets.index');
        Route::get('/student-wallets/create', [TenantStudentWalletController::class, 'create'])->name('student-wallets.create');
        Route::post('/student-wallets', [TenantStudentWalletController::class, 'store'])->name('student-wallets.store');
        Route::get('/student-wallets/{studentWallet}', [TenantStudentWalletController::class, 'show'])->name('student-wallets.show');
        Route::get('/student-wallets/{studentWallet}/edit', [TenantStudentWalletController::class, 'edit'])->name('student-wallets.edit');
        Route::put('/student-wallets/{studentWallet}', [TenantStudentWalletController::class, 'update'])->name('student-wallets.update');
        Route::delete('/student-wallets/{studentWallet}', [TenantStudentWalletController::class, 'destroy'])->name('student-wallets.destroy');
        Route::get('/wallet-topups', [TenantWalletTopupController::class, 'index'])->name('wallet-topups.index');
        Route::get('/wallet-topups/{walletTopup}', [TenantWalletTopupController::class, 'show'])->name('wallet-topups.show');
        Route::patch('/wallet-topups/{walletTopup}/approve', [TenantWalletTopupController::class, 'approve'])->name('wallet-topups.approve');
        Route::patch('/wallet-topups/{walletTopup}/reject', [TenantWalletTopupController::class, 'reject'])->name('wallet-topups.reject');
        Route::get('/wallet-transactions', [TenantWalletTransactionController::class, 'index'])->name('wallet-transactions.index');
        Route::get('/wallet-transactions/{walletTransaction}', [TenantWalletTransactionController::class, 'show'])->name('wallet-transactions.show');
        Route::get('/student-tabs', [TenantStudentTabController::class, 'index'])->name('student-tabs.index');
        Route::get('/student-tabs/create', [TenantStudentTabController::class, 'create'])->name('student-tabs.create');
        Route::post('/student-tabs', [TenantStudentTabController::class, 'store'])->name('student-tabs.store');
        Route::get('/student-tabs/{studentTab}', [TenantStudentTabController::class, 'show'])->name('student-tabs.show');
        Route::get('/student-tabs/{studentTab}/edit', [TenantStudentTabController::class, 'edit'])->name('student-tabs.edit');
        Route::put('/student-tabs/{studentTab}', [TenantStudentTabController::class, 'update'])->name('student-tabs.update');
        Route::delete('/student-tabs/{studentTab}', [TenantStudentTabController::class, 'destroy'])->name('student-tabs.destroy');
        Route::get('/tab-entries', [TenantTabEntryController::class, 'index'])->name('tab-entries.index');
        Route::get('/tab-entries/create', [TenantTabEntryController::class, 'create'])->name('tab-entries.create');
        Route::post('/tab-entries', [TenantTabEntryController::class, 'store'])->name('tab-entries.store');
        Route::get('/tab-entries/{tabEntry}', [TenantTabEntryController::class, 'show'])->name('tab-entries.show');
        Route::get('/tab-entries/{tabEntry}/edit', [TenantTabEntryController::class, 'edit'])->name('tab-entries.edit');
        Route::put('/tab-entries/{tabEntry}', [TenantTabEntryController::class, 'update'])->name('tab-entries.update');
        Route::delete('/tab-entries/{tabEntry}', [TenantTabEntryController::class, 'destroy'])->name('tab-entries.destroy');
        Route::get('/payments', [TenantPaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/create', [TenantPaymentController::class, 'create'])->name('payments.create');
        Route::post('/payments', [TenantPaymentController::class, 'store'])->name('payments.store');
        Route::get('/payments/{payment}', [TenantPaymentController::class, 'show'])->name('payments.show');
        Route::get('/payments/{payment}/edit', [TenantPaymentController::class, 'edit'])->name('payments.edit');
        Route::put('/payments/{payment}', [TenantPaymentController::class, 'update'])->name('payments.update');
        Route::delete('/payments/{payment}', [TenantPaymentController::class, 'destroy'])->name('payments.destroy');

        Route::get('/parental-controls', [TenantParentalControlController::class, 'index'])->name('parental-controls.index');
        Route::get('/parental-controls/create', [TenantParentalControlController::class, 'create'])->name('parental-controls.create');
        Route::post('/parental-controls', [TenantParentalControlController::class, 'store'])->name('parental-controls.store');
        Route::get('/parental-controls/{parentalControl}', [TenantParentalControlController::class, 'show'])->name('parental-controls.show');
        Route::get('/parental-controls/{parentalControl}/edit', [TenantParentalControlController::class, 'edit'])->name('parental-controls.edit');
        Route::put('/parental-controls/{parentalControl}', [TenantParentalControlController::class, 'update'])->name('parental-controls.update');

        Route::get('/parental-allowed-categories', [TenantParentalAllowedCategoryController::class, 'index'])->name('parental-allowed-categories.index');
        Route::get('/parental-allowed-categories/create', [TenantParentalAllowedCategoryController::class, 'create'])->name('parental-allowed-categories.create');
        Route::post('/parental-allowed-categories', [TenantParentalAllowedCategoryController::class, 'store'])->name('parental-allowed-categories.store');
        Route::get('/parental-allowed-categories/{parentalAllowedCategory}', [TenantParentalAllowedCategoryController::class, 'show'])->name('parental-allowed-categories.show');
        Route::get('/parental-allowed-categories/{parentalAllowedCategory}/edit', [TenantParentalAllowedCategoryController::class, 'edit'])->name('parental-allowed-categories.edit');
        Route::put('/parental-allowed-categories/{parentalAllowedCategory}', [TenantParentalAllowedCategoryController::class, 'update'])->name('parental-allowed-categories.update');

        Route::get('/parental-blocked-products', [TenantParentalBlockedProductController::class, 'index'])->name('parental-blocked-products.index');
        Route::get('/parental-blocked-products/create', [TenantParentalBlockedProductController::class, 'create'])->name('parental-blocked-products.create');
        Route::post('/parental-blocked-products', [TenantParentalBlockedProductController::class, 'store'])->name('parental-blocked-products.store');
        Route::get('/parental-blocked-products/{parentalBlockedProduct}', [TenantParentalBlockedProductController::class, 'show'])->name('parental-blocked-products.show');
        Route::get('/parental-blocked-products/{parentalBlockedProduct}/edit', [TenantParentalBlockedProductController::class, 'edit'])->name('parental-blocked-products.edit');
        Route::put('/parental-blocked-products/{parentalBlockedProduct}', [TenantParentalBlockedProductController::class, 'update'])->name('parental-blocked-products.update');

        Route::get('/purchase-authorizations', [TenantPurchaseAuthorizationController::class, 'index'])->name('purchase-authorizations.index');
        Route::get('/purchase-authorizations/{purchaseAuthorization}', [TenantPurchaseAuthorizationController::class, 'show'])->name('purchase-authorizations.show');

        Route::get('/notifications', [TenantNotificationController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/{notification}', [TenantNotificationController::class, 'show'])->name('notifications.show');
        Route::patch('/notifications/{notification}/mark-as-read', [TenantNotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
        Route::patch('/notifications/{notification}/mark-as-unread', [TenantNotificationController::class, 'markAsUnread'])->name('notifications.mark-as-unread');

        Route::get('/audit-logs', [TenantAuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('/audit-logs/{auditLog}', [TenantAuditLogController::class, 'show'])->name('audit-logs.show');
    });

Route::prefix('operator')
    ->name('operator.')
    ->middleware(['auth', 'tenant.context', 'operator'])
    ->group(function () {
        Route::get('/dashboard', [OperatorDashboardController::class, 'index'])->name('dashboard');
        Route::get('/pos', [OperatorPosController::class, 'index'])->name('pos.index');
        Route::get('/pos/students', [OperatorPosController::class, 'searchStudents'])->name('pos.students');
        Route::post('/pos/checkout', [OperatorPosController::class, 'checkout'])->name('pos.checkout');
        Route::get('/orders', [OperatorOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/create', [OperatorOrderController::class, 'create'])->name('orders.create');
        Route::post('/orders', [OperatorOrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/{order}', [OperatorOrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/items', [OperatorOrderController::class, 'addItem'])->name('orders.items.store');
        Route::patch('/orders/{order}/status', [OperatorOrderController::class, 'updateStatus'])->name('orders.status.update');
        Route::get('/students', [OperatorStudentController::class, 'index'])->name('students.index');
        Route::get('/students/{student}', [OperatorStudentController::class, 'show'])->name('students.show');
        Route::get('/wallets', [OperatorWalletController::class, 'index'])->name('wallets.index');
        Route::get('/wallets/{wallet}', [OperatorWalletController::class, 'show'])->name('wallets.show');
    });

Route::prefix('parent')
    ->name('parent.')
    ->middleware(['auth', 'tenant.context', 'parent'])
    ->group(function () {
        Route::get('/', [ParentDashboardController::class, 'index'])->name('dashboard');
        Route::redirect('/dashboard', '/parent');
        Route::get('/children', [ParentChildController::class, 'index'])->name('children.index');
        Route::get('/children/create', [ParentChildController::class, 'create'])->name('children.create');
        Route::post('/children', [ParentChildController::class, 'store'])->name('children.store');
        Route::get('/children/{student}', [ParentChildController::class, 'show'])->name('children.show');
        Route::get('/children/{student}/tab', [ParentTabController::class, 'show'])->name('children.tab');
        Route::get('/children/{student}/edit', [ParentChildController::class, 'edit'])->name('children.edit');
        Route::put('/children/{student}', [ParentChildController::class, 'update'])->name('children.update');
        Route::get('/children/{student}/access', [ParentChildAccessController::class, 'show'])->name('children.access');
        Route::get('/children/{student}/controls', [ParentChildControlController::class, 'show'])->name('children.controls');
        Route::put('/children/{student}/controls', [ParentChildControlController::class, 'update'])->name('children.controls.update');
        Route::get('/children/{student}/menu', [ParentOrderController::class, 'menu'])->name('children.menu');
        Route::get('/children/{student}/orders/create', [ParentOrderController::class, 'checkout'])->name('children.orders.create');
        Route::post('/children/{student}/orders', [ParentOrderController::class, 'store'])->name('children.orders.store');
        Route::get('/self', [ParentSelfOrderController::class, 'setup'])->name('self.setup');
        Route::post('/self', [ParentSelfOrderController::class, 'enable'])->name('self.enable');
        Route::get('/self/menu', [ParentSelfOrderController::class, 'menu'])->name('self.menu');
        Route::get('/self/orders/create', [ParentSelfOrderController::class, 'checkout'])->name('self.orders.create');
        Route::post('/self/orders', [ParentSelfOrderController::class, 'store'])->name('self.orders.store');
        Route::get('/children/{student}/topups/create', [ParentWalletTopupController::class, 'create'])->name('topups.create');
        Route::post('/children/{student}/topups', [ParentWalletTopupController::class, 'store'])->name('topups.store');
        Route::get('/topups/{walletTopup}', [ParentWalletTopupController::class, 'show'])->name('topups.show');
        Route::post('/topups/{walletTopup}/receipt', [ParentWalletTopupController::class, 'receipt'])->name('topups.receipt');
        Route::get('/orders', [ParentOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/create', [ParentOrderController::class, 'create'])->name('orders.create');
        Route::get('/orders/{order}', [ParentOrderController::class, 'show'])->name('orders.show');
        Route::patch('/orders/{order}/cancel', [ParentOrderController::class, 'cancel'])->name('orders.cancel');
        Route::get('/tab', [ParentTabController::class, 'index'])->name('tab.index');
        Route::get('/account', [ParentAccountController::class, 'index'])->name('account');
    });

Route::prefix('student')
    ->name('student.')
    ->middleware(['auth', 'tenant.context', 'student'])
    ->group(function () {
        Route::get('/', [StudentDashboardController::class, 'index'])->name('dashboard');
        Route::redirect('/dashboard', '/student');
        Route::get('/menu', [StudentDashboardController::class, 'menu'])->name('menu');
        Route::get('/orders', [StudentDashboardController::class, 'orders'])->name('orders');
        Route::get('/orders/create', [StudentOrderController::class, 'create'])->name('orders.create');
        Route::post('/orders', [StudentOrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/{order}', [StudentOrderController::class, 'show'])->name('orders.show');
        Route::patch('/orders/{order}/cancel', [StudentOrderController::class, 'cancel'])->name('orders.cancel');
        Route::get('/account', [StudentDashboardController::class, 'account'])->name('account');
        Route::put('/account/pin', [StudentDashboardController::class, 'updatePin'])->name('account.pin');
    });

Route::prefix('requester')
    ->name('requester.')
    ->middleware(['auth', 'tenant.context', 'requester'])
    ->group(function () {
        Route::get('/', [RequesterDashboardController::class, 'index'])->name('dashboard');
        Route::redirect('/dashboard', '/requester');
        Route::get('/menu', [RequesterDashboardController::class, 'menu'])->name('menu');
        Route::get('/orders', [RequesterDashboardController::class, 'orders'])->name('orders');
        Route::get('/orders/create', [RequesterOrderController::class, 'create'])->name('orders.create');
        Route::post('/orders', [RequesterOrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/{order}', [RequesterOrderController::class, 'show'])->name('orders.show');
        Route::patch('/orders/{order}/cancel', [RequesterOrderController::class, 'cancel'])->name('orders.cancel');
        Route::get('/account', [RequesterDashboardController::class, 'account'])->name('account');
        Route::put('/account/pin', [RequesterDashboardController::class, 'updatePin'])->name('account.pin');
    });
