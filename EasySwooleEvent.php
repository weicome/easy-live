<?php


namespace EasySwoole\EasySwoole;

use App\Process\FFmpegProcess;
use App\Process\SrsProcess;
use EasySwoole\Component\Di;
use EasySwoole\Component\Process\Manager;
use EasySwoole\Component\TableManager;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use Swoole\Table;

class EasySwooleEvent implements Event
{
    public static function initialize()
    {
        date_default_timezone_set('Asia/Shanghai');
        $instance = Config::getInstance();
        defined('TEMP_PATH') or define('TEMP_PATH', $instance->getConf('TEMP_DIR'));
        defined('LOG_PATH') or define('LOG_PATH', EASYSWOOLE_ROOT . '/Log');
        defined('SRS_ERROR') or define('SRS_ERROR', 1);
        defined('SRS_SUCCESS') or define('SRS_SUCCESS', 0);
    }

    public static function mainServerCreate(EventRegister $register)
    {
        self::loadConfig();
        self::initSwooleTable();
        self::initProcess();
        self::initTimer();
    }

    /**
     * 加载配置文件
     */
    public static function loadConfig()
    {
        $instance = Config::getInstance();
        foreach (glob(EASYSWOOLE_ROOT . DIRECTORY_SEPARATOR . 'config/*.php') as $filePath) {
            $instance->setConf(rtrim(basename($filePath), '.php'), require_once $filePath);
        }
    }

    /**
     * 初始化内存
     */
    public static function initSwooleTable()
    {
        $tm = TableManager::getInstance();
        $tm->add('process', ['php_pid' => ['type' => Table::TYPE_INT, 'size' => 11],], 1024);
        $tm->add('stream', ['rows' => ['type' => Table::TYPE_STRING, 'size' => 4096],], 1024);
        //        $tm->add('client',['stream_key'=>['type'=>Table::TYPE_STRING,'size'=>32]],1024);
    }

    /**
     * 初始化进程
     */
    public static function initProcess()
    {
        $srsProcessConfig = new \EasySwoole\Component\Process\Config([
            'processName' => 'SRS',
            'enableCoroutine' => true,
            'redirectStdinStdout' => false,
        ]);
        $srsProcess = new SrsProcess($srsProcessConfig);
        Di::getInstance()->set('srsProcess', $srsProcess->getProcess());
        Manager::getInstance()->addProcess($srsProcess);

        $ffmProcessConfig = new \EasySwoole\Component\Process\Config();
        $ffmProcessConfig->setProcessName('ffmpeg');
        $ffmProcessConfig->setPipeType(SOCK_STREAM);
        $ffmProcessConfig->setEnableCoroutine(true);
        $ffmProcessConfig->setRedirectStdinStdout(false);
        $ffmProcess = new FFmpegProcess($ffmProcessConfig);
        Di::getInstance()->set('ffmProcess', $ffmProcess->getProcess());
        Manager::getInstance()->addProcess($ffmProcess);
    }

    /**
     * 初始化定时任务
     */
    public static function initTimer()
    {
        // 每天午夜清空内存
        $timer_id = \Swoole\Timer::tick(1000 * 60, function () {
            $pid = TableManager::getInstance()->get('process')->destroy();
            $st = TableManager::getInstance()->get('stream')->destroy();
        });
        \Swoole\Timer::clear($timer_id);
    }
}
