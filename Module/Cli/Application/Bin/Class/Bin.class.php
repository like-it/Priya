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

class Bin extends Cli {
    const DIR = __DIR__;

    public function run(){
        $this->data('response', 'binary');
        $this->data('binary', Bin::response($this));
        $this->data('response', 'create');
        echo Bin::response($this);

        die;

        var_dump($this->data('module.dir.data'));
        die;


        if($this->parameter('clear')){
            Cache::clear($this, 'application');
            Cache::clear($this, 'smarty');
        }
        $this->data('delete', 'response');
        return Cache::response($this);
        return $this->result('cli');
    }

    public static function clear($object, $type=''){
        $object->data('response', $type);
        switch ($type){
            case 'smarty':
                echo Cache::response($object);
                return Cache::clearSmarty($object);
            case 'application':
                echo Cache::response($object);
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