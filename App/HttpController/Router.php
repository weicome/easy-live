<?php

namespace App\HttpController;

use EasySwoole\Http\AbstractInterface\AbstractRouter;
use FastRoute\RouteCollector;

class Router extends AbstractRouter
{
    function initialize(RouteCollector $routeCollector)
    {
        // TODO: Implement initialize() method.
        $this->setGlobalMode(true);
        $routeCollector->addRoute('GET','/','/Index');
        $routeCollector->addRoute('GET','/test','/Index/test');
        $routeCollector->addRoute('GET','/close','/Index/onClose');
        //直播管理
        $this->addApi($routeCollector);
        //流管理
        $this->addSrs($routeCollector);

        $this->setMethodNotAllowCallBack(function (\EasySwoole\Http\Request $request,\EasySwoole\Http\Response $response){
            $response->withStatus(403)->write('未找到处理方法');
            return false; // 结束此次响应
        });
        $this->setRouterNotFoundCallBack(function (\EasySwoole\Http\Request $request,\EasySwoole\Http\Response $response){
            $response->withStatus(404)->write('未找到路由匹配');
            return false; // 重定向到 index 路由
        });
    }

    private function addApi(RouteCollector $routeCollector)
    {
        $routeCollector->addGroup('/api',function (RouteCollector $routeCollector){
            // 创建视频流
            $routeCollector->addRoute('POST','/create','/Api/create');
            $routeCollector->addRoute('PUT','/update','/Api/update');
            $routeCollector->addRoute('DELETE','/delete','/Api/destroy');
        });
    }

    private function addSrs(RouteCollector $routeCollector)
    {
        $routeCollector->addGroup('/srs',function (RouteCollector $routeCollector){
            // srs服务
            $routeCollector->addRoute('GET','/heart','/Srs/heartbeat');
            $routeCollector->addRoute('POST','/play','/Srs/onPlay');
            $routeCollector->addRoute('POST','/close','/Srs/onClose');
        });
    }
}
