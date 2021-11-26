<?php

namespace App\HttpController;

use App\Util\Stream;
use EasySwoole\Component\Di;
use EasySwoole\Component\TableManager;
use EasySwoole\EasySwoole\Config;

class Api extends BaseController
{
    // 创建一个直播流
    public function create()
    {
        try{
            $live_host = $this->input('live_host');
            if(empty($live_host)){
                throw new \Exception('参数错误,live_host是必须的',400);
            }
            $app = $this->input('live') ?: 'live';
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
            $stream_key = $this->input('stream_key');
            if(empty($stream_key)){
                throw new \Exception('参数错误',400);
            }
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
            throw  $exception;
        }
    }
}
