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
use Priya\Module\Core\Cli;
use Priya\Module\File\Dir;

class Cache extends Cli {
    const DIR = __DIR__;

    public function run(){
        if($this->parameter('clear')){
            Cache::clear($this, 'application');
            Cache::clear($this, 'smarty');
        }
        $this->data('delete', 'execute');
        return Cache::execute($this);
    }

    public static function clear($object, $type=''){
        $object->data('execute', $type);
        switch ($type){
            case 'smarty':
                echo Cache::execute($object);
                return Cache::clearSmarty($object);
            break;
            case 'application':
                echo Cache::execute($object);
                return Cache::clearApplication($object);
            break;
        }
    }

    private static function clearApplication($object){
        $url = $object->data('priya.dir.cache');
        $dir = new Dir();
        return $dir->delete($url);
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