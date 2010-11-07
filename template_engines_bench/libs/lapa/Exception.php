<?php

class LapaException extends Exception
{
    public function __construct($message = null, $code = 0)
    {
        $param = func_get_args();
        $this->message = call_user_func_array('sprintf', $param);
    }
    
    public function __toString()
    {
        return $this->message;
    }
    
    
}