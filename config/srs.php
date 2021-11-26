<?php

$host = 'http://127.0.0.1:8080/srs';//不要瞎改
$rtmp_port = '1935';

return [
    'localhost' => '127.0.0.1',
    'srs_path' => EASYSWOOLE_ROOT . '/storage/setup/srs/trunk',
    'rtmp_port' => $rtmp_port,
    'srs_config' => [
        'listen' =>$rtmp_port.';',
        'max_connections' => '1000;',
        'pid' => TEMP_PATH . '/srs.pid' . ';',//pid文件路径
        'ff_log_dir' => TEMP_PATH . '/srs' . ';',
        'srs_log_tank' => 'file;',//日志文件打印位置  console|file
        'srs_log_level' => 'error;',//日志级别 从高到低 verbose|info|trace|warn|error
        'srs_log_file' => LOG_PATH . '/srs.log' . ';',
        'daemon' => 'off;',
        'http_api' => [
            'enabled' => 'off;',
            'listen' => '9585;',
        ],
        'http_server' => [
            'enabled' => 'on;',
            'listen' => '9580;',
            'dir' => EASYSWOOLE_ROOT.'/storage/setup/srs/trunk/objs/nginx/html;',
        ],
        'rtc_server' => [
            'enabled' => 'on;',
            'listen' => '8000;',
            # @see https://github.com/ossrs/srs/wiki/v4_CN_WebRTC#config-candidate
            'candidate' => '$CANDIDATE;',
        ],
        'vhost __defaultVhost__' => [
            'hls' => [
                'enabled' => 'on;'
            ],
            'http_remux' => [
                'enabled' => 'on;',
                'mount' => '[vhost]/[app]/[stream].flv;',
            ],
            'rtc' => [
                'enabled' => 'on;',
                # @see https://github.com/ossrs/srs/wiki/v4_CN_WebRTC#rtmp-to-rtc
                'rtmp_to_rtc' => 'off;',
                # @see https://github.com/ossrs/srs/wiki/v4_CN_WebRTC#rtc-to-rtmp
                'rtc_to_rtmp' => 'off;',
            ],
        ],
    ],
];
