<?php


namespace iAmirNet\SMS\Request;


abstract class Request implements Construct, Send, SendByPattern, Fetch, FetchAll, Check
{
    public function clearNumber($n, $country = '98') {
        if(substr($n, 0, 1)=='+')
            $n = substr($n,1);
        if(substr($n, 0, 2)=='00')
            $n = substr($n,2);
        if(substr($n, 0, 1)=='0')
            $n = substr($n,1);
        if(substr($n, 0, strlen($country)) == $country)
            $n = substr($n,strlen($country));
        return "+{$country}{$n}";
    }
}