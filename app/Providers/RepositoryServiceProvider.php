<?php

namespace App\Providers;

use App\Interfaces\CountryRepositoryInterface;
use App\Interfaces\OrderRepositoryInterface;
use App\Interfaces\TripRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Repositories\CountryRepository;
use App\Repositories\OrderRepository;
use App\Repositories\TripRepository;
use App\Repositories\UserRepository;
// use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(CountryRepositoryInterface::class, CountryRepository::class);
        $this->app->bind(TripRepositoryInterface::class, TripRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);

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
