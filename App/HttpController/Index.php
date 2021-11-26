<?php

namespace App\HttpController;


use EasySwoole\Component\Di;
use EasySwoole\Component\TableManager;
use EasySwoole\EasySwoole\Config;

class Index extends BaseController
{
    public function index()
    {
        $name = $this->request()->getQueryParam('name');
        $this->response()->write('Hello '.$name . PHP_EOL);
    }

    public function test()
    {
        $host = EASYSWOOLE_ROOT.DIRECTORY_SEPARATOR.'storage/setup/srs/trunk/doc/source.200kbps.768x320.flv';
        $app = $this->input('live') ?: 'live';
        $stream_id = $this->input('stream') ?: 'test123';
        $stream_key = stream_key($app,$stream_id);
        $streamTable = TableManager::getInstance()->get('stream');
        if($row = $streamTable->get($stream_key)){
            $play = true;
        }else{
            $streamTable->set($stream_key,['rows'=>json_encode(['app'=>$app,'stream_id'=>$stream_id,'rtsp_host'=>$host])]);
            $process =  Di::getInstance()->get('ffmProcess');
            $play = $process->write($stream_key);
            $row = ['app'=>$app,'stream_id'=>$stream_id,'live_host'=>$host];
        }
        $localhost = Config::getInstance()->getConf('srs.localhost');
        $httpport = Config::getInstance()->getConf('srs.srs_config.http_server.listen');
        $httpport = substr($httpport,0,strpos($httpport,';'));
        $play ? $this->success([
            'stream_key' => $stream_key,
            'RTMP'=> "rtmp://{$localhost}/live/test123",
            'HTTP-FLV'=> "http://{$localhost}:{$httpport}/live/test123.flv",
            'HLS'=> "http://{$localhost}:{$httpport}/live/test123.m3u8",
            'WebRTC'=> "webrtc://{$localhost}/live/test123",
        ],'播放成功,记得关闭测试！') : $this->error();
    }

    public function onClose()
    {
        try{
            $stream_key = stream_key('live','test123');
            $processTable = TableManager::getInstance()->get('process');
            if($processTable->exist($stream_key)){
                $php_pid = $processTable->get($stream_key,'php_pid');
                \swoole_process::kill($php_pid,0);
                exec("pkill -P {$php_pid}");
//                proc_close($php_pid);
                $processTable->del($stream_key);
            }
            $streamTable = TableManager::getInstance()->get('stream');
            $streamTable->del($stream_key);
            $this->success([],'删除成功');
        }catch (\Exception $exception){
            throw $exception;
        }
    }
}
