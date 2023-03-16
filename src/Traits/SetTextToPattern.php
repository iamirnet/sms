<?php


namespace iAmirNet\SMS\Traits;


trait SetTextToPattern
{
    public function setTextToPattern($values, $message)
    {
        $replace_values = array_map(function($replace_value) {return "%{$replace_value}%";}, array_keys((array) $values));
        $message = str_replace($replace_values, array_values((array)$values), $message);
        return $message;
    }

    public static function fast($options = [])
    {
        return new static($options);
    }
}
