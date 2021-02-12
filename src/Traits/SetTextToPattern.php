<?php


namespace iAmirNet\SMS\Traits;


trait SetTextToPattern
{
    public function setTextToPattern($pattern, $message)
    {
        $patterns = array_map(function($pattern) {return "%{$pattern}%";}, array_keys($message));
        $message = str_replace($patterns, array_values($message), $pattern);
        return $message;
    }
}