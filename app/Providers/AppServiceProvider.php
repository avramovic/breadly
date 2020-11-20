<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
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
        Schema::defaultStringLength(191);

        Validator::extend('absent', function ($attribute, $value, $parameters, $validator) {
            return false;
        });

        $appUrl = \Config::get('app.url');

        if (rtrim($appUrl, '/') != 'http://localhost') {
            \URL::forceRootUrl($appUrl);
        }

        if (\Illuminate\Support\Str::contains($appUrl, 'https://')) {
            \URL::forceScheme('https');
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
