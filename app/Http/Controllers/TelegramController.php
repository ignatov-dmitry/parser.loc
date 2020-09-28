<?php

namespace App\Http\Controllers;

use App\Category;
use App\City;
use App\Country;
use App\Facades\TelegramBot;
use App\Filter;
use App\FilterVehicleModels;
use App\Region;
use App\TelegramUser;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{

    public function index(){
        $result = TelegramBot::getWebhookUpdates();

        //dd(property_exists(json_decode('{"action":"getBrand","offset":"50"}'), 'offset'));

//        $model = Category::where('id', '=', 2)->first();
//        $years = array();
//
//
//
//
//        for ($i = (int)$model->release_start; $i <= (int)$model->release_end; $i++){
//
//            for ($j = 0; $j < 3; $j++){
//                if ($i + $j <= (int)$model->release_end){
//                    $year = $i + $j;
//                    $years[$i][$j] =  array('text' => $year, 'callback_data'=>'{"action":"year","year":"' . $year . '"}');
//                }
//            }
//            $i = $i + $j - 1;
//
//
//        }
//
//
//        dd(array_values($years), array(
//            array(
//                array('text'=>'Бренд','callback_data'=>'{"action":"getBrand"}'),
//                array('text'=>'Модель','callback_data'=>'{"action":"setModel"}'),
//                array('text'=>'Год','callback_data'=>'{"action":"setYear"')
//            ),
//            array(
//                array('text'=>'Область','callback_data'=>'{"action":"getRegion"}'),
//                array('text'=>'Город','callback_data'=>'{"action":"getCity"}'),
//                array('text'=>'Страна','callback_data'=>'{"action":"getCountry"}')
//            )
//        ));


        if (isset($result['message'])){
            $text = $result["message"]["text"]; //Текст сообщения
            $chat_id = $result["message"]["chat"]["id"]; //Уникальный идентификатор пользователя
            $name = isset($result["message"]["from"]["username"]) ? $result["message"]["from"]["username"] : 'NO NAME';
        }

        if (isset($result['callback_query'])){
            Log::debug(json_encode($result));
            $btns = array();

            $reply = '';
            $chat_id = $result['callback_query']["message"]["chat"]["id"]; //Уникальный идентификатор пользователя
            $filter = Filter::where('chat_id', '=', $chat_id)->first();
            $btns = array();
            \Telegram::bot('carpars_bot')->answerCallbackQuery([
                'callback_query_id' => $result['callback_query']['id'],
                'text' => $result['callback_query']['data']
            ]);
            $data = json_decode(str_replace('\\', '', $result['callback_query']['data']));
            switch ($data->action) {
                case 'getBrand':
                    $reply = 'Выберите марку';
                    if (property_exists($data, 'offset')){
                        $btns = TelegramBot::keyboardFromModel(Category::where('parent_id', '=', 0)->offset($data->offset)->limit(50)->get(), 'brand');
                        $offset = $data->offset + 50;
                        $btns = array_merge($btns, array(array(array("text" => "Показать ещё", "callback_data" => '{"action":"getBrand","offset":"' . $offset . '"}'))));
                    }
                    else{
                        $btns = TelegramBot::keyboardFromModel(Category::where('parent_id', '=', 0)->limit(50)->get(), 'brand');
                        $btns = array_merge($btns, array(array(array("text" => "Показать ещё", "callback_data" => '{"action":"getBrand","offset":"50"}'))));
                    }
                    break;


                case 'brand':
                    $filter = Filter::whereChatId($chat_id)->first();
                    $filter->brand = $data->id;
                    $filter->save();

                    $reply = 'Выберите модель';
                    $btns = TelegramBot::keyboardFromModel(Category::where('parent_id', '=', $data->id)->get(), 'model');
                    break;




                case 'model':
                    $filter = Filter::whereChatId($chat_id)->first();
                    if (!FilterVehicleModels::where('filter_id', '=', $filter->id)->where('category_id', '=', $data->id)->first()){
                        FilterVehicleModels::insertOrIgnore([
                            'filter_id' => $filter->id,
                            'category_id' => $data->id
                        ]);
                    }

                    $reply = 'Выберите минимальный год производства';
                    $model = Category::where('id', '=', $data->id)->first();
                    $years = array();




                    for ($i = (int)$model->release_start; $i <= (int)$model->release_end; $i++){

                        for ($j = 0; $j < 3; $j++){
                            if ($i + $j <= (int)$model->release_end){
                                $year = $i + $j;
                                $years[$i][$j] =  array('text' => $year, 'callback_data'=>'{"action":"year","year":"' . $year . '"}');
                            }
                        }
                        $i = $i + $j - 1;


                    }

                    $btns = array_values($years);




                    break;






                case 'getYear':
                    $reply = 'Выберите год';

                    break;



                case 'getCountry':
                    $reply = 'Выберите страну';
                    $btns = TelegramBot::keyboardFromModel(Country::all(), 'country');
                    break;

                case 'country':
                    $reply = 'Выберите регион';
                    $filter = Filter::whereChatId($chat_id)->first();
                    $filter->country_id = $data->id;
                    $filter->save();
                    $btns = TelegramBot::keyboardFromModel(Region::where('country_id', '=', $data->id)->get(), 'region');

                    break;



                case 'region':
                    $reply = 'Выберите город';
                    $filter = Filter::whereChatId($chat_id)->first();
                    $filter->region_id = $data->id;
                    $filter->save();
                    $btns = TelegramBot::keyboardFromModel(City::where('region_id', '=', $data->id)->get(),'city' );
                    break;

                case 'city':
                    $reply = 'Местоположение выбрано';
                    $filter = Filter::whereChatId($chat_id)->first();
                    $filter->city_id = $data->id;
                    $filter->save();
                    break;


            }

            TelegramBot::sendMessage($chat_id, $reply, array('inline_keyboard' => $btns));

        }



        if(isset($text)){
            if ($text == "/start") {
                $name = isset($result["message"]["from"]["username"]) ? $result["message"]["from"]["username"] : 'NO NAME'; //Юзернейм пользователя
                $reply = "Добро пожаловать в бота " . $name . ";";
                TelegramBot::sendMessage($chat_id, $reply);
            }
            elseif ($text === "/help"){
                $reply = "Помощь";
                TelegramBot::sendMessage($chat_id, $reply);
            }
            elseif ($text === "/register"){
                TelegramUser::insert(array('chat_id' => $chat_id));

                $reply = "Вы успешно зарегестрированны " . $name . ";";
                TelegramBot::sendMessage($chat_id, $reply);
            }
            elseif ($text === "/add_filter"){
                $reply = "Добавить фильтр";



                if (!$filter_id = Filter::where('chat_id', '=', $chat_id)->first()){
                    Filter::insert(array(
                        'chat_id' => $chat_id,
                    ));
                }



                TelegramBot::sendMessage($chat_id, $reply, array('inline_keyboard' => array(
                    array(
                        array('text'=>'Поиск по маркам машин','callback_data'=>'{"action":"getBrand"}')
                    ),
                    array(
                        array('text'=>'Задать местоположение','callback_data'=>'{"action":"getCountry"}')
                    )
                )));
            }
        }
    }





}
