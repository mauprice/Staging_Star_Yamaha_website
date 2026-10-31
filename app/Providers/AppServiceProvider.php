<?php

namespace App\Providers;

use App\Http\Controllers\CartController;
use App\Models\Order;
use App\Observers\OrderObserver;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
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

        // Staff flagged pos_shares_admin_login don't need a separate POS
        // login - logging into the admin (web guard) session transparently
        // authenticates them on the POS guard too, and signing out of admin
        // signs them out of POS in the same step. Everyone else still gets
        // POS's own independent login screen.
        Event::listen(function (Login $event) {
            if ($event->guard === 'web' && $event->user->can_access_pos && $event->user->pos_shares_admin_login) {
                Auth::guard('pos')->login($event->user);
            }
        });

        Event::listen(function (Logout $event) {
            if ($event->guard === 'web' && $event->user?->pos_shares_admin_login) {
                Auth::guard('pos')->logout();
            }
        });
    }
}
