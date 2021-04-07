<?php



/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/2/20, 6:44 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iAmirNet\SMS\Gateways;
use IPPanel\Errors\Error;
use IPPanel\Errors\HttpException;

use iAmirNet\SMS\Traits\SetTextToPattern;
use IPPanel\Errors\ResponseCodes;

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
        try{
            list($statuses, $paginationInfo) = $this->client->fetchStatuses($id, 0, 10);
            $statuses = array_unique(array_column($statuses, 'status'));
            return ['status' => true, 'result' => count($statuses) == 1 ? $statuses[0] : 'sent'];
        } catch (Error $e) { // ippanel error
            return ['status' => false, 'result' => $e->unwrap(), 'code' => $e->getCode()];
        } catch (HttpException $e) { // http error
            return ['status' => false, 'result' => $e->getMessage(), 'code' => $e->getCode()];
        }
    }

    public function fetch($id)
    {
        try{
            return ['status' => true, 'result' => (array) $this->client->getMessage($id)];
        } catch (Error $e) { // ippanel error
            return ['status' => false, 'result' => $e->unwrap(), 'code' => $e->getCode()];
        } catch (HttpException $e) { // http error
            return ['status' => false, 'result' => $e->getMessage(), 'code' => $e->getCode()];
        }
    }

    public function fetchAll($page, $limit)
    {
        try{
            list($messages, $paginationInfo) = $this->client->fetchInbox($page, $limit);
            return ['status' => true, 'result' => $messages];
        } catch (Error $e) { // ippanel error
            return ['status' => false, 'result' => $e->unwrap(), 'code' => $e->getCode()];
        } catch (HttpException $e) { // http error
            return ['status' => false, 'result' => $e->getMessage(), 'code' => $e->getCode()];
        }
    }

    public function send($receiver, $message, $sender = null)
    {
        if ($this->footer)
            $message .= "\n" . $this->footer;
        try{
            return ['status' => true, 'result' => (array) $this->client->send((string)($sender ?: $this->sender), (is_array($receiver) ? $receiver : [$receiver]), $message)];
        } catch (Error $e) { // ippanel error
            return ['status' => false, 'result' => $e->unwrap(), 'code' => $e->getCode()];
        } catch (HttpException $e) { // http error
            return ['status' => false, 'result' => $e->getMessage(), 'code' => $e->getCode()];
        }
    }

    public function sendByPattern($pattern, $receiver, $message, $sender = null)
    {

        if (is_array($pattern))
            return (array) $this->send($receiver, $this->setTextToPattern($pattern, $message), $this->sender_pattern);
        else
            try{
                return ['status' => true, 'result' => (array) $this->client->sendPattern(
                    $pattern,
                    (string)($sender ?: ($this->sender_pattern ? :$this->sender)),
                    $receiver,
                    $message
                )];
            } catch (Error $e) { // ippanel error
                return ['status' => false, 'result' => $e->unwrap(), 'code' => $e->getCode()];
            } catch (HttpException $e) { // http error
                return ['status' => false, 'result' => $e->getMessage(), 'code' => $e->getCode()];
            }
    }
}
