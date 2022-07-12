<?php

namespace uzdevid\telegrambot;

use yii\base\Component;
use yii\base\InvalidValueException;

class TelegramBot extends Component {
    public $token;
    public $chat_id;
    public $last_message_id;
    public $reply_markup = [];

    public $parse_mode;
    public $webhook_url;

    const endpointUrl = "https://api.telegram.org/bot{token}";

    public function __construct($config = []) {
        if (empty($config['token']))
            throw new InvalidValueException('Telegram bot token required for the class to work');
        parent::__construct($config);
    }

    public function buildUrlWith($method) {
        $url = str_replace('{token}', $this->token, self::endpointUrl);
        return $method ? $url . "/${method}" : $url;
    }

    public function createKeyboard($keyboard) {
        $this->reply_markup = json_encode(['keyboard' => $keyboard, 'resize_keyboard' => true, 'one_time_keyboard' => true]);
    }

    public function createInlineKeyboard($keyboard) {
        $this->reply_markup = json_encode(['inline_keyboard' => $keyboard]);
    }

    public function getUpdates() {
        $url = $this->buildUrlWith('getUpdates');
        return $this->execute($url);
    }

    public function getBody() {
        $data = file_get_contents("php://input");
        $data = json_decode($data, true);

        if (isset($data['message']))
            $this->chat_id = $data['message']['chat']['id'];
        elseif (isset($data['callback_query']))
            $this->chat_id = $data['callback_query']['message']['chat']['id'];

        return $data;
    }

    protected function execute($url) {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response, true);
    }

    public function setWebHook() {
        if (empty($this->webhook_url))
            throw new InvalidValueException("Requires webhook_url to set webhook");

        $url = $this->buildUrlWith('setWebHook');
        $params = ['url' => $this->webhook_url];
        $url = $url . '?' . http_build_query($params);

        $content = $this->execute($url);
        return $content['result'];
    }

    public function deleteWebHook() {
        if (empty($this->webhook_url))
            throw new InvalidValueException("Requires webhook_url to delete webhook");

        $url = $this->buildUrlWith('deleteWebHook');
        $params = ['url' => $this->webhook_url];
        $url = $url . '?' . http_build_query($params);

        $content = $this->execute($url);
        return $content['result'];
    }

    public function onMessage($message, $function) {
        if ($this->body['message']['text'] === $message) $function($this);
    }

    public function onCallBack($data, $function) {
        if (!isset($this->body['callback_query']))
            return false;

        $callback_data = $this->body['callback_query']['data'];
        if ($callback_data == $data) {
            $function($this, $this->body['callback_query']['data']);
        } else {
            $callback_data = json_decode($callback_data, true);
            if (isset($callback_data['command']) && $callback_data['command'] == $data)
                $function($this, $callback_data);
        }
    }

    protected function beforeSend($params) {
        if (is_string($params))
            $params = ['text' => $params];

        if (empty($params['chat_id']))
            $params['chat_id'] = $this->chat_id;

        if (empty($params['parse_mode']))
            $params['parse_mode'] = $this->parse_mode;

        if (empty($params['reply_markup']))
            $params['reply_markup'] = $this->reply_markup;

        return $params;
    }

    public function send($params) {
        $params = $this->beforeSend($params);

        $url = $this->buildUrlWith('sendMessage');
        $url = $url . '?' . http_build_query($params);
        $response = $this->execute($url);

        $this->afterSend($response);
        return $response;
    }

    protected function afterSend($response) {
        if ($response['ok'] === true) {
            $this->last_message_id = $response['result']['message_id'];
        }

        $this->reply_markup = null;
    }

    protected function beforeEdit($params) {
        if (is_string($params))
            $params = ['text' => $params];

        if (empty($params['chat_id']))
            $params['chat_id'] = $this->chat_id;

        if (empty($params['message_id']))
            $params['message_id'] = $this->last_message_id;

        if (empty($params['reply_markup']))
            $params['reply_markup'] = $this->reply_markup;

        return $params;
    }

    public function edit($params) {
        $params = $this->beforeEdit($params);

        $url = $this->buildUrlWith('editMessageText');
        $url = $url . '?' . http_build_query($params);
        $response = $this->execute($url);

        $this->afterEdit($response);
        return $response;
    }

    protected function afterEdit($response) {
        $this->reply_markup = null;
    }
}