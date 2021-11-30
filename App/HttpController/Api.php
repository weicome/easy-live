<?php

namespace App\HttpController;


use EasySwoole\Component\Di;
use EasySwoole\Component\TableManager;
use EasySwoole\EasySwoole\Config;

class Api extends BaseController
{
    // 创建一个直播流
    public function create()
    {
        try{
            $data = $this->raw();
            if(empty($data)){
                throw new \Exception('参数错误,live_host是必须的',400);
            }
            $data = json_decode($data,true);
            if(!isset($data['live_host'])||empty($data['live_host'])){
                throw new \Exception('参数错误,live_host是必须的',400);
            }
            $app = $this->input('live') ?: 'live';
            $live_host = $data['live_host'];
            $stream_id = $this->input('stream') ?: md5($live_host);
            $stream_key = stream_key($app,$stream_id);
            $streamTable = TableManager::getInstance()->get('stream');
            if($row = $streamTable->get($stream_key)){
                $row = json_decode($row['rows'],true);
                $play = true;
            }else{
                $streamTable->set($stream_key,['rows'=>json_encode(['app'=>$app,'stream_id'=>$stream_id,'rtsp_host'=>$live_host])]);
                $process =  Di::getInstance()->get('ffmProcess');
                $play = $process->write($stream_key);
                $row = ['app'=>$app,'stream_id'=>$stream_id,'rtsp_host'=>$live_host];
            }
            $localhost = Config::getInstance()->getConf('srs.localhost');
            $httpport = Config::getInstance()->getConf('srs.srs_config.http_server.listen');
            $httpport = substr($httpport,0,strpos($httpport,';'));
            $play ? $this->success([
                'stream_key' => $stream_key,
                'RTMP'=> "rtmp://{$localhost}/{$row['app']}/{$row['stream_id']}",
                'HTTP-FLV'=> "http://{$localhost}:{$httpport}/{$row['app']}/{$row['stream_id']}.flv",
                'HLS'=> "http://{$localhost}:{$httpport}/{$row['app']}/{$row['stream_id']}.m3u8",
                'WebRTC'=> "webrtc://{$localhost}/{$row['app']}/{$row['stream_id']}",
            ],'播放成功') : $this->error();
        }catch (\Exception $exception){
            throw $exception;
        }
    }

    public function destroy()
    {
        try{
            $data = json_decode($this->raw(),true);
            if(empty($data) || !isset($data['stream_key']) || empty($data['stream_key'])){
                throw new \Exception('参数错误',400);
            }
            $stream_key = $data['stream_key'];
            $processTable = TableManager::getInstance()->get('stream');
            if($processTable->exist($stream_key)){
                $process =  Di::getInstance()->get('ffmProcess');
                $process->write('close-'.$stream_key);
                $processTable->del($stream_key);
            }
            $this->success([],'删除成功');
        }catch (\Exception $exception){
            throw  $exception;
        }
    }
}
