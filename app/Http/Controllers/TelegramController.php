<?php

namespace App\Http\Controllers;

use App\Category;
use App\City;
use App\Country;
use App\Facades\TelegramBot;
use App\Filter;
use App\FilterVehicleModels;
use App\Generation;
use App\Region;
use App\TelegramUser;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{

    public function index(){
        $result = TelegramBot::getWebhookUpdates();


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
                'text' => 'Выбрано' //$result['callback_query']['data']
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

                    $reply = 'Выберите модель или пропустите';
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

                    $reply = 'Выберите поколение';
                    $btns = TelegramBot::keyboardFromModel(Generation::where('category_id', '=', $data->id)->get(), 'generations');
                    break;

                case 'generations':

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


        if (isset($result["message"]["test"])){
            TelegramBot::sendMessage($chat_id, $reply, array(
                'keyboard' => array(
                    array(
                        array('text' => 'Работает')
                    )
                ),
                'resize_keyboard ' => true
            ));
        }

        if(isset($text)){
            if ($text == "/start") {
                $name = isset($result["message"]["from"]["username"]) ? $result["message"]["from"]["username"] : 'NO NAME'; //Юзернейм пользователя
                $reply = "Добро пожаловать в бота";

                if (!$telegramUser = TelegramUser::where('chat_id', '=', $chat_id)->first()){
                    TelegramUser::insert(array('chat_id' => $chat_id));
                }

                TelegramBot::sendMessage($chat_id, $reply, array(
                    'keyboard' => array(
                        array(
                            array('text' => 'Добавить фильтр'),
                            array('text' => 'Мой фильтр')
                        ),
                        array(
                            array('text' => 'Удалить фильтр'),
                        )
                    ),
                    'resize_keyboard ' => true
                ));
            }
            elseif ($text === "/help"){
                $reply = "Помощь";
                TelegramBot::sendMessage($chat_id, $reply);
            }
            elseif ($text === "/register"){
                if (!$telegramUser = TelegramUser::where('chat_id', '=', $chat_id)->first()){
                    TelegramUser::insert(array('chat_id' => $chat_id));
                    $reply = "Вы успешно зарегестрированны " . $name . ";";
                }
                else{
                    $reply = 'Вы уже зарегестрированны';
                }

                TelegramBot::sendMessage($chat_id, $reply);
            }
            elseif ($text === "Добавить фильтр"){
                $reply = "Добавить фильтр";
                if (!$filter_id = Filter::where('chat_id', '=', $chat_id)->first()){
                    Filter::insert(array(
                        'chat_id' => $chat_id,
                    ));
                }

                TelegramBot::sendMessage($chat_id, $reply, array(
                    'inline_keyboard' => array(
                        array(
                            array('text'=>'Поиск по маркам машин','callback_data'=>'{"action":"getBrand"}')
                        ),
                        array(
                            array('text'=>'Задать местоположение','callback_data'=>'{"action":"getCountry"}')
                        )
                    ))
                );
            }
            elseif($text === "Мой фильтр"){
                $modelsArray = array();
                $modelNames = '';
                $reply = "Фильтров не найдено";
                $userFilter = Filter::where('chat_id', '=', $chat_id)->first();
                if (!empty($userFilter)){
                    $brand = Category::whereId($userFilter->brand)->first();
                    $models = FilterVehicleModels::whereFilterId($userFilter->id)->get('category_id');
                    $location = City::whereId($userFilter->city_id)->first();
                    $region = Region::whereId($userFilter->region_id)->first();
                    $country = Country::whereId($userFilter->country_id)->first();

                    $brand = empty($brand) == false ? $brand->name : "Все";
                    foreach ($models as $model){
                        $modelsArray[] = Category::whereId($model->category_id)->first()->name;
                    }
                    $modelNames = empty($models->toArray()) == false ? implode(", ", $modelsArray) : "Все";
                    $country = empty($country) == false ? $country->name : "Все";
                    $region = empty($region) == false ? $region->name : "Все";
                    $location = empty($location) == false ? $location->name : "Все" ;

                    $reply = "
                   Бренд: {$brand}\nМодели: $modelNames\nСтрана: $country\nРегион: $region\nГород:  $location
                ";
                }



                TelegramBot::sendMessage($chat_id, $reply);


            }
            elseif($text === "Удалить фильтр"){
                $reply = "Фильтр удален";
                $userFilter = Filter::where('chat_id', '=', $chat_id)->first();
                if (!empty($userFilter)){
                    FilterVehicleModels::whereFilterId($userFilter->id)->delete();
                    $userFilter->delete();
                }
                else{
                    $reply = "Фильтров не найдено";
                }
//                $userFilter->country_id = 0;
//                $userFilter->region_id = 0;
//                $userFilter->city_id = 0;
//                $userFilter->brand = 0;
//                $userFilter->save();
                TelegramBot::sendMessage($chat_id, $reply);
            }
        }
    }





}
