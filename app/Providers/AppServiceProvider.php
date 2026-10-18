<?php

namespace App\Providers;

use App\Contracts\PaymentGateway;
use App\Services\Gateways\FakeGateway;
use App\Services\Gateways\RazorpayGateway;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PaymentGateway::class, function () {
            return config('services.payment_gateway') === 'razorpay'
                ? new RazorpayGateway()
                : new FakeGateway();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
