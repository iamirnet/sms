<?php


namespace iAmirNet\SMS\Gateways;


use iAmirNet\SMS\Request\Request;
use iAmirNet\SMS\Traits\SetTextToPattern;
use IPPanel\Errors\Error;
use IPPanel\Errors\HttpException;

class Telegram extends Request
{
    use SetTextToPattern;
    public $name = 'telegram';

    public $token = null;
    public $client = null;
    public $number = null;
    public $number_pattern = null;
    public $proxy_url = null;

    public static $countries = ['*'];

    public function __construct($options = [])
    {
        foreach ($options as $index => $option)
            $this->$index = $option;
    }

    public function check($id)
    {
        // TODO: Implement check() method.
    }

    public function fetch($id)
    {
        // TODO: Implement fetch() method.
    }

    public function fetchAll($page, $limit)
    {
        // TODO: Implement fetchAll() method.
    }

    public function _send($method, $data, $arg = null)
    {
        $url = "https://api.telegram.org/bot" . $this->token . "/" . $method;
        if ($this->proxy_url)
            $data = ['url' => urlencode($url), 'data' => $data];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->proxy_url ? : $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $res = json_decode(curl_exec($ch));
        if ($res->ok) {
            return ['status' => true, 'result' => $res->result, 'id' => $res->result->message_id];
        } else {
            return ['status' => false, 'result' => $res->description, 'code' => $res->error_code];
        }
    }

    public function send($receiver, $message, $number = null) {
        return $this->_send('sendMessage', [
            'chat_id' => $this->number,
            'text' => $message . "\n ----------------- \n receiver: $receiver",
            'parse_mode' => "html"
        ]);
    }

    public function sendByPattern($values, $receiver, $message, $number = null)
    {
        return $this->send($receiver, $this->setTextToPattern((array)$message, $values), $number ?: ($this->number_pattern ? :$this->number));
    }
}
