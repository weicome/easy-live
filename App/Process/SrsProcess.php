<?php

namespace App\Process;

use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\EasySwoole\Config;
use Swoole\Process;

class SrsProcess extends AbstractProcess
{
    /**启动文件
     * @var string
     */
    private $srsTempConfPath = TEMP_PATH.DIRECTORY_SEPARATOR.'srs.conf';

    protected function run($arg)
    {
        // TODO: Implement run() method.
        $instance = Config::getInstance();
        $config = $instance->getConf('srs');

        try{
            $this->loadSrsConfig($config['srs_path'],$config['srs_config']);
            $this->start($config['srs_path']);
        }catch (\Exception $exception){
            dump($exception->getMessage());
        }
    }
    /**加载配置文件
     * @param string $srsBasePath
     * @param array $srsConf
     * @return bool
     * @throws \Exception
     */
    private function loadSrsConfig(string $srsBasePath,array $srsConf)
    {
        //检测二进制文件
        if (!file_exists($srsBasePath.'/objs/srs')) {throw new \Exception('没有找到srs可执行程序');}
        if(empty($srsConf)){echo "srs配置信息加载失败\n\n";return false;}
        $srsConf=json_encode($srsConf);
        $srsConf=substr($srsConf, 1);
        $srsConf=substr($srsConf, 0, -1);
        $srsConf=str_replace("\",\"","\n",$srsConf);//替换掉每个属性的逗号
        $srsConf=str_replace("\":\"",' ',$srsConf);
        $srsConf=str_replace("\"",'',$srsConf);
        $srsConf=str_replace("\\",'',$srsConf);//url等的http:\/
        $srsConf=str_replace(",",'',$srsConf);
        $srsConf=str_replace(":{"," { \n",$srsConf);//键值对的 冒号
        $srsConf=str_replace(";}"," ;} \n",$srsConf);//键值对的 冒号
        $myConf = fopen($this->srsTempConfPath, "w");
        fwrite($myConf,$srsConf);
        fclose($myConf);
    }

    private function start(string $srsBasePath)
    {
        $this->getProcess()->exec($srsBasePath.'/objs/srs',['-c',$this->srsTempConfPath]);
    }

    protected function onPipeReadable(Process $process)
    {
        // TODO: Change the autogenerated stub
        $cli = $process->read();
        switch ($cli){
            case 'start':
            case 'status':
            case 'reload':
            case 'stop':
                break;
        }
    }

    protected function onShutDown()
    {
        parent::onShutDown(); // TODO: Change the autogenerated stub
    }

    protected function onException(\Throwable $throwable, ...$args)
    {
        parent::onException($throwable, $args); // TODO: Change the autogenerated stub
    }
}
