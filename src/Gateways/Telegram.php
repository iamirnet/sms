<?php


namespace iAmirNet\SMS\Gateways;


use iAmirNet\SMS\Request\Request;

class Telegram extends Request
{
    public $token = null;
    public $client = null;
    public $sender = null;

    public function __construct(array $options)
    {
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

    public function send($method, $data, $arg = null)
    {
        $url = "https://api.telegram.org/bot" . $this->token . "/" . $method;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $res = curl_exec($ch);
        if (curl_error($ch)) {
            return false;
        } else {
            return true;
        }
    }

    public function sendByPattern($pattern, $receiver, $message, $sender = null)
    {
        // TODO: Implement sendByPattern() method.
    }
}