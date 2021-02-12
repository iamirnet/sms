<?php



/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/2/20, 6:44 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iAmirNet\SMS\Gateways;


use iAmirNet\SMS\Traits\SetTextToPattern;

class IPPanel extends \iAmirNet\SMS\Request\Request
{
    use SetTextToPattern;

    public $name = 'ippanel';

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
            $this->client = new \IPPanel\Client($this->token);
    }

    public function check($id)
    {
        list($statuses, $paginationInfo) = $this->client->fetchStatuses($id, 0, 10);
        $statuses = array_unique(array_column($statuses, 'status'));
        return count($statuses) == 1 ? $statuses[0] : 'sent';
    }

    public function fetch($id)
    {
        return (array) $this->client->getMessage($id);
    }

    public function fetchAll($page, $limit)
    {
        list($messages, $paginationInfo) = $this->client->fetchInbox($page, $limit);
        return $messages;
    }

    public function send($receiver, $message, $sender = null)
    {
        if ($this->footer)
            $message .= "\n" . $this->footer;
        return (array) $this->client->send((string)($sender ?: $this->sender), (is_array($receiver) ? $receiver : [$receiver]), $message);
    }

    public function sendByPattern($pattern, $receiver, $message, $sender = null)
    {
        if (is_array($pattern))
            return (array) $this->send($receiver, $this->setTextToPattern($pattern, $message), $this->sender_pattern);
        else
            return (array) $this->client->sendPattern(
                $pattern,
                (string)($sender ?: ($this->sender_pattern ? :$this->sender)),
                (is_array($receiver) ? $receiver : [$receiver]),
                $message
            );
    }
}
