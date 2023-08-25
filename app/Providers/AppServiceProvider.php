<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(Request::class, function ($app) {
            $request = Request::createFrom($app['request']);
            $request->merge([
                'per_page' => $request->input('per_page', 20), // Set the default value of per_page to 10
            ]);
            return $request;
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
