<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Contracts\PaymentGateway::class,
            \App\Services\StripePaymentGateway::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::shouldBeStrict();

        $this->configureRateLimiting();
        $this->configurePulseAuthorization();
    }

    /**
     * Configure Pulse dashboard authorization.
     */
    protected function configurePulseAuthorization(): void
    {
        Gate::define('viewPulse', function (User $user) {
            // Only admin users can view the Pulse dashboard
            return $user->hasRole('admin');
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // Limit donations to 10 per minute per IP address
        RateLimiter::for('donations', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });
    }
}
