<?php

namespace App\Providers;

use App\Http\Controllers\CartController;
use App\Models\Order;
use App\Observers\OrderObserver;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('yamaha.layout', function ($view) {
            $view->with('cartCount', CartController::currentCount());
        });

        Order::observe(OrderObserver::class);
    }
}
