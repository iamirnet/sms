<?php



/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/2/20, 6:44 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iAmirNet\SMS\Gateways;


use iAmirNet\SMS\Traits\SetTextToPattern;

class Kavenegar extends \iAmirNet\SMS\Request\Request
{
    use SetTextToPattern;

    public $name = 'kavenegar';

    public $token = null;
    public $client = null;
    public $sender = null;
    public $sender_pattern = null;
    public $footer = null;

    public function __construct(array $options = [])
    {
        foreach ($options as $index => $option)
            $this->$index = $option;
        if (!$this->client)
            $this->client = new \Kavenegar\KavenegarApi($this->token);
    }

    public function check($id)
    {
        return (array) $this->client->Status($id);
    }

    public function fetch($id)
    {
        return (array) $this->client->Select($id);
    }

    public function fetchAll($page, $sender)
    {
        return (array) $this->client->LatestOutbox($page, $sender);
    }

    public function send($receiver, $message, $sender = null)
    {
        if ($this->footer)
            $message .= "\n" . $this->footer;
        return (array) $this->client->Send((string)($sender ?: $this->sender), (is_array($receiver) ? $receiver : [$receiver]), $message);
    }

    public function sendByPattern($pattern, $receiver, $message, $sender = null)
    {
        return (array) $this->send($receiver, $this->setTextToPattern($pattern, $message), $this->sender_pattern);
    }
}
