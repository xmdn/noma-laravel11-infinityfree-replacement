<?php

namespace App\Providers;

use App\Domain\Cart\CartRepository;
use App\Domain\Catalog\ProductRepository;
use App\Domain\Checkout\Promotion;
use App\Infrastructure\Checkout\DatabasePromotion;
use App\Infrastructure\Cart\DatabaseCartRepository;
// use App\Infrastructure\Cart\SessionCartRepository;
use App\Infrastructure\Catalog\DatabaseProductRepository;
use App\Listeners\ProvisionOwnerShopAfterVerification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ProductRepository::class, DatabaseProductRepository::class);
        // $this->app->bind(CartRepository::class, SessionCartRepository::class);
        $this->app->bind(CartRepository::class, DatabaseCartRepository::class);
        $this->app->singleton(DatabasePromotion::class);
        $this->app->singleton(Promotion::class, fn ($app) => $app->make(DatabasePromotion::class));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(UrlGenerator $url): void
    {
        Event::listen(Verified::class, ProvisionOwnerShopAfterVerification::class);

        $rootUrl = $this->applicationRootUrl();

        if ($rootUrl !== null) {
            $url->forceRootUrl($rootUrl);
        }

        if ($this->app->environment('production') || str_starts_with((string) $rootUrl, 'https://')) {
            $url->forceScheme('https');
        }
    }

    private function applicationRootUrl(): ?string
    {
        $configuredUrl = rtrim(trim((string) config('app.url')), '/');
        $codespaceName = trim((string) env('CODESPACE_NAME'));
        $codespacesDomain = trim((string) env('GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN'), '/');
        $appPort = trim((string) env('NOMA_APP_PORT', '8083'));

        if (
            $codespaceName !== ''
            && $codespacesDomain !== ''
            && $appPort !== ''
            && ($configuredUrl === '' || str_starts_with($configuredUrl, 'http://localhost'))
        ) {
            return "https://{$codespaceName}-{$appPort}.{$codespacesDomain}";
        }

        return $configuredUrl !== '' ? $configuredUrl : null;
    }
}
