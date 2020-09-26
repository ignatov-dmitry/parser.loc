<?php


namespace App\Bots;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
class TelegramBot
{
    public function setWebHook($url){
        $result = TelegramBot::sendTelegramData('setwebhook', [
            'query' => ['url' => 'https://carpars.ru' . $url]
        ]);
    }

    public function getWebhookUpdates(){
        return \Telegram::bot('carpars_bot')->getWebhookUpdate();
    }

    public function sendMessage($chatId, $message, $keyboard = array()){
        $params = [
            'query' => [
                'chat_id' => $chatId,
                'text' => $message
        ]];

        if ($keyboard){
            $params['query']['reply_markup'] = json_encode($keyboard);
        }

        $this->sendTelegramData('sendmessage', $params);
    }

    private function sendTelegramData($route = '', $params = [], $method = 'POST') : string {
        $client = new Client(array(
            'base_uri' => 'https://api.telegram.org/bot' . \Telegram::bot('carpars_bot')->getAccessToken() . '/'
        ));

        try {
            $result = $client->request($method, $route, $params);
        } catch (GuzzleException $e) {
            dd($e->getMessage());
        }
        return (string)$result->getBody();
    }



    public function keyboardFromModel(Collection $collection, $action) {
        $buttons = array();
        for ($i = 0; $i < count($collection); $i++){
            for ($j = 0; $j < 3; $j++){
                if ($i + $j < count($collection)){
                    $buttons[$i][$j] =  array('text'=>$collection[$i + $j]->name,'callback_data'=>'{"action":"' . $action . '","id":"' . $collection[$i + $j]->id . '"}');
                }
            }
            $i = $i + $j - 1;
        }

        return array_values($buttons);
    }
}
