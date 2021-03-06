<?php

namespace top\library\exception;

class ResponseException extends BaseException
{
    public function __construct($message = "", $code = 0, \Throwable $previous = null)
    {
        parent::__construct('[ResponseException]' . $message, $code, $previous);
    }

    /**
     * @param \Exception $exception
     */
    public function handler($exception = null)
    {
        parent::handler($this); // TODO: Change the autogenerated stub
    }
}
