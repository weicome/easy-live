<?php

namespace App\Util;

use EasySwoole\Component\TableManager;

class Stream
{
    private static $path = EASYSWOOLE_ROOT . DIRECTORY_SEPARATOR . 'storage/stream/';

    public function __construct()
    {
        if(!is_dir(self::$path)){
            mkdir(self::$path,0777,true);
        }
    }

    public static function get(string $stream_key)
    {
        if(self::exists($stream_key)){
            return json_decode(file_get_contents(self::$path.$stream_key),true);
        }
        return null;
    }

    // 设置
    public static function set(string $stream_id,string $rtspHost,string $app)
    {
        $f = fopen(self::$path.stream_key($app,$stream_id),'w');
        fwrite($f,json_encode([
            'stream_id' => $stream_id,
            'rtsp_host' => $rtspHost,
            'app' => $app,
        ]));
        fclose($f);
    }
    //删除
    public static function del(string $stream_key)
    {
        if(self::exists($stream_key)){
            // 删除文件
            return unlink(self::$path.$stream_key);
        }
        return false;
    }

    //结束指定流
    public static function stop(string $stream_key)
    {
        // 从内存中取得数据
        $streamTable = TableManager::getInstance()->get('stream');
//        $watchTable = TableManager::getInstance()->get('watch');
        if(!$streamTable->exist($stream_key)){return true;}
        $php_pid = $streamTable->get($stream_key,'php_pid');
        echo $php_pid.PHP_EOL;
        $skill =  \swoole_process::kill($php_pid,0);
        echo 'Swoole kill'.$skill.PHP_EOL;
        $kill =  exec("pkill -P {$php_pid}");
        echo 'exec kill'.$kill.PHP_EOL;
//            $watchTable->del($stream_key);
        $t =$streamTable->del($stream_key);
        echo 'streamTable del'.$t.PHP_EOL;
        self::del($stream_key);
    }

    public static function exists(string $stream_key): bool
    {
        return file_exists(self::$path.$stream_key);
    }
}
