<?php

namespace App\Providers;

use App\Src\NumberLleters;
use Illuminate\Support\ServiceProvider;
use SunatHelper;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(NumberLleters::class, function(){
            return new NumberLleters();
        });

        $this->app->bind(SunatHelper::class, function(){
            return new SunatHelper();
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
