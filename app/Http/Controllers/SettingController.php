<?php

namespace App\Http\Controllers;

use App\Category;
use App\TelegramUser;
use App\Vehicle;
use Illuminate\Http\Request;
use App\Facades\TelegramBot;
use App\Facades\AVBY;
class SettingController extends Controller
{
    public function index(){
        return view('settings.index');
    }


    public function loadCategoriesAvBy(){
        return AVBY::loadCategories();
    }


    //Импорт категорий в БД
    function importCategory(Request $request){
        AVBY::importCategory($request);
    }


    public function getCategories(){
        $categories = Category::all();
//        $categories = Cache::rememberForever('categories', function () {
//            return Category::all();
//        });
//        $categories = Cache::get('categories');
        $childCategories = array();
        foreach ($categories as $category) {
            if ($category['parent_id'] !== 0){
                $childCategories[] = $category;
            }
        }
        return response()->json($childCategories);
    }


    public function getTable(){
//        $categories = Cache::rememberForever('categories', function () {
//            return Category::all();
//        });
        $categories = Category::all();
        return view('partials.categories', array(
            'categories' => $categories
        ));
    }

    //Импорт машин в БД
    public function importCars(Request $request) {
        $start = microtime(true);
        $cars = AVBY::getCarList($request->id, $request->url);
        $telegramUsers = TelegramUser::all();
        if ($cars['items']){
            $txt = array_map(function ($item){
                return $item['url'];
            }, $cars['items']);

            foreach ($telegramUsers as $telegramUser) {
                TelegramBot::sendMessage($telegramUser->chat_id, implode(',', $txt));
            }
            //TelegramBot::sendMessage(518575553, implode('\n', $txt));
            Vehicle::insert($cars['items']);
        }
        return response()->json(array(
            'id'    => $request->id,
            'count' => $cars['items'] ? count($cars['items']) : 0,
            'time'  => round(microtime(true) - $start, 2)
        ));
    }
}
