<?php

namespace App\Command;

use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\AbstractInterface\CommandInterface;
use SebastianBergmann\CodeCoverage\Report\PHP;

class InstallSrs implements CommandInterface
{

    public function commandName(): string
    {
        // TODO: Implement commandName() method.
        return 'srs';
    }

    public function exec(): ?string
    {
        // TODO: Implement exec() method.
        $setupZip=EASYSWOOLE_ROOT.'/storage/setup/srs-feature-gb28181.zip';
        if(!file_exists($setupZip)) {
            echo '安装包丢失，请重新下载:srs-feature-gb28181.zip'.PHP_EOL;
            return null;
        }
        $srsS =  substr($setupZip,0,strpos($setupZip,'.'));
        $srsN = dirname($setupZip).'/srs';
        echo "\e[32m 程序安装中,预计30分钟,请勿退出此界面 \e[0m \r\n";sleep(3);
        if(!is_dir($srsN)){
            if(!unZip($setupZip,dirname($setupZip).'')){
                echo  '解压失败，请检查权限！'.PHP_EOL;
                return null;
            }
            exec('mv '.$srsS. ' '. $srsN);
        }
        // 安装
        exec('chmod -R 777 '.$srsN);
        $osx = PHP_OS_FAMILY == 'Darwin' ? ' --osx ' : ' ';
        $cmd = 'cd '.$srsN.'/trunk && ./configure --full --prefix='.$srsN.'/trunk'.$osx.'--gb28181=on && make --jobs='.swoole_cpu_num();
        $process = proc_open($cmd,[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes,'/bin/bash');
        if(is_resource($process)){
            while ($ret = fgets($pipes[1])){
                echo ''.$ret;
            }
        }
        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);
        exec('cd '.$srsN.'/trunk && make --jobs='.swoole_cpu_num(),$out);
        echo implode($out,'\n').PHP_EOL;
        return null;

    }

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        // TODO: Implement help() method.
        $commandHelp->addAction('install','start install srs');
        return $commandHelp;
    }

    public function desc(): string
    {
        // TODO: Implement desc() method.
        return '安装SRS流媒体服务器';
    }
}
