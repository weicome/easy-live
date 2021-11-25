<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/24
 * Time: 下午3:19
 */

namespace EasySwoole\Http\Message;

use Psr\Http\Message\ResponseInterface;

class Response extends Message implements ResponseInterface
{
    private $statusCode = 200;
    private $reasonPhrase = 'OK';
    private $cookies = [];
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function withStatus($code, $reasonPhrase = '')
    {
        if($code === $this->statusCode){
            return $this;
        }else{
            $this->statusCode = $code;
            if(empty($reasonPhrase)){
                $this->reasonPhrase = Status::getReasonPhrase($this->statusCode);
            }else{
                $this->reasonPhrase = $reasonPhrase;
            }
            return $this;
        }
    }

    public function getReasonPhrase()
    {
        return $this->reasonPhrase;
    }

    function withAddedCookie(array $cookie){
        $this->cookies[] = $cookie;
        return $this;
    }

    function getCookies(){
        return $this->cookies;
    }
}