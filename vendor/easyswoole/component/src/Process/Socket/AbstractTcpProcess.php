<?php


namespace EasySwoole\Component\Process\Socket;


use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\Component\Process\Exception;
use Swoole\Coroutine\Socket;
use Swoole\Coroutine;

abstract class AbstractTcpProcess extends AbstractProcess
{
    function __construct(TcpProcessConfig $config)
    {
        $config->setEnableCoroutine(true);
        if(empty($config->getListenPort())){
            throw new Exception("listen port empty at class ".static::class);
        }
        parent::__construct($config);
    }

    public function run($arg)
    {
        $socket = new Socket(AF_INET,SOCK_STREAM,0);
        $socket->setOption(SOL_SOCKET,SO_REUSEPORT,true);
        $socket->setOption(SOL_SOCKET,SO_REUSEADDR,true);
        $socket->setOption(SOL_SOCKET,SO_LINGER,$this->getConfig()->getLinger());
        $ret = $socket->bind($this->getConfig()->getListenAddress(),$this->getConfig()->getListenPort());
        if(!$ret){
            throw new Exception(static::class." bind {$this->getConfig()->getListenAddress()}:{$this->getConfig()->getListenPort()} fail case ".$socket->errMsg);
        }
        $ret = $socket->listen(2048);
        if(!$ret){
            throw new Exception(static::class." listen {$this->getConfig()->getListenAddress()}:{$this->getConfig()->getListenPort()} fail case ".$socket->errMsg);
        }
        while (1){
            $client = $socket->accept(-1);
            if(!$client){
                return;
            }
            if($this->getConfig()->isAsyncCallback()){
                Coroutine::create(function ()use($client){
                    try{
                        $this->onAccept($client);
                    }catch (\Throwable $throwable){
                        $this->onException($throwable,$client);
                    }
                });
            }else{
                try{
                    $this->onAccept($client);
                }catch (\Throwable $throwable){
                    $this->onException($throwable,$client);
                }
            }
        }
    }

    abstract function onAccept(Socket $socket);
}