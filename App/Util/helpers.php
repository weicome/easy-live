<?php


function stream_key(string $app, string $stream_id)
{
    return md5($app.$stream_id);
}

function unZip($filepath, $extractTo) :bool
{
    $zip = new ZipArchive();
    $res = $zip->open($filepath);
    if($res == TRUE){
        $zip->extractTo($extractTo);
        $zip->close();
        return true;
    }else{
        echo 'failed, code:'.$res;
        return false;
    }
}
