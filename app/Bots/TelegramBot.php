<?php


namespace App\Bots;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

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

    public function sendMessage($chatId, $message){
        $this->sendTelegramData('sendmessage', [
            'query' => [
                'chat_id' => $chatId,
                'text' => $message
            ]
        ]);
    }

    private function sendTelegramData($route = '', $params = [], $method = 'POST') {
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
}
