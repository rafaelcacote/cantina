<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\TenantController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SchoolController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\ParentController;
use App\Http\Controllers\Admin\StudentParentController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductSectionController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\DailyMenuController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\StudentWalletController;
use App\Http\Controllers\Admin\WalletTransactionController;
use App\Http\Controllers\Admin\StudentTabController;
use App\Http\Controllers\Admin\TabEntryController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ParentalControlController;
use App\Http\Controllers\Admin\ParentalAllowedCategoryController;
use App\Http\Controllers\Admin\ParentalBlockedProductController;
use App\Http\Controllers\Admin\ParentalPreselectedOrderController;
use App\Http\Controllers\Admin\PurchaseAuthorizationController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Tenant\DashboardController as TenantDashboardController;
use App\Http\Controllers\Tenant\SchoolController as TenantSchoolController;
use App\Http\Controllers\Tenant\StudentController as TenantStudentController;
use App\Http\Controllers\Tenant\ParentController as TenantParentController;
use App\Http\Controllers\Tenant\ProductController as TenantProductController;
use App\Http\Controllers\Tenant\StockController as TenantStockController;
use App\Http\Controllers\Tenant\DailyMenuController as TenantDailyMenuController;
use App\Http\Controllers\Tenant\OrderController as TenantOrderController;
use App\Http\Controllers\Tenant\StudentWalletController as TenantStudentWalletController;
use App\Http\Controllers\Tenant\WalletTransactionController as TenantWalletTransactionController;
use App\Http\Controllers\Tenant\StudentTabController as TenantStudentTabController;
use App\Http\Controllers\Tenant\TabEntryController as TenantTabEntryController;
use App\Http\Controllers\Tenant\PaymentController as TenantPaymentController;
use App\Http\Controllers\Tenant\ProductSectionController as TenantProductSectionController;
use App\Http\Controllers\Tenant\ProductCategoryController as TenantProductCategoryController;
use App\Http\Controllers\Tenant\StudentParentController as TenantStudentParentController;

Route::middleware('guest')->group(function () {
    Route::redirect('/login', '/signin')->name('login');

    // authentication pages
    Route::get('/signin', [AuthenticatedSessionController::class, 'create'])->name('signin');
    Route::post('/signin', [AuthenticatedSessionController::class, 'store'])->name('signin.store');

    Route::get('/signup', function () {
        return view('pages.auth.signup', ['title' => 'Sign Up']);
    })->name('signup');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'tenant.context'])->group(function () {
    // dashboard pages
    Route::get('/', function () {
        if (Auth::user()?->user_type === 'tenant_admin') {
            return redirect()->route('tenant.dashboard');
        }

        return view('pages.dashboard.ecommerce', ['title' => 'E-commerce Dashboard']);
    })->name('dashboard');

    // calender pages
    Route::get('/calendar', function () {
        return view('pages.calender', ['title' => 'Calendar']);
    })->name('calendar');

    // profile pages
    Route::get('/profile', function () {
        return view('pages.profile', ['title' => 'Profile']);
    })->name('profile');

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

        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/create', [PaymentController::class, 'create'])->name('payments.create');
        Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
        Route::get('/payments/{payment}/edit', [PaymentController::class, 'edit'])->name('payments.edit');
        Route::put('/payments/{payment}', [PaymentController::class, 'update'])->name('payments.update');

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
        Route::get('/students', [TenantStudentController::class, 'index'])->name('students.index');
        Route::get('/students/create', [TenantStudentController::class, 'create'])->name('students.create');
        Route::post('/students', [TenantStudentController::class, 'store'])->name('students.store');
        Route::get('/students/{student}', [TenantStudentController::class, 'show'])->name('students.show');
        Route::get('/students/{student}/edit', [TenantStudentController::class, 'edit'])->name('students.edit');
        Route::put('/students/{student}', [TenantStudentController::class, 'update'])->name('students.update');
        Route::get('/parents', [TenantParentController::class, 'index'])->name('parents.index');
        Route::get('/parents/create', [TenantParentController::class, 'create'])->name('parents.create');
        Route::post('/parents', [TenantParentController::class, 'store'])->name('parents.store');
        Route::get('/parents/{parent}', [TenantParentController::class, 'show'])->name('parents.show');
        Route::get('/parents/{parent}/edit', [TenantParentController::class, 'edit'])->name('parents.edit');
        Route::put('/parents/{parent}', [TenantParentController::class, 'update'])->name('parents.update');
        Route::get('/student-parents', [TenantStudentParentController::class, 'index'])->name('student-parents.index');
        Route::get('/student-parents/create', [TenantStudentParentController::class, 'create'])->name('student-parents.create');
        Route::post('/student-parents', [TenantStudentParentController::class, 'store'])->name('student-parents.store');
        Route::get('/student-parents/{studentParent}', [TenantStudentParentController::class, 'show'])->name('student-parents.show');
        Route::get('/student-parents/{studentParent}/edit', [TenantStudentParentController::class, 'edit'])->name('student-parents.edit');
        Route::put('/student-parents/{studentParent}', [TenantStudentParentController::class, 'update'])->name('student-parents.update');
        Route::get('/product-sections', [TenantProductSectionController::class, 'index'])->name('product-sections.index');
        Route::get('/product-sections/create', [TenantProductSectionController::class, 'create'])->name('product-sections.create');
        Route::post('/product-sections', [TenantProductSectionController::class, 'store'])->name('product-sections.store');
        Route::get('/product-sections/{productSection}', [TenantProductSectionController::class, 'show'])->name('product-sections.show');
        Route::get('/product-sections/{productSection}/edit', [TenantProductSectionController::class, 'edit'])->name('product-sections.edit');
        Route::put('/product-sections/{productSection}', [TenantProductSectionController::class, 'update'])->name('product-sections.update');
        Route::get('/product-categories', [TenantProductCategoryController::class, 'index'])->name('product-categories.index');
        Route::get('/product-categories/create', [TenantProductCategoryController::class, 'create'])->name('product-categories.create');
        Route::post('/product-categories', [TenantProductCategoryController::class, 'store'])->name('product-categories.store');
        Route::get('/product-categories/{productCategory}', [TenantProductCategoryController::class, 'show'])->name('product-categories.show');
        Route::get('/product-categories/{productCategory}/edit', [TenantProductCategoryController::class, 'edit'])->name('product-categories.edit');
        Route::put('/product-categories/{productCategory}', [TenantProductCategoryController::class, 'update'])->name('product-categories.update');
        Route::get('/products', [TenantProductController::class, 'index'])->name('products.index');
        Route::get('/products/create', [TenantProductController::class, 'create'])->name('products.create');
        Route::post('/products', [TenantProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}', [TenantProductController::class, 'show'])->name('products.show');
        Route::get('/products/{product}/edit', [TenantProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [TenantProductController::class, 'update'])->name('products.update');
        Route::get('/stocks', [TenantStockController::class, 'index'])->name('stocks.index');
        Route::get('/stocks/{stock}', [TenantStockController::class, 'show'])->name('stocks.show');
        Route::get('/stocks/{stock}/edit', [TenantStockController::class, 'edit'])->name('stocks.edit');
        Route::put('/stocks/{stock}', [TenantStockController::class, 'update'])->name('stocks.update');
        Route::post('/stocks/{stock}/adjust', [TenantStockController::class, 'adjust'])->name('stocks.adjust');
        Route::get('/stock-movements', [TenantStockController::class, 'movements'])->name('stock-movements.index');
        Route::get('/daily-menus', [TenantDailyMenuController::class, 'index'])->name('daily-menus.index');
        Route::get('/daily-menus/create', [TenantDailyMenuController::class, 'create'])->name('daily-menus.create');
        Route::post('/daily-menus', [TenantDailyMenuController::class, 'store'])->name('daily-menus.store');
        Route::get('/daily-menus/{dailyMenu}', [TenantDailyMenuController::class, 'show'])->name('daily-menus.show');
        Route::get('/daily-menus/{dailyMenu}/edit', [TenantDailyMenuController::class, 'edit'])->name('daily-menus.edit');
        Route::put('/daily-menus/{dailyMenu}', [TenantDailyMenuController::class, 'update'])->name('daily-menus.update');
        Route::post('/daily-menus/{dailyMenu}/items', [TenantDailyMenuController::class, 'addItem'])->name('daily-menus.items.store');
        Route::put('/daily-menus/{dailyMenu}/items/{item}', [TenantDailyMenuController::class, 'updateItem'])->name('daily-menus.items.update');
        Route::delete('/daily-menus/{dailyMenu}/items/{item}', [TenantDailyMenuController::class, 'removeItem'])->name('daily-menus.items.destroy');
        Route::get('/orders', [TenantOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/create', [TenantOrderController::class, 'create'])->name('orders.create');
        Route::post('/orders', [TenantOrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/{order}', [TenantOrderController::class, 'show'])->name('orders.show');
        Route::get('/orders/{order}/edit', [TenantOrderController::class, 'edit'])->name('orders.edit');
        Route::put('/orders/{order}', [TenantOrderController::class, 'update'])->name('orders.update');
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
        Route::get('/wallet-transactions', [TenantWalletTransactionController::class, 'index'])->name('wallet-transactions.index');
        Route::get('/wallet-transactions/{walletTransaction}', [TenantWalletTransactionController::class, 'show'])->name('wallet-transactions.show');
        Route::get('/student-tabs', [TenantStudentTabController::class, 'index'])->name('student-tabs.index');
        Route::get('/student-tabs/create', [TenantStudentTabController::class, 'create'])->name('student-tabs.create');
        Route::post('/student-tabs', [TenantStudentTabController::class, 'store'])->name('student-tabs.store');
        Route::get('/student-tabs/{studentTab}', [TenantStudentTabController::class, 'show'])->name('student-tabs.show');
        Route::get('/student-tabs/{studentTab}/edit', [TenantStudentTabController::class, 'edit'])->name('student-tabs.edit');
        Route::put('/student-tabs/{studentTab}', [TenantStudentTabController::class, 'update'])->name('student-tabs.update');
        Route::get('/tab-entries', [TenantTabEntryController::class, 'index'])->name('tab-entries.index');
        Route::get('/tab-entries/create', [TenantTabEntryController::class, 'create'])->name('tab-entries.create');
        Route::post('/tab-entries', [TenantTabEntryController::class, 'store'])->name('tab-entries.store');
        Route::get('/tab-entries/{tabEntry}', [TenantTabEntryController::class, 'show'])->name('tab-entries.show');
        Route::get('/tab-entries/{tabEntry}/edit', [TenantTabEntryController::class, 'edit'])->name('tab-entries.edit');
        Route::put('/tab-entries/{tabEntry}', [TenantTabEntryController::class, 'update'])->name('tab-entries.update');
        Route::get('/payments', [TenantPaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/create', [TenantPaymentController::class, 'create'])->name('payments.create');
        Route::post('/payments', [TenantPaymentController::class, 'store'])->name('payments.store');
        Route::get('/payments/{payment}', [TenantPaymentController::class, 'show'])->name('payments.show');
        Route::get('/payments/{payment}/edit', [TenantPaymentController::class, 'edit'])->name('payments.edit');
        Route::put('/payments/{payment}', [TenantPaymentController::class, 'update'])->name('payments.update');
    });






















