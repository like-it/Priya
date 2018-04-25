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
        return Cache::response($this);
        return $this->result('cli');
    }

    public static function clear($object, $type=''){
        switch ($type){
            case 'smarty':
                $object->output('Clearing Smarty cache...' . PHP_EOL);
                return Cache::clearSmarty($object);
            case 'application':
                $object->output('Clearing Application cache...' . PHP_EOL);
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