<?php
namespace App\Providers;

use App\Observers\VehicleObserver;
use App\Vehicle;
use Illuminate\Support\ServiceProvider;

class VehicleServiceProvide extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Vehicle::observe(VehicleObserver::class);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
