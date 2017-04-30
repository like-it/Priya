<?php
/**
 * @author 		Remco van der Velde
 * @since 		19-07-2015
 * @version		1.0
 * @changeLog
 *  -	all
 */
namespace Priya\Module;

class File {
    const CHMOD = 0640;

    public static function dir($directory=''){
        return str_replace('\\\/', DIRECTORY_SEPARATOR, rtrim($directory,'\\\/')) . DIRECTORY_SEPARATOR;
    }

    public function write($url='', $data=''){
        $url = (string) $url;
        $data = (string) $data;
        $fwrite = 0;
        $resource = fopen($url, 'w');
        if($resource === false){
            return $resource;
        }
        $lock = flock($resource, LOCK_EX);
        for ($written = 0; $written < strlen($data); $written += $fwrite) {
            $fwrite = fwrite($resource, substr($data, $written));
            if ($fwrite === false) {
                break;
            }
        }
        if(!empty($resource)){
            flock($resource, LOCK_UN);
            fclose($resource);
        }
        if($written != strlen($data)){
            return false;
        } else {
            return $fwrite;
        }
    }

    public function read($url=''){
        if(file_exists($url) === false){
            return false;
        }
        return implode('',file($url));
    }

    public function extension($url=''){
        $ext = explode('.', $url);
        if(count($ext)==1){
            $extension = '';
        } else {
            $extension = array_pop($ext);
        }
        return $extension;
    }
}