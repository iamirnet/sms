<?php



/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/2/20, 6:44 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iAmirNet\SMS\Gateways;
use iAmirNet\SamanTel\SamanTelSOAP;
use iAmirNet\SMS\Request\Request;
use iAmirNet\SMS\Traits\SetTextToPattern;

class SamanTel extends Request
{
    use SetTextToPattern;

    public $name = 'samantel';

    public $types = ['soap'];
    public $type = 'soap';
    public $username = null;
    public $password = null;
    public $client = null;
    public $number = null;
    public $number_pattern = null;
    public $footer = null;

    public static $countries = [
        '98'
    ];

    public function __construct($options = [])
    {
        foreach ($options as $index => $option)
            $this->$index = $option;
        if (!$this->client)
            $this->client = new SamanTelSOAP($this->username, $this->password);
    }

    public function credit()
    {
        try{
            $result = $this->client->balance();
            return $result->status ? ['status' => false, 'result' => $result->balanace] : ['status' => false, 'result' => $this->client->getError('E1'), 'code' => 'E1'];
        }catch (\Throwable $e) { // http error
            return ['status' => false, 'result' => $e->getMessage(), 'code' => $e->getCode()];
        }
    }

    public function check($id)
    {
        try{
            $result = $this->fetch($id);
            return $result['status'] ? ['status' => true, 'result' => $result['result'], 'id' => $id] : $result;
        } catch (\Throwable $e) { // http error
            return ['status' => false, 'result' => $e->getMessage(), 'code' => $e->getCode()];
        }
    }

    public function fetch($id)
    {
        try{
            $result = $this->client->deliveryReport($this->number, $id);
            if ($result->status) {
                return ['status' => true, 'result' => $result->message, 'id' => $result->id];
            }else return ['status' => false, 'result' => $result->msg];
        }catch (\Throwable $e) { // http error
            return ['status' => false, 'result' => $e->getMessage(), 'code' => $e->getCode()];
        }
    }

    public function fetchAll($number = null, $limit = 0)
    {
        try{
            list($messages) = $this->client->viewReceive($this->number);
            return ['status' => true, 'result' => $messages];
        } catch (\Throwable $e) { // http error
            return ['status' => false, 'result' => $e->getMessage(), 'code' => $e->getCode()];
        }
    }

    public function send($receiver, $message, $number = null)
    {
        if ($this->footer)
            $message .= "\n" . $this->footer;
        try{
            $result = $this->client->send($number, $receiver, $message);
            if ($result->status) {
                return ['status' => true, 'result' => (array) $result, 'id' => $result->id];
            }else return ['status' => false, 'result' => $result->msg , 'code' => $result->status];
        } catch (\Throwable $e) { // http error
            return ['status' => false, 'result' => $e->getMessage(), 'code' => $e->getCode()];
        }
    }

    public function sendByPattern($values, $receiver, $message, $number = null)
    {
        return $this->send($receiver, $this->setTextToPattern((array)$message, $values), $number ?: ($this->number_pattern ? :$this->number));
    }
}
