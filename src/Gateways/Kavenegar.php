<?php



/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/2/20, 6:44 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iAmirNet\SMS\Gateways;


use iAmirNet\SMS\Traits\SetTextToPattern;
use Kavenegar\Exceptions\ApiException;
use Kavenegar\Exceptions\HttpException;

class Kavenegar extends \iAmirNet\SMS\Request\Request
{
    use SetTextToPattern;

    public $name = 'kavenegar';

    public $token = null;
    public $client = null;
    public $number = null;
    public $number_pattern = null;
    public $footer = null;

    public static $countries = [
        '98'
    ];

    public function __construct(array $options = [])
    {
        foreach ($options as $index => $option)
            $this->$index = $option;
        if (!$this->client)
            $this->client = new \Kavenegar\KavenegarApi($this->token);
    }

    public function check($id)
    {
        try{
            $result = (array) $this->client->Status($id);
            $status = isset($result[0]) && $result[0]['status'] == 10;
            return ['status' => $status, 'result' => $status ? 'sent' : 'unsent'];
        } catch (ApiException $e) {
            return ['status' => false, 'result' => $e->errorMessage(), 'code' => $e->getCode()];
        } catch (HttpException $e) {
            return ['status' => false, 'result' => $e->errorMessage(), 'code' => $e->getCode()];
        }
    }

    public function fetch($id)
    {
        try{
            $result = (array) $this->client->Select($id);
            return ['status' => count($result) > 0, 'result' => $result[0]];
        } catch (ApiException $e) {
            return ['status' => false, 'result' => $e->errorMessage(), 'code' => $e->getCode()];
        } catch (HttpException $e) {
            return ['status' => false, 'result' => $e->errorMessage(), 'code' => $e->getCode()];
        }
    }

    public function fetchAll($page, $number)
    {
        try{
            return ['status' => true, 'result' => (array) $this->client->LatestOutbox($page, $number)];
        } catch (ApiException $e) {
            return ['status' => false, 'result' => $e->errorMessage(), 'code' => $e->getCode()];
        } catch (HttpException $e) {
            return ['status' => false, 'result' => $e->errorMessage(), 'code' => $e->getCode()];
        }
    }

    public function send($receiver, $message, $number = null)
    {
        if ($this->footer)
            $message .= "\n" . $this->footer;
        try{
            $this->client->Send((string)($number ?: $this->number), (is_array($receiver) ? $receiver : [$receiver]), $message);
            return ['status' => true, 'result' => 'sent'];
        }
        catch(ApiException $e){
            return ['status' => false, 'result' => $e->errorMessage(), 'code' => $e->getCode()];
        }
        catch(HttpException $e){
            return ['status' => false, 'result' => $e->errorMessage(), 'code' => $e->getCode()];
        }
    }

    public function sendByPattern($values, $receiver, $message, $number = null)
    {
        return $this->send($receiver, $this->setTextToPattern((array)$message, $values), $number ?: ($this->number_pattern ? :$this->number));
    }
}
