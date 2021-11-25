<?php
//全局bootstrap事件
date_default_timezone_set('Asia/Shanghai');
EasySwoole\EasySwoole\Core::getInstance()->initialize();
\EasySwoole\Command\CommandManager::getInstance()->addCommand(new \App\Command\InstallSrs());
