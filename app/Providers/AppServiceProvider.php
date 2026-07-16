<?php

namespace App\Providers;

use App\Domain\Cart\CartRepository;
use App\Domain\Catalog\ProductRepository;
use App\Domain\Checkout\Promotion;
use App\Domain\Checkout\ThresholdPromotion;
use App\Infrastructure\Cart\SessionCartRepository;
use App\Infrastructure\Catalog\CuratedProductRepository;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ProductRepository::class, CuratedProductRepository::class);
        $this->app->bind(CartRepository::class, SessionCartRepository::class);
        $this->app->singleton(Promotion::class, fn () => new ThresholdPromotion(30000, 10));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(UrlGenerator $url): void
    {
        if ($this->app->environment('production')) {
            $url->forceScheme('https');
        }
    }
}
