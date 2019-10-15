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
use Priya\Module\Data;

class Cache {
    const DIR = __DIR__;
    const DATA = 'Data';
    const CACHE = 'Cache';

    const CONFIG = [
        '+ 1 minute' => 60,
        '+ 2 minutes' => 120,
        '+ 3 minutes' => 180,
        '+ 4 minutes' => 240,
        '+ 5 minutes' => 300,
        '+ 10 minutes' => 600,
        '+ 15 minutes' => 900,
        '+ 20 minutes' => 1200,
        '+ 25 minutes' => 1500,
        '+ 30 minutes' => 1800,
        '+ 35 minutes' => 2100,
        '+ 40 minutes' => 2400,
        '+ 45 minutes' => 2700,
        '+ 50 minutes' => 3000,
        '+ 55 minutes' => 3300,
        '+ 60 minutes' => 3600,
        '+ 1 hour' => 3600,
        '+ 2 hours' => 7200,
        '+ 3 hours' => 10800,
        '+ 6 hours' => 21600,
        '+ 9 hours' => 32400,
        '+ 12 hours' => 43200,
        '+ 15 hours' => 54000,
        '+ 18 hours' => 64800,
        '+ 21 hours' => 75600,
        '+ 24 hours' => 86400,
        '+ 1 day' => 86400,
        '+ 2 days' => 172800,
        '+ 3 days' => 259200,
        '+ 4 days' => 345600,
        '+ 5 days' => 432000,
        '+ 6 days' => 518400,
        '+ 7 days' => 604800,
        '+ 1 week' => 604800,
        '+ 2 weeks' => 1209600,
        '+ 3 weeks' => 1814400,
        '+ 4 weeks' => 2419200,
        '+ 1 month' => 2518500,
        '+ 2 months' => 5037000,
        '+ 3 months' => 7555500,
        '+ 4 months' => 10074000,
        '+ 5 months' => 12592500,
        '+ 6 months' => 15111000,
        '+ 9 months' => 22666500,
        '+ 12 months' => 30222000,
        '+ 1 year' => 31536000,
        '+ 2 years' => 63072000,
        '+ 3 years' => 94608000,
        '+ 4 years' => 126144000,
        '+ 5 years' => 157680000,
    ];
    const DEFAULT = '+ 1 minute';

    const MINUTE = '01';
    const TEN_MINUTE = '10';
    const HOUR = '60';

    const ERROR_EXPIRE = 1;
    const ERROR_CORRUPT = 10;

    const EXCEPTION_FILE_READ = 'Cannot fetch read state in priya.cache';
    const EXCEPTION_FILE_URL = 'Cannot fetch url in priya.cache';
    const EXCEPTION_FILE_MTIME = 'Cannot fetch mtime in priya.cache';

    public static function url($url='', $ext='.php'){
        $dir = Dir::name($url) . Application::DS;
        $sha1 = sha1($url);
        $url = $dir . $sha1 . $ext;
        return $url;
    }

    public static function extend($url='', $extend='+1 minute'){
        if(!File::exist($url)){
            return false;
        }
        if(is_numeric($extend)){
            $mtime = time() + $extend + 0;
        } else {
            $mtime = strtotime($extend);
        }
        return File::touch($url, $mtime);
    }

    public static function validate($url='', $extend='+1 minute', $key=''){
        $expired = Cache::read($url, 0);
        $data = new Data();
        $data->data($expired);
        if(empty($key)){
            $key = 'priya.cache.file';
        }
        $list = $data->data($key);
        $cache = false;
        $renew = false;
        if(is_array($list)){
            foreach($list as $file){
                if(!isset($file->read)){
                    throw new Exception(Cache::EXCEPTION_FILE_READ);
                }
                if(!isset($file->url)){
                    throw new Exception(Cache::EXCEPTION_FILE_URL);
                }
                if($file->read === true){
                    //check if exists and mtime
                    if(!File::exist($file->url)){
                        //new cache
                        $renew = true;
                        break;
                    }
                    if(!isset($file->mtime)){
                        throw new Exception(Cache::EXCEPTION_FILE_MTIME);
                    }
                    if(file::mtime($file->url) != $file->mtime){
                        //new cache
                        $renew = true;
                        break;
                    }
                } else {
                    if(File::exist($file->url)){
                        //new cache
                        $renew = true;
                        break;
                    }
                }
            }
        }
        if(!$renew){
            $ext = Cache::extend($url, $extend);
            $cache = $expired;
        }
        return $cache;
    }

    public static function write($url='', $data='', $overwrite=false){
        $dir = Dir::name($url) . Application::DS;
        if(File::exist($url)){
            if($overwrite === false){
                return true;
            }
        }
        if(!Dir::is($dir)){
            Dir::create($dir, Dir::CHMOD);
        }
        return File::write($url, $data);
    }

    /*
    public static function write($url='', $data='', $overwrite=false, $type='object.php'){
        $dir = Dir::name($url) . Application::DS;
        $cache = Cache::url($url, '.' . $type);
        if(File::exist($cache)){
            if($overwrite === false){
                return true;
            }
        }
        if(!Dir::is($dir)){
            Dir::create($dir, Dir::CHMOD);
        }
        if(in_array($type,[
            'json'
        ])){
            return File::write($cache, Core::object($data, 'json line')); //production 'json line' //develop 'json'
        } else {
            $data = var_export($data, true);
            $data = str_replace('stdClass::__set_state', '(object)', $data);
            $data = '<?php $read = ' . $data . ';';
            return File::write($cache, $data);
            return true;
        }
    }
    */

    public static function read($url='', $expiration='+1 minute'){
        if(!File::exist($url)){
            return false;
        }
        $debug = debug_backtrace(true);

        var_dump($debug);
        die;
        $mtime = File::mtime($url);
        if(is_numeric($expiration)){
            if($expiration == 0){
                $ttl = $expiration;
            } else {
                $ttl = $expiration + $mtime;
            }

        } else {
            $ttl = strtotime($expiration, $mtime); //+/- 30 msec for +1 minute
        }
        $time = time();
        if(!empty($ttl) && $ttl < $time){
            return Cache::ERROR_EXPIRE;
        }
        $read = File::read($url);
        if(empty($read)){
            return Cache::ERROR_CORRUPT;
        }
        return json_decode($read);
    }


    /*
    public static function read($url='', $expiration='+1 minute'){
        $extension = File::extension($url);
        if($extension == 'json'){
            $cache = Cache::url($url, '.json');
        }
        elseif($extension == 'export'){
            $cache = Cache::url($url, '.export');
        }
        elseif($extension == 'php'){
                $cache = Cache::url($url, '.object.php');
        } else {
            throw new Exception('Extension ('. $extension .') not supported in Cache::read...');
        }
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
        if($extension == 'json'){
            $read = File::read($cache);
            if(empty($read)){
                return Cache::ERROR_CORRUPT;
            }
            return json_decode($read);
            return Core::object($read, 'object');
        } else {
            @include_once $cache;
            if(empty($read)){
                return Cache::ERROR_CORRUPT;
            }
            return $read;
        }
    }
    */

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