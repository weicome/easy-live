<?php

namespace App\Process;

use EasySwoole\Component\Di;
use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\Component\TableManager;
use EasySwoole\Component\Timer;
use EasySwoole\EasySwoole\Config;
use Swoole\Process;

class WatchProcess extends AbstractProcess
{
    private static array $streamTimer = [];

    protected function run($arg)
    {
        // TODO: Implement run() method.
        $this->loadTimer();
    }

    private function loadTimer()
    {
        $timer = (int)Config::getInstance()->getConf('srs.timer') ?: 60 * 5;
        Timer::getInstance()->loop($timer * 1000, function () use($timer) {
            $now = time();
            foreach (self::$streamTimer as $key => $outTime){
                if($outTime < $now){
                    $this->clearTable($key);
                }
            }
        });
    }

    private function clearTable($stream_key)
    {
        $ffmpegProcess = Di::getInstance()->get('ffmProcess');
        $ffmpegProcess->write('close-' . $stream_key);
        $processTable = TableManager::getInstance()->get('process');
        $streamTable = TableManager::getInstance()->get('stream');
        $clientTable = TableManager::getInstance()->get('client');
        $clientTable->del($stream_key);
        $streamTable->del($stream_key);
        $php_pid = $processTable->get($stream_key,'php_pid');
        $processTable->del($stream_key);
        \swoole_process::kill($php_pid, 0);
        exec("pkill -P {$php_pid}");
        exec("kill -9 {$php_pid}");
        unset(self::$streamTimer[$stream_key]);
    }

    protected function onPipeReadable(Process $process)
    {
        // 该回调可选
        // 当主进程对子进程发送消息的时候 会触发
        $msg = json_decode($process->read(),true); // 用于获取主进程给当前进程发送的消息
        switch (array_keys($msg)[0]){
            case 'add':
                self::$streamTimer[$msg['add']] = time();
                break;
            case 'clear':
                $this->clearTable($msg['clear']);
                break;
        }
    }

    protected function onException(\Throwable $throwable, ...$args)
    {
        // 该回调可选
        // 捕获 run 方法内抛出的异常
        // 这里可以通过记录异常信息来帮助更加方便地知道出现问题的代码
    }

    protected function onShutDown()
    {
        // 该回调可选
        // 进程意外退出 触发此回调
        // 大部分用于清理工作
    }

    protected function onSigTerm()
    {
        // 当进程接收到 SIGTERM 信号触发该回调
    }
}
