<?php

namespace App\HttpController;

use EasySwoole\Http\AbstractInterface\Controller;

class BaseController extends Controller
{
    public function index()
    {
        parent::index(); // TODO: Change the autogenerated stub
        $file = EASYSWOOLE_ROOT.'/src/Resource/Http/404.html';
        $this->response()->withStatus(404)->write(file_get_contents($file));
    }

    protected function actionNotFound(?string $action)
    {
        // TODO: Change the autogenerated stub
        $this->response()->withStatus(404);
        $file = EASYSWOOLE_ROOT.'/vendor/easyswoole/easyswoole/src/Resource/Http/404.html';
        if(!is_file($file)){
            $file = EASYSWOOLE_ROOT.'/src/Resource/Http/404.html';
        }
        $this->response()->write(file_get_contents($file));
    }

    protected function input(?string $key)
    {
        return trim($this->request()->getRequestParam($key));
    }

    protected function all()
    {
        return $this->request()->getRequestParam();
    }

    protected function raw(): string
    {
        return $this->request()->getBody()->__toString();
    }

    protected function success($data=[],$msg='成功')
    {
        $this->response()->withAddedHeader('Content-type',"application/json;charset=utf-8")
        ->withStatus(200)->write(json_encode(['code'=>200,'msg'=>$msg,'data'=>$data]));
        $this->response()->end();
    }

    protected function error(array $data=[],$msg='失败', ?int $code = 200)
    {
        $this->response()->withAddedHeader('Content-type',"application/json;charset=utf-8")
        ->withStatus($code)->write(json_encode(['code'=>$code,'msg'=>$msg,'data'=>$data]));
        $this->response()->end();
    }

    protected function onException(\Throwable $throwable): void
    {
        // TODO: Change the autogenerated stub
        $this->response()
            ->withAddedHeader('Content-type',"application/json;charset=utf-8")
            ->withStatus($throwable->getCode() ?: 400)
            ->write(json_encode(['code'=>$throwable->getCode()?:400,'msg'=>$throwable->getMessage(),'data'=>[]]));
        $this->response()->end();
    }
}
