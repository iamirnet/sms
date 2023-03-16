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

class SabaPayamak
{
    use SetTextToPattern;

    public $name = 'sabapayamak';

    public $api_url = "https://api.sabapayamak.com";
    public $username = null;
    public $token = null;
    public $client = null;
    public $number = null;
    public $number_pattern = null;
    public $footer = null;
    public $path_config = null;

    public static $countries = [
        '98'
    ];

    public function __construct($options = [])
    {
        foreach ($options as $index => $option)
            $this->$index = $option;
        if (!$this->client)
            $this->client = new \Sabapayamak\Sabapayamak($this->api_url);
        if (!$this->path_config)
            $this->path_config = join(DIRECTORY_SEPARATOR, [__DIR__, "..", "config"]);
        $this->path_config = join(DIRECTORY_SEPARATOR, [$this->path_config, "gateways", "sabapayamak"]);
        if (!file_exists($this->path_config))
            mkdir($this->path_config, 0755, true);
        $this->path_config = join(DIRECTORY_SEPARATOR, [$this->path_config, $this->username . ".json"]);
        $config = [];
        if (file_exists($this->path_config)) {
            $config = json_decode(file_get_contents($this->path_config), true);
            if ($config["expired_at"] < time()) {
                $config = [];
            }
        }

        if (!isset($config["token"])) {
            $result = $this->client->GetToken()
        }

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
            return ['status' => true, 'result' => $this->client->getMessage($id)];
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

    public function send($receiver, $message, $number = null)
    {
        if ($this->footer)
            $message .= "\n" . $this->footer;
        try{
            $result = $this->client->send((string)($number ?: $this->number), (is_array($receiver) ? $receiver : [$receiver]), $message);
            $result = $this->fetch($result)['result'];
            return ['status' => true, 'result' => (array) $result, 'id' => $result->bulkId];
        } catch (Error $e) { // ippanel error
            return ['status' => false, 'result' => $e->unwrap(), 'code' => $e->getCode()];
        } catch (HttpException $e) { // http error
            return ['status' => false, 'result' => $e->getMessage(), 'code' => $e->getCode()];
        }
    }

    public function sendByPattern($pattern, $receiver, $message, $provider = false, $number = null)
    {
        if (!$provider)
            return (array) $this->send($receiver, $this->setTextToPattern((array) $message, $pattern), $number ?: ($this->number_pattern ? :$this->number));
        else
            try{
                $result = $this->client->sendPattern(
                    $pattern,
                    (string)($number ?: ($this->number_pattern ? :$this->number)),
                    $receiver,
                    array_map(function ($value) {
                        return (string) $value;
                    }, (array) $message)
                );
                sleep(2);
                $result = $this->fetch($result)['result'];
                return ['status' => true, 'result' => (array) $result, 'id' => $result->bulkId];
            } catch (Error $e) { // ippanel error
                return ['status' => false, 'result' => $e->unwrap(), 'code' => $e->getCode()];
            } catch (HttpException $e) { // http error
                return ['status' => false, 'result' => $e->getMessage(), 'code' => $e->getCode()];
            }
    }
}
