<?php

namespace App\HttpController;

use App\Util\Stream;
use EasySwoole\Component\Di;
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
        $host = EASYSWOOLE_ROOT.DIRECTORY_SEPARATOR.'storage/setup/srs/trunk/doc/source.flv ';
        Stream::set('test123',$host,'live');
        $stream_key = stream_key('live','test123');
        $process =  Di::getInstance()->get('ffmProcess');
        $play = $process->write($stream_key);
        $localhost = Config::getInstance()->getConf('srs.localhost');
        $httpport = Config::getInstance()->getConf('srs.srs_config.http_server.listen');
        $httpport = substr($httpport,0,strpos($httpport,';'));
        $play ? $this->success([
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
//            $watchTable = TableManager::getInstance()->get('watch');
//            $clientTable = TableManager::getInstance()->get('client');
//            if(!$clientTable->exists($data['client_id'])){throw new \Exception();}
//            $clientStreamKey = $clientTable->get($data['client_id'],'stream_key');
//            $watchClient = $watchTable->get($clientStreamKey,'rows');
//            $watchClient = $watchClient ? json_decode($watchTable,true):[];
//            $clientTable->del($data['client_id']);
//            foreach ($watchClient as $key=>$client_id){
//                if($client_id==$data['client_id']){
//                    unset($watchClient[$key]);
//                }
//            }
//            if(empty($watchClient)){
//                Stream::stop($clientStreamKey);
//            }else{
//                $watchTable->set($clientStreamKey,['rows'=>json_encode($watchClient)]);
//            }
            Stream::stop($stream_key);
            $this->success();
        }catch (\Exception $exception){
            throw $exception;
        }
    }
}
