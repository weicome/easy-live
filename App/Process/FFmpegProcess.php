<?php

namespace App\Process;

use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\Component\TableManager;
use EasySwoole\EasySwoole\Config;
use Swoole\Process;

class FFmpegProcess extends AbstractProcess
{
    private $childProc = [];

    /**音频码率因子
     * @var float
     */
    private static $audio_factor = 0.6;
    /**视频码率因子
     * @var float
     */
    private static $video_factor = 0.6;

    protected function run($arg)
    {
        // TODO: Implement run() method.
        Process::signal(SIGCHLD, function ($sig) {
            Process::wait(false);
        });
    }

    private function pullStream(string $stream_key)
    {
        $instance = Config::getInstance();
        $streamTable = TableManager::getInstance()->get('stream');
        $rows = $streamTable->get($stream_key);
        $row = json_decode($rows['rows'], true);
        $descriptorspec = array(
            0 => array("pipe", "r"),
            1 => array("pipe", "w"),
        );
        if ($instance->getConf('srs.log_debug')) {
            $descriptorspec[2] = array("file", EASYSWOOLE_ROOT . "/Log/error-output.txt", "a");
        }
        $ffm = '/usr/bin/ffmpeg';
        if (file_exists($instance->getConf('srs.srs_path') . '/objs/ffmpeg/bin/ffmpeg')) {
            $ffm = $instance->getConf('srs.srs_path') . '/objs/ffmpeg/bin/ffmpeg';
        }
        $cmd =  $ffm . ' ' . ' -rtsp_transport tcp -i "' .
            "{$row['rtsp_host']}" .
            '" -c copy -f flv -y ' .
            "rtmp://localhost/{$row['app']}/{$row['stream_id']}";
        echo 'cmd: ' . $cmd . PHP_EOL;
        $process = proc_open($cmd,  $descriptorspec, $pipes);
        $this->childProc[$stream_key] = [$process, $pipes];
        echo $process . PHP_EOL;
        $pinfo = proc_get_status($process);
        $processTable = TableManager::getInstance()->get('process');
        $processTable->set($stream_key, ['php_pid' => $pinfo['pid']]);
    }

    private function closeStream(string $stream_key)
    {
        $msg = explode('-', $stream_key);
        if (list($proc, $pipes) = $this->childProc[$msg[1]]) {
            echo '资源'.json_encode($pipes).PHP_EOL;
            if (isset($pipes[0])) fclose($pipes[0]);
            if (isset($pipes[1])) fclose($pipes[1]);
            if (isset($pipes[2])) fclose($pipes[2]);
            $res = proc_terminate($proc);
            echo '退出状态:' . $res . PHP_EOL;
            unset($this->childProc[$msg[1]]);
        }
    }

    protected function onPipeReadable(Process $process)
    {
        // TODO: Change the autogenerated stub
        try {
            $stream_key = $this->getProcess()->read(38);
            $streamTable = TableManager::getInstance()->get('stream');
            if (strlen($stream_key) > 32) {
                $this->closeStream($stream_key);
            } else {
                if ($streamTable->exists($stream_key)) {
                    $this->pullStream($stream_key);
                }
            }
        } catch (\Exception $exception) {
            dump($exception->getMessage());
        }
    }

    protected function onException(\Throwable $throwable, ...$args)
    {
        parent::onException($throwable, $args); // TODO: Change the autogenerated stub
    }

    protected function onSigTerm()
    {
        parent::onSigTerm(); // TODO: Change the autogenerated stub
    }

    protected function onShutDown()
    {
        parent::onShutDown(); // TODO: Change the autogenerated stub
    }
}
