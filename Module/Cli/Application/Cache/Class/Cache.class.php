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
use Exception;

class Cache extends Cli {
    const DIR = __DIR__;
    public function run(){
        $data = $this->request('data');
        if($this->parameter('clear')){
            echo Cache::view($this, 'Cache.Start');
            Cache::clear($this, 'application');
            Cache::clear($this, 'application.temp');
//             Cache::clear($this, 'smarty'); @unused since 2019-07-06
            Cache::clear($this, 'r3m.io');
            Cache::clear($this, 'autoload');
            echo Cache::view($this, 'Cache.End');
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
                try {
                    Cache::clearApplication($object);
                    echo Cache::view($object, 'Cache.Application');
                } catch (Exception $e) {
                    echo $e;
                }
            break;
            case 'autoload':
                try {
                    Cache::clearAutoload($object);
                    echo Cache::view($object, 'Cache.Autoload');
                } catch (Exception $e) {
                    echo $e;
                }
            break;
            case 'application.temp':
                try {
                    Cache::clearApplicationTemp($object);
                    echo Cache::view($object, 'Cache.Application.Temp');
                } catch (Exception $e) {
                    echo $e;
                }
            break;
            case 'smarty':
                try {
//                     Cache::clearRoute($object);
                    echo Cache::view($object, 'Cache.Smarty');
                } catch (Exception $e) {
                    echo $e;
                }
            break;
            case 'r3m.io':
                try {
                    Cache::clearR3mIo($object);
                    echo Cache::view($object, 'Cache.R3m.io');
                } catch (Exception $e) {
                    echo $e;
                }
                break;
        }
        $object->data('delete', 'execute');
        return;
    }
    private static function clearApplication($object){
        $url = $object->data('priya.dir.cache');
        $dir = new Dir();
        $dir->delete($url);
        $url = $object->data('priya.cache.init.url');
        if(file_exists($url)){
            $delete =  File::delete($url);
            if($delete === false){
                throw new Exception('Failed to clear Application cache...');
            }
        }
    }

    private static function clearR3mIo($object){
        /*
        $url = $object->data('priya.dir.cache');
        $dir = new Dir();
        $dir->delete($url);
        $url = $object->data('priya.cache.init.url');
        if(file_exists($url)){
            $delete =  File::delete($url);
            if($delete === false){
                throw new Exception('Failed to clear Application cache...');
            }
        }
        */
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
            $delete =  File::delete($url);
            if($delete === false){
                throw new Exception('Failed to clear Module\Autoload cache...');
            }
        }
    }
    private static function clearApplicationTemp($object){
        $url = $object->data('priya.dir.temp');
        if(Dir::exist($url)){
            Dir::remove($url);
        }
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
        $delete = $dir->delete($url);
        if($delete === false){
            throw new Exception('Failed to clear Smarty cache...');
        }
    }
}