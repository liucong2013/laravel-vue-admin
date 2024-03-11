<?php

namespace App\Exceptions;


use Throwable;

class CustomException extends \Exception
{
    /**
     * 自定义错误消息和错误码
     * @var mixed|string
     */
    public $customMessage = '';
    public $customCode;

    public function __construct($message = "系统内部错误，请联系客服" , $code = 500 , Throwable $previous = null)
    {
        if(!empty($message)){
            $this->customMessage = $message;
        }
        if(!empty($code)){
            $this->customCode = $code;
        }


        parent::__construct($previous);

    }
}
