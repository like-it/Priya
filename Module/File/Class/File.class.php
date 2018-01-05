<?php
/**
 * @author         Remco van der Velde
 * @since         19-07-2015
 * @version        1.0
 * @changeLog
 *  -    all
 */
namespace Priya\Module;

use stdClass;

class File {
    const CHMOD = 0640;
    const TYPE = 'File';
    const SCHEME_HTTP = 'http';

    public static function dir($directory=''){
        return str_replace('\\\/', DIRECTORY_SEPARATOR, rtrim($directory,'\\\/')) . DIRECTORY_SEPARATOR;
    }

    public static function mtime($url=''){
        return filemtime($url);
    }

    public static function info(stdClass $node){
        $rev = strrev($node->name);
        $explode = explode('.', $rev, 2);
        if(count($explode) == 2){
            $ext = strrev($explode[0]);
            $node->extension = strtolower($ext);
            $node->filetype = ucfirst(strtolower($ext)) . ' ' . strtolower(File::TYPE);
        } else {
            $node->extension = '';
            $node->filetype = File::TYPE;
        }
        $node->mtime = filemtime($node->url);
        $node->size = filesize($node->url);
        return $node;
    }

    public static function chown($url='', $owner=null, $group=null){
        if($owner === null){
            $owner = 'root:root';
        }
        if($group == null){
            $explode = explode(':', $owner, 2);
            if(count($explode) == 1){
                $group = $owner;
            } else {
                $owner = $explode[0];
                $group = $explode[1];
            }
        }
        exec('chown ' . $owner . ':' . $group . ' ' . $url, $output);
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
        if(strpos($url, File::SCHEME_HTTP) !== false){
            return implode('',file($url));
        }
        if(file_exists($url) === false){
            return false;
        }
        return implode('',file($url));
    }

    public function copy($source='', $destination=''){
        return copy($source, $destination);
    }

    public function delete($url=''){
        return unlink($url);
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

    public function removeExtension($filename='', $extension=array()){
        if(!is_array($extension)){
            $extension = array($extension);
        }
        foreach($extension as $ext){
            $ext = '.' . ltrim($ext, '.');
            $filename = explode($ext, $filename, 2);
            if(count($filename) > 1 && empty(end($filename))){
                array_pop($filename);
            }
            $filename = implode($ext, $filename);
        }
        return $filename;
    }
}