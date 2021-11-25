<?php

namespace App\HttpController;

use App\Util\Stream;
use EasySwoole\Component\Di;
use EasySwoole\Component\Process\Manager;
use EasySwoole\Component\TableManager;
use EasySwoole\EasySwoole\Config;
use EasySwoole\Utility\Str;

class Srs extends BaseController
{
    public function heartbeat()
    {
        $this->retSrs();
    }

    public function  onPublish()
    {
        try{
            $data = $this->all();
            if(empty($data)){ throw new \Exception();}
            $ffmProcess = Di::getInstance()->get('ffmProcess');
            $ffmProcess->write($data['stream_key']);
            $this->retSrs();
        }catch (\Exception $exception){
            $this->retSrs(SRS_ERROR);
        }
    }

    public function onUnpublish()
    {
        try{
            $data = json_decode($this->raw(),true);
            if(empty($data)||!Stream::exists(stream_key($data['app'],$data['stream']))){throw new \Exception();}
            Stream::stop(stream_key($data['app'],$data['stream']));
            $this->retSrs();
        }catch (\Exception $exception){
            $this->retSrs(SRS_ERROR);
        }
    }

    public function onPlay()
    {
        try{
            $stream_key = trim($this->input('stream_key'));
            if(empty($stream_key)){
                throw new \Exception('参数错误',400);
            }
            if(!$row = Stream::get($stream_key)){ throw new \Exception('流媒体文件不存在',405);}
            $process =  Di::getInstance()->get('ffmProcess');
            $play = $process->write($stream_key);
            $localhost = Config::getInstance()->getConf('srs.localhost');
            $httpport = Config::getInstance()->getConf('srs.srs_config.http_server.listen');
            $httpport = substr($httpport,0,strpos($httpport,';'));
            $play ? $this->success([
                'RTMP'=> "rtmp://{$localhost}/{$row['app']}/{$row['stream_id']}",
                'HTTP-FLV'=> "http://{$localhost}:{$httpport}/{$row['app']}/{$row['stream_id']}.flv",
                'HLS'=> "http://{$localhost}:{$httpport}/{$row['app']}/{$row['stream_id']}.m3u8",
                'WebRTC'=> "webrtc://{$localhost}/{$row['app']}/{$row['stream_id']}",
            ],'播放成功') : $this->error();
        }catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function onClose()
    {
        try{
            $stream_key = trim($this->input('stream_key'));
            if(empty($stream_key)){
                throw new \Exception('参数错误',400);
            }
            Stream::stop($stream_key);
            $this->success();
        }catch (\Exception $exception){
            $this->retSrs();
        }
    }

    /**发送srs响应
     * @param int $code
     */
    private function retSrs(int $code=SRS_SUCCESS)
    {
        $this->response()->write($code);
        $this->response()->withStatus(200);
        $this->response()->end();
    }
}
