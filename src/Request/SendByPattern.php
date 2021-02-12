<?php



/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/13/20, 1:01 AM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iAmirNet\SMS\Request;

interface SendByPattern
{
    public function sendByPattern($pattern, $receiver, $message, $sender = null);
}
