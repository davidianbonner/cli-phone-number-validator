<?php

namespace App\Providers;

use App\PhoneNumberValidator;
use App\LibPhoneNumberValidator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(PhoneNumberValidator::class, function ($app) {
            return LibPhoneNumberValidator::getInstance();
        });
    }
}
