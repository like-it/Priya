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
use Priya\Module\File\Dir;

class File {
    const CHMOD = 0640;
    const TYPE = 'File';
    const SCHEME_HTTP = 'http';

    public static function is($url=''){
        $url = rtrim($url, Dir::SEPARATOR);
        return is_file($url);
    }

    public static function dir($directory=''){
        return str_replace('\\\/', Dir::SEPARATOR, rtrim($directory,'\\\/')) . Dir::SEPARATOR;
    }

    public static function mtime($url=''){
        return filemtime($url);
    }

    public static function diskusage($url=''){
        system('du ' . $url, $usage);
        var_dump($usage);
        exit;
    }

    public static function exist($url){ //File::exist means File has exist and not exist
        $url = rtrim($url, Dir::SEPARATOR);
        return file_exists($url);
    }

    public static function touch($url='', $time=null, $atime=null){
        if($atime === null){
            return @touch($url, $time);
        } else {
            return @touch($url, $time, $atime);
        }
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

    public static function chown($url='', $owner=null, $group=null, $recursive=false){
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
        if($recursive){
            exec('chown ' . $owner . ':' . $group . ' -R ' . $url, $output);
        } else {
            exec('chown ' . $owner . ':' . $group . ' ' . $url, $output);
        }
    }

    public static function write($url='', $data=''){
        $url = (string) $url;

        $data = Core::object($data, 'json');
        $data = (string) $data;
        $fwrite = 0;
        $resource = @fopen($url, 'w');
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

    public static function read($url=''){
        if(strpos($url, File::SCHEME_HTTP) !== false){
          //check network connection first (@) added for that              //error
         $file = @file($url);
         if(!is_array($file)){
            return false;
         }
            return implode('', $file);
        }
        if(file_exists($url) === false){
            return false;
        }
        return implode('',file($url));
    }

    public static function copy($source='', $destination=''){
        return copy($source, $destination);
    }

    public static function delete($url=''){
        return unlink($url);
    }

    public static function extension($url=''){
        $url = basename($url);
        $ext = explode('.', $url);
        if(!isset($ext[1])){
            $extension = '';
        } else {
            $extension = array_pop($ext);
        }
        return $extension;
    }

    public static function basename($url='', $extension=''){
        $filename = basename($url);
        $explode = explode('?', $filename, 2);
        $filename = $explode[0];
        $filename = basename($filename, $extension);
        return $filename;
    }

    public static function removeExtension($filename='', $extension=array()){
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