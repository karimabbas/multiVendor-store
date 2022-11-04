<?php

namespace App\Providers;

use App\Repositories\Cart\CartModelRepository;
use App\Repositories\Cart\CartRepository;
use Illuminate\Support\ServiceProvider;

class CartServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // (1)===>> can pass name 'cart' and using this name in controller

        // $this->app->bind('cart', function () {
        //     return new CartModelRepository();
        // });

        // (2)===>>pass the class itself and pass it as argument to controller

        $this->app->bind(CartRepository::class, function () {
            return new CartModelRepository();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
