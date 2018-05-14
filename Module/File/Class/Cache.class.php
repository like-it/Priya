<?php
/**
 * @author         Remco van der Velde
 * @since         19-07-2015
 * @version        1.0
 * @changeLog
 *  -    all
 */

namespace Priya\Module\File;

use Exception;
use Priya\Application;
use Priya\Module\File;
use Priya\Module\Core;
use Priya\Module\Data;

class Cache {
    const DIR = __DIR__;
    const DATA = 'Data';
    const CACHE = 'Cache';

    const MINUTE = '01';
    const TEN_MINUTE = '10';
    const HOUR = '60';

    const ERROR_EXPIRE = 1;
    const ERROR_CORRUPT = 10;

    public static function url($url='', $ext='.php'){
        $dir = Dir::name($url) . Application::DS;
        $sha1 = sha1($url);
        $url = $dir . $sha1 . $ext;
        return $url;
    }

    public static function extend($url, $extend='+1 minute'){
        $cache = Cache::url($url, '.json');
        if(!File::exist($cache)){
            return false;
        }
        if(is_numeric($extend)){
            $mtime = $extend + 0;
        } else {
            $mtime = strtotime($extend);
        }
        return File::touch($cache, $mtime);
    }

    public static function validate($url='', $extend='+1 minute'){
        $expired = Cache::read($url, 0);
        $data = new Data();
        $data->data($expired);

        $list = $data->data('priya.cache');

        $cache = false;
        $renew = false;
        if(is_array($list)){
            foreach($list as $file){
                if(!isset($file->read)){
                    throw new Exception('Cannot fetch read state in priya.cache');
                }
                if(!isset($file->url)){
                    throw new Exception('Cannot fetch url in priya.cache');
                }
                if($file->read === true){
                    //check if exists and mtime
                    if(!file::exist($file->url)){
                        //new cache
                        $renew = true;
                        break;
                    }
                    if(!isset($file->mtime)){
                        throw new Exception('Cannot fetch mtime in priya.cache');
                    }
                    if(file::mtime($file->url) != $file->mtime){
                        //new cache
                        $renew = true;
                        break;
                    }
                } else {
                    if(file::exist($file->url)){
                        //new cache
                        $renew = true;
                        break;
                    }
                }
            }
        }
        if(!$renew){
            Cache::extend($url, $extend);
            $cache = $expired;
        }
        return $cache;
    }

    public static function write($url='', $data='', $overwrite=false){
        $dir = Dir::name($url) . Application::DS;
        $cache = Cache::url($url, '.json');
        if(File::exist($cache)){
            if($overwrite === false){
                return true;
            }
        }
        if(!Dir::is($dir)){
            Dir::create($dir, Dir::CHMOD);
        }
        File::write($cache, Core::object($data, 'json line')); //production 'json line' //develop 'json'
        return true;
    }

    public static function read($url='', $expiration='+1 minute'){
        $cache = Cache::url($url, '.json');

        if(!File::exist($cache)){
            return false;
        }
        $mtime = File::mtime($cache);
        if(is_numeric($expiration)){
            $ttl = $expiration;
        } else {
            $ttl = strtotime($expiration, $mtime);
        }
        if($ttl < time() && !empty($ttl)){
            return Cache::ERROR_EXPIRE;
        }
        $read = File::read($cache);
        if(empty($read)){
            return Cache::ERROR_CORRUPT;
        }
        return Core::object($read, 'object');
    }

    public static function serialize($object, $url=''){
        $serialize = serialize($object);

        $dir = Dir::name($url) . Application::DS;
        if(!Dir::is($dir)){
            Dir::create($dir, Dir::CHMOD);
        }
        $cache = Cache::url($url, '.serialize');
        return File::write($cache, $serialize);
    }

    public static function deserialize($url='', $expiration='+1 minute'){
        $cache = Cache::url($url, '.serialize');
        if(!File::exist($cache)){
            return false;
        }
        $mtime = File::mtime($cache);
        if(is_numeric($expiration)){
            $ttl = $expiration;
        } else {
            $ttl = strtotime($expiration, $mtime);
        }
        if($ttl < time() && !empty($ttl)){
            return Cache::ERROR_EXPIRE;
        }
        $serialize = File::read($cache);
        return unserialize($serialize);
    }
}