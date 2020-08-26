<?php

namespace App\Http\Controllers;

use App\Category;
use App\Contracts\IParser;
use App\TelegramUser;
use App\Vehicle;
use Illuminate\Http\Request;
use App\Facades\TelegramBot;
class CronController extends Controller
{
    private $parser;

    public function __construct(IParser $parser)
    {
        $this->parser = $parser;
    }

    public function parse(){
        $start = microtime(true);
        $categories = Category::where('parent_id', '!=', 0)->get();
        $telegramUsers = TelegramUser::all();
        foreach ($categories as $category){
            $cars = $this->parser->getCarList($category->id, $category->url);
            if ($cars['items']){
                $txt = array_map(function ($item){
                    return $item['url'];
                }, $cars['items']);
                foreach ($telegramUsers as $telegramUser) {
                    TelegramBot::sendMessage($telegramUser->chat_id, implode('\n ', $txt));
                }
                Vehicle::insert($cars['items']);
            }
        }

        dd(round(microtime(true) - $start, 2));
    }
}
