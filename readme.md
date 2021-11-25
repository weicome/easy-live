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
    "app": "live",
    "stream": "唯一名称",
    "live_host": "推流地址",//不同设备rtsp地址可能不一样	
}
```
2.编辑设备
```
POST  http://服务器ip:服务端口/api/update 
{
    "stream_key" : "32位字符串",
    "app": "live"
    "stream": "唯一名称",
    "live_host": "推流地址",//不同设备rtsp地址可能不一样
}
```
3.删除设备
```
POST  http://服务器ip:服务端口/api/destroy 
{
    "stream": "唯一名称",
    "app": "live"
}
```
4.开始播放
```
POST 127.0.0.1:9501/srs/play
{
    "stream_key": "50a3cb58a81223ef57c6ff611af7e297"
}
```
5.停止播放
```
POST 127.0.0.1:9501/srs/close
{
    "stream_key": "50a3cb58a81223ef57c6ff611af7e297"
}
```
停止播放后记得删除设备信息，再次调用删除接口

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
