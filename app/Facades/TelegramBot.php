<?php


namespace App\Facades;



use Illuminate\Support\Facades\Facade;

class TelegramBot extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'TelegramBot';
    }
}
