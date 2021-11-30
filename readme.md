# 基于easyswoolePHP多进程+ffmpeg工具+SRS流媒体服务器

使用easyswoole的进程管理，调用ffmpeg产生推流，自带第三方开源流媒体服务器SRS

### 基本信息：
1.入门推荐书籍:

    FFmpeg从入门到精通

    Swoole从入门到精通

    SRS概述 必读!!!!!!!!!!!!!!

2.环境

    PHP => 7.4
    swoole => ^4.4
    easyswoole => ^3.4

3.使用
    
    1. composer install

    2. php easyswoole srs install

    3. php easyswoole server start

### 配置文件

修改config下的配置文件 srs.php
```
localhost => '你的ip'
```

### 测试
```
http://127.0.0.1:9501/test
```
返回
```
{
    "code": 200,
    "msg": "播放成功,记得关闭测试！",
    "data": {
        "RTMP": "rtmp://127.0.0.1/live/test123",
        "HTTP-FLV": "http://127.0.0.1:8080/live/test123.flv",
        "HLS": "http://127.0.0.1:8080/live/test123.m3u8",
        "WebRTC": "webrtc://127.0.0.1/live/test123"
    }
}
```
关闭测试
```
http://127.0.0.1:9501/close
```

### HTTP-API接口

1.添加设备
```
POST http://服务器ip:服务端口/api/create 
{
    "app": "live",   // 可选，默认live
    "stream": "唯一名称",  // 可选，默认md5(live_host)
    "live_host": "推流地址",//不同设备rtsp地址可能不一样	
}
```
返回值: 
```
{
    "code": 200,
    "msg": "播放成功,记得关闭!",
    "data": {
        "stream_key": "cc8174b483ec50c564e9b96541dcabc5",
        "RTMP": "rtmp://127.0.0.1/live/test123",
        "HTTP-FLV": "http://127.0.0.1:9580/live/test123.flv",
        "HLS": "http://127.0.0.1:9580/live/test123.m3u8",
        "WebRTC": "webrtc://127.0.0.1/live/test123"
    }
}
```
2.删除设备
```
POST  http://服务器ip:服务端口/api/destroy 
{
    "stream_key": "cc8174b483ec50c564e9b96541dcabc5"
}
```
3.为每个流签名保活
```
POST  http://服务器ip:服务端口/api/heartbeat 
{
    "keep_alive": [
        "cc8174b483ec50c564e9b96541dcabc5",
        "cc8174b483ec50c564e9b96541dcabc2",
    ]
}
```
每个直播流创建后有5分钟的保活时间，请在此执行定时保活
### 观看流视频
```
打开下面的页面播放流（若SRS不在本机，请将localhost更换成服务器IP）:

RTMP (by VLC): rtmp://localhost/live/livestream
H5(HTTP-FLV): http://localhost:8080/live/livestream.flv
H5(HLS): http://localhost:8080/live/livestream.m3u8
H5(WebRTC): webrtc://localhost/live/livestream
```

## 更多支持

请查询swoole文档

ffmpeg文档

SRS流媒体文档
