<?php
/**
 * @author         Remco van der Velde
 * @since         2016-10-19
 * @version        1.0
 * @changeLog
 *     -    all
 */

namespace Priya\Module\Cli\Application;

use Priya\Application;
use Priya\Module\Autoload;
use Priya\Module\Core\Cli;
use Priya\Module\File;
use Priya\Module\File\Dir;
use Priya\Module\Data;

class Cache extends Cli {
    const DIR = __DIR__;

    public function run(){
        if($this->parameter('clear')){
            Cache::clear($this, 'application');
            Cache::clear($this, 'smarty');
            Cache::clear($this, 'autoload');
            Cache::clear($this, 'route');
        }
        elseif($this->parameter('on')){
            Cache::on($this);
        }
        elseif($this->parameter('off')){
            Cache::off($this);
        }
    }

    public static function on($object){
        $object->data('execute', 'on');
        $url = $object->data('dir.data') . Application::CONFIG;
        if(File::exist($url)){
            $data = new Data();
            $data->read($url);
            $data->data('delete', 'priya.cache.disable');
            $data->write();
            echo Cache::execute($object);
            $object->data('delete', 'execute');
        }
    }

    public static function off($object){
        $object->data('execute', 'off');
        $url = $object->data('dir.data') . Application::CONFIG;
        if(File::exist($url)){
            $data = new Data();
            $data->read($url);
            $data->data('priya.cache.disable', true);
            $data->write();
        }
        if(File::exist($object->data('priya.cache.init.url'))){
            File::delete($object->data('priya.cache.init.url'));
        }
        echo Cache::execute($object);
        $object->data('delete', 'execute');
    }

    public static function clear($object, $type=''){
        $object->data('execute', $type);
        switch ($type){
            case 'application':
                echo Cache::execute($object);
                $result =  Cache::clearApplication($object);
            break;
            case 'autoload':
                echo Cache::execute($object);
                $result = Cache::clearAutoload($object);
            break;
            case 'route':
                echo Cache::execute($object);
                $result = Cache::clearRoute($object);
            break;
            case 'smarty':
                echo Cache::execute($object);
                $result = Cache::clearSmarty($object);
            break;
        }
        $this->data('delete', 'execute');
        return $result;
    }

    private static function clearApplication($object){
        $url = $object->data('priya.dir.cache');
        $dir = new Dir();
        return $dir->delete($url);
    }

    private static function clearAutoload($object){
        $url =
            dirname(Autoload::DIR) .
            Application::DS .
            Application::DATA .
            Application::DS  .
            Autoload::FILE
        ;
        if(!file_exists($url)){
            return false;
        }
        return File::delete($url);
    }

    private static function clearRoute($object){
        /*
        $url =
        dirname(Autoload::DIR) .
        Application::DS .
        Application::DATA .
        Application::DS  .
        Autoload::FILE
        ;
        return File::delete($url);
        */
    }

    private static function clearSmarty($object){
        $url =
            $object->data('priya.dir.module') .
              'Smarty' .
              Application::DS .
              'Data' .
              Application::DS
          ;
        $dir = new Dir();
        return $dir->delete($url);
    }
}