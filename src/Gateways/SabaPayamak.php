<?php



/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/2/20, 6:44 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iAmirNet\SMS\Gateways;
use iAmirNet\SMS\Request\Request;
use iAmirNet\SMS\Traits\SetTextToPattern;

class SabaPayamak extends Request
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
            $this->client = new \Sabapayamak\SabapayamakApi($this->api_url);
        if (!$this->path_config)
            $this->path_config = implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "config"]);
        $this->path_config = implode(DIRECTORY_SEPARATOR, [$this->path_config, "gateways", "sabapayamak"]);
        if (!file_exists($this->path_config))
            if (!mkdir($concurrentDirectory = $this->path_config, 0755, true) && !is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        $this->path_config = implode(DIRECTORY_SEPARATOR, [$this->path_config, $this->username . $this->number . ".json"]);
        $config = [];
        if (file_exists($this->path_config)) {
            $config = json_decode(file_get_contents($this->path_config), true);
            //dd(date('Y/m/d', strtotime('today')), date('Y/m/d', $config["expired_at"]));
            if ($config["expired_at"] < time()) {
                $config = [];
            }
        }
        if (!isset($config["token"])) {
            $result = $this->client->GetToken($this->username, $this->password, $this->number, 365);
            if (isset($result->status) && $result->status == 200 && isset($result->data->token)) {
                $config['token'] = $result->data->token;
                $config['expired_at'] = strtotime("360 day");
                file_put_contents($this->path_config, json_encode($config));
            }else {
                throw new \Exception('Can\'t create a token.');
            }
        }
        $this->token = $config['token'];
    }

    public function credit()
    {
        try{
            $result = $this->client->GetCredit($this->token);
            return
                isset($result->status) && $result->status == 200 && isset($result->data) ?
                    ['status' => true, 'result' => $result->data] :
                    ['status' => false, 'result' => isset($result->errors) && $result->errors ? $result->errors : (isset($result->message) && $result->message ? $result->message : "NOK"), 'code' => $result->status];
        } catch (Error $e) { // ippanel error
            return ['status' => false, 'result' => $e->unwrap(), 'code' => $e->getCode()];
        } catch (HttpException $e) { // http error
            return ['status' => false, 'result' => $e->getMessage(), 'code' => $e->getCode()];
        }
    }

    public function check($id)
    {
        try{
            $result = $this->fetch($id);
            return $result['status'] && isset($result['result']['status'])? ['status' => true, 'result' => $result['result']['status'], 'id' => $result['id']] : $result['status'];
        } catch (Error $e) { // ippanel error
            return ['status' => false, 'result' => $e->unwrap(), 'code' => $e->getCode()];
        } catch (HttpException $e) { // http error
            return ['status' => false, 'result' => $e->getMessage(), 'code' => $e->getCode()];
        }
    }

    public function fetch($id)
    {
        try{
            $result = $this->client->GetMessageById($id, $this->token);
            if (isset($result->status) && $result->status == 200 && isset($result->data->messageID)) {
                return ['status' => true, 'result' => (array) $result->data, 'id' => $result->data->messageID];
            }else return ['status' => false, 'result' => isset($result->errors) && $result->errors ? $result->errors : (isset($result->message) && $result->message ? $result->message : "NOK"), 'code' => $result->status];
        } catch (Error $e) { // ippanel error
            return ['status' => false, 'result' => $e->unwrap(), 'code' => $e->getCode()];
        } catch (HttpException $e) { // http error
            return ['status' => false, 'result' => $e->getMessage(), 'code' => $e->getCode()];
        }
    }

    public function fetchAll($number = null, $limit = 0)
    {
        try{
            list($messages, $paginationInfo) = $this->client->GetMessageByNumber($this->number, $this->token);
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
            $result = $this->client->SendMessage($message, (is_array($receiver) ? $receiver : [$receiver]), $this->token);
            if (isset($result->status) && $result->status == 200 && isset($result->data->id)) {
                return ['status' => true, 'result' => (array) $result->data, 'id' => $result->data->id];
            }else return ['status' => false, 'result' => isset($result->errors) && $result->errors ? $result->errors : (isset($result->message) && $result->message ? $result->message : "NOK"), 'code' => $result->status];
        } catch (Error $e) { // ippanel error
            return ['status' => false, 'result' => $e->unwrap(), 'code' => $e->getCode()];
        } catch (HttpException $e) { // http error
            return ['status' => false, 'result' => $e->getMessage(), 'code' => $e->getCode()];
        }
    }

    public function sendByPattern($values, $receiver, $message, $number = null)
    {
        return $this->send($receiver, $this->setTextToPattern((array)$message, $values), $number ?: ($this->number_pattern ? :$this->number));
    }
}
