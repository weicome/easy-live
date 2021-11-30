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
        try {
            $data = $this->raw();
            if (empty($data)) {
                throw new \Exception('参数错误,live_host是必须的', 400);
            }
            $data = json_decode($data, true);
            if (!isset($data['live_host']) || empty($data['live_host'])) {
                throw new \Exception('参数错误,live_host是必须的', 400);
            }
            $app = $this->input('live') ?: 'live';
            $live_host = $data['live_host'];
            $stream_id = $this->input('stream') ?: md5($live_host);
            $stream_key = stream_key($app, $stream_id);
            $streamTable = TableManager::getInstance()->get('stream');
            $clientTable = TableManager::getInstance()->get('client');
            $watchProcess = Di::getInstance()->get('watchProcess');
            $msg = ['add'=>$stream_key];
            $watchProcess->write(json_encode($msg));
            if ($row = $streamTable->get($stream_key,'rows')) {
                $clientTable->incr($stream_key,'watch',1);
                $row = json_decode($row, true);
                $play = true;
            } else {
                $streamTable->set($stream_key, ['rows' => json_encode(['app' => $app, 'stream_id' => $stream_id, 'rtsp_host' => $live_host])]);
                $clientTable->set($stream_key, ['watch'=>1]);
                $process =  Di::getInstance()->get('ffmProcess');
                $play = $process->write($stream_key);
                $row = ['app' => $app, 'stream_id' => $stream_id, 'rtsp_host' => $live_host];
            }
            $localhost = Config::getInstance()->getConf('srs.localhost');
            $httpport = Config::getInstance()->getConf('srs.srs_config.http_server.listen');
            $httpport = substr($httpport, 0, strpos($httpport, ';'));
            $play ? $this->success([
                'stream_key' => $stream_key,
                'client_nums' => $clientTable->get($stream_key,'watch'),
                'RTMP' => "rtmp://{$localhost}/{$row['app']}/{$row['stream_id']}",
                'HTTP-FLV' => "http://{$localhost}:{$httpport}/{$row['app']}/{$row['stream_id']}.flv",
                'HLS' => "http://{$localhost}:{$httpport}/{$row['app']}/{$row['stream_id']}.m3u8",
                'WebRTC' => "webrtc://{$localhost}/{$row['app']}/{$row['stream_id']}",
            ], '播放成功') : $this->error();
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function destroy()
    {
        try {
            $data = json_decode($this->raw(), true);
            if (empty($data) || !isset($data['stream_key']) || empty($data['stream_key'])) {
                throw new \Exception('参数错误', 400);
            }
            $stream_key = $data['stream_key'];
            $streamTable = TableManager::getInstance()->get('stream');
            if ($streamTable->exist($stream_key)) {
                $clientTable = TableManager::getInstance()->get('client');
                if($cid = $clientTable->get($stream_key,'watch')){
                    if($cid > 1) {
                        $clientTable->decr($stream_key, 'watch', 1);
                    }else{
                        $cid = 0;
                        $watchProcess = Di::getInstance()->get('watchProcess');
                        $msg = ['clear'=>$stream_key];
                        $watchProcess->write(json_encode($msg));
                    }
                }else{
                    $cid = 0;
                    $watchProcess = Di::getInstance()->get('watchProcess');
                    $msg = ['clear'=>$stream_key];
                    $watchProcess->write(json_encode($msg));
                }
                $this->success(['stream_key' => $stream_key,'client_nums'=>$cid], '删除成功');
            }
            $this->error([], '删除失败');
        } catch (\Exception $exception) {
            throw  $exception;
        }
    }

    public function heartbeat()
    {
        $stream_key = json_decode($this->raw(),true);
        if(empty($stream_key) || !isset($stream_key['keep_alive']) || empty($stream_key['keep_alive'])){
            throw new \Exception('参数错误', 400);
        }
        $watchProcess = Di::getInstance()->get('watchProcess');
        foreach ($stream_key['keep_alive'] as $key){
            $msg = ['add'=>$key];
            $watchProcess->write(json_encode($msg));
        }
        $this->success();
    }
}
