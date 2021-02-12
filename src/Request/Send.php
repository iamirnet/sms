<?php



/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/13/20, 7:23 AM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iAmirNet\SMS\Request;


interface Send
{
    public function send($receiver, $message, $sender = null);
}
