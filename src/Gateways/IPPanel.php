<?php



/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/2/20, 6:44 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iLaravel\iSMS\Vendor\GateWays;


class IPPanel extends \iAmirNet\SMS\Request\Request
{
    public $token = null;
    public $client = null;
    public $sender = null;

    public function __construct(array $options)
    {
        $this->token = $options['key'];
        $this->sender = isset($options['sender']) && $options['sender'] ? $options['sender'] : null;
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
        return (array) $this->client->send((string)($sender ?: $this->sender), (is_array($receiver) ? $receiver : [$receiver]), $message);
    }

    public function sendByPattern($pattern, $receiver, $message, $sender = null)
    {
        return (array) $this->client->sendPattern(
            $pattern,
            (string)($sender ?: $this->sender),
            (is_array($receiver) ? $receiver : [$receiver]),
            $message
        );
    }
}
