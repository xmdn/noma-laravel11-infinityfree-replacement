<?php

use App\Domain\Identity\PermissionName;
use App\Application\Checkout\ConvertCartToOrder;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductModerationController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\ShopController;
use App\Http\Controllers\Admin\ShopSettingsController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\OwnerRegistrationController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\TenantSessionBridgeController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\CheckoutSummaryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Owner\OnboardingController;
use App\Livewire\CustomerCheckout;
use App\Livewire\Storefront;
use App\Models\Cart;
use App\Models\Shop;
use Illuminate\Support\Facades\Route;

$centralHost = parse_url(config('app.url'), PHP_URL_HOST) ?: 'localhost';

Route::domain('{shop}.'.$centralHost)
    ->middleware('shop.tenant')
    ->group(function (): void {
        Route::get('/', Storefront::class)->name('shops.show');
        Route::get('/checkout', function (string $shop) {
            if (! auth()->check()) {
                return redirect()->route('shops.checkout.login', ['shop' => $shop]);
            }

            $cart = Cart::query()
                ->where('session_id', session()->getId())
                ->where('status', 'active')
                ->where('expires_at', '>', now())
                ->with('items')
                ->first();

            if ($cart instanceof Cart) {
                $orderId = app(ConvertCartToOrder::class)($cart, auth()->user());

                if (is_string($orderId)) {
                    session()->put('checkout_order_id', $orderId);
                }
            }

            session()->put('checkout_user_id', auth()->id());
            session()->put('checkout_user_access_expires_at', now()->addMinutes(max(1, (int) config('noma.customer_access_ttl_minutes', 3)))->timestamp);

            return redirect()->route('shops.checkout.summary', ['shop' => $shop]);
        })->name('shops.checkout');
        Route::get('/checkout/login', CustomerCheckout::class)
            ->defaults('mode', 'login')
            ->name('shops.checkout.login');
        Route::get('/checkout/register', CustomerCheckout::class)
            ->defaults('mode', 'register')
            ->name('shops.checkout.register');
        Route::get('/checkout/summary', CheckoutSummaryController::class)->name('shops.checkout.summary');
        Route::get('/auth/bridge/{user}', TenantSessionBridgeController::class)
            ->middleware(['signed', 'throttle:6,1'])
            ->name('shops.auth.bridge');
    });

Route::get('/shops/{shop:slug}', fn (Shop $shop) => redirect()->away($shop->publicUrl()))
    ->name('shops.legacy');

Route::get('/', function () {
    return view('welcome', [
        'shops' => Shop::query()
            ->withCount(['products' => fn ($query) => $query->where('status', 'active')])
            ->orderByDesc('created_at')
            ->limit(12)
            ->get(),
    ]);
})->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('throttle:6,1');
    Route::get('/sell/register', [OwnerRegistrationController::class, 'create'])->name('owner.register');
    Route::post('/sell/register', [OwnerRegistrationController::class, 'store'])->middleware('throttle:6,1');
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:6,1');
});

Route::get('/email/verify/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/email/verify', EmailVerificationPromptController::class)->name('verification.notice');
    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
    Route::post('/owner/onboarding/retry', [OnboardingController::class, 'retry'])
        ->middleware(['verified', 'role:owner'])
        ->name('owner.onboarding.retry');

    Route::prefix('admin')
        ->name('admin.')
        ->middleware(['verified', 'permission:'.PermissionName::AccessAdmin->value])
        ->group(function (): void {
            Route::get('/', AdminDashboardController::class)->name('dashboard');
            Route::get('/shop/settings', [ShopSettingsController::class, 'edit'])
                ->middleware('permission:'.PermissionName::ManageShopSettings->value)
                ->name('shop.settings.edit');
            Route::patch('/shop/settings', [ShopSettingsController::class, 'update'])
                ->middleware('permission:'.PermissionName::ManageShopSettings->value)
                ->name('shop.settings.update');
            Route::resource('categories', CategoryController::class)
                ->except(['show'])
                ->middleware('permission:'.PermissionName::ManageCatalog->value);
            Route::resource('promotions', PromotionController::class)
                ->except(['show', 'destroy'])
                ->middleware('permission:'.PermissionName::ManageOrders->value);
            Route::resource('products', ProductController::class)
                ->except(['show']);
            Route::resource('shops', ShopController::class)
                ->only(['index', 'update'])
                ->middleware('permission:'.PermissionName::ManagePlatformShops->value);
            Route::patch('/products/{product}/moderation', [ProductModerationController::class, 'update'])
                ->middleware('permission:'.PermissionName::ModeratePlatformProducts->value)
                ->name('products.moderation.update');
            Route::resource('users', AdminUserController::class)
                ->only(['index', 'edit', 'update'])
                ->middleware('permission:'.PermissionName::ManageUsers->value);
        });
});
