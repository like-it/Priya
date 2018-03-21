<?php
/**
 * @author         Remco van der Velde
 * @since         19-07-2015
 * @version        1.0
 * @changeLog
 *  -    all
 */

namespace Priya\Module\File;

use Priya\Application;
use Priya\Module\File;
use Priya\Module\File\Dir;
use Priya\Module\Core;
use Priya\Module\Core\Object;

class Cache {
    const DIR = __DIR__;
    const DATA = 'Data';
    const CACHE = 'Cache';
    const BEGIN = '<?php $cache = ';
    const END = ';' . "\n";
    const MINUTE = '01';
    const TEN_MINUTE = '10';
    const HOUR = '60';

    public static function read($url=''){
        //sha1 only filename, rest of url is target...
        $dir = dirname($url) . Application::DS;
        $sha1 = sha1($url);
        $url = $dir . $sha1 . '.php';
        @include $url;
        if(isset($cache)){
            return $cache;
        }
    }

    public static function write($url='', $data='', $overwrite=false){
        //sha1 only filename, rest of url is target...
        $dir = dirname($url) . Application::DS;
        $sha1 = sha1($url);
        $url = $dir . $sha1 . '.php';
        if(file_exists($url)){
            if($overwrite === false){
                return true;
            }
        }
        $data = Core::object($data, 'array');
        $data = Cache::BEGIN . var_export($data, true) . Cache::END;
        if(!is_dir($dir)){
            Dir::create($dir, Dir::CHMOD);
        }
        $file = new File();
        $file->write($url, $data);
        return true;
    }
}