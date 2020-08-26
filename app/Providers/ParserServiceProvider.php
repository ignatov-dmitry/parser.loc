<?php

namespace App\Providers;

use App\Contracts\IParser;
use App\Http\Controllers\CronController;
use App\Http\Controllers\SettingController;
use App\Parser\AVBY;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\DomCrawler\Crawler;

class ParserServiceProvider extends ServiceProvider
{

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {

        $this->app->when(SettingController::class)
        ->needs(IParser::class)
        ->give(function (){
            return new AVBY();
        });
        $this->app->when(CronController::class)
            ->needs(IParser::class)
            ->give(function (){
                return new AVBY();
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
