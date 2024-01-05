<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        view()->composer(['layouts.partials.sidebar'], function ($view) {

            $avatar = asset('assets/img/default-avatar.png');

            $sidebar_image = asset('');

            $color = 'white';
            $btn_color = 'purple';


            $view->with('current_route', Route::current())
                ->with('avatar', $avatar)
                ->with('sidebar_image', $sidebar_image)
                ->with('color', $color)
                ->with('btn_color', $btn_color);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
