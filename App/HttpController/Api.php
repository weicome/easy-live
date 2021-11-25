<?php

namespace App\HttpController;

use App\Util\Stream;

class Api extends BaseController
{
    // 创建一个直播流
    public function create()
    {
        try{
            $data = $this->all();
            if(empty($data['stream'])||empty($data['live_host'])|| empty($data['app']) || strlen($data['app'])>10){
                throw new \Exception('参数错误',400);
            }
            if(Stream::exists(stream_key($data['app'],$data['stream']))){
                throw new \Exception('该资源已存在',409);
            }
            Stream::set($data['stream'],$data['live_host'],$data['app']);
            $this->success(['stream_key'=>stream_key($data['app'],$data['stream'])]);
        }catch (\Exception $exception){
            throw $exception;
        }
    }

    // 更新
    public function update()
    {
        try{
            $data = $this->all();
            if(empty($data['stream_key'])||empty($data['stream']) || empty($data['live_host']) || empty($data['app']) || strlen($data['app'])>10){
                throw new \Exception('参数错误');
            }
            if(!Stream::exists($data['stream_key'])){
                throw new \Exception('该资源不存在');
            }
            Stream::del($data['stream_key']);
            Stream::set($data['stream'],$data['live_host'],$data['app']);
            $this->success(['stream_key'=>stream_key($data['app'],$data['stream'])]);
        }catch (\Exception $exception){
            throw  $exception;
        }
    }

    public function destroy()
    {
        try{
            $data = $this->all();
            if(empty($data['stream']) || empty($data['app'])){
                throw new \Exception('参数错误');
            }
            Stream::del(stream_key($data['app'],$data['stream']));
            $this->success([],'删除成功');
        }catch (\Exception $exception){
            throw  $exception;
        }
    }
}
