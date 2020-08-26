<?php

namespace App\Http\Controllers;

use App\Facades\TelegramBot;
use App\TelegramUser;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Telegram\Bot\Api;

class TelegramController extends Controller
{

    public function index(){

        $result = TelegramBot::getWebhookUpdates();
        //dd($result);
        $text = $result["message"]["text"]; //Текст сообщения
        $chat_id = $result["message"]["chat"]["id"]; //Уникальный идентификатор пользователя
        $name = isset($result["message"]["from"]["username"]) ? $result["message"]["from"]["username"] : 'NO NAME'; //Юзернейм пользователя

        if($text){
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
        }

//        $result = TelegramBot::sendTelegramData('setwebhook', [
//            'query' => ['url' => 'https://carpars.ru' . '/settings/telegram_test/']
//        ]);

        //TelegramBot::setWebHook('/settings/telegram_test/');
        //TelegramBot::sendMessage(518575553, 'https://cars.av.by/nissan/murano/18193518');
//        $result = TelegramBot::sendTelegramData('sendmessage', [
//            'query' => [
//                'chat_id' => '518575553',
//                'text' => 'https://cars.av.by/nissan/murano/18193518'
//            ]
//        ]);
    }

}
