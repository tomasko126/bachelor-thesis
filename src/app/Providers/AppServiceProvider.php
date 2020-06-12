<?php

namespace App\Providers;

use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Add 'not_present' validation rule, which checks whether the given attribute is not present in the request data
        Validator::extendImplicit('not_present', function ($attribute, $value, $parameters, $validator) {
            return !array_key_exists($attribute, $validator->getData());
        });

        $this->app->singleton(FakerGenerator::class, function () {
            return FakerFactory::create('en_US');
        });
    }
}
