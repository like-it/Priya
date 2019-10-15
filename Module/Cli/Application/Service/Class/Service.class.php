<?php
/**
 * @author         Remco van der Velde
 * @since         2016-10-19
 * @version        1.0
 * @changeLog
 *     -    all
 */

namespace Priya\Module\Cli\Application;

use Priya\Module\Core\Cli;
use Priya\Module\Data;
use Priya\Application;
use Priya\Module\File;
use Priya\Module\Cron;
use Exception;

class Service extends Cli {
    const NAMESPACE = __NAMESPACE__;
    const NAME = __CLASS__;    
    
    const DIR = __DIR__;
    const FILE = __FILE__;    
    
    public function run($object=null){
        if($object === null){
            $object = $this;
        }
        $class = __CLASS__;
        $object->read($class);
        $command = $object->parameter($class, 1);
        if(in_array($command, $object->data('command'))){
            // do nothing for now...
            // add logger
            // add minesweeper encryption ®aXon
        } else {
            $command = $object->data('default.command');
        }
        if(!method_exists($class, $command)){
            throw new Exception('Command (' . $command . ') not found');
        }
        return $class::{$command}($object);
    }
    
    public static function info($object){
        $class = __CLASS__;
        $object->data('command', ucfirst(__FUNCTION__));
        return $class::view($object, $object->data('command'));
    }        
    
    public static function cron($object){                         
        $object->data('command', ucfirst(__FUNCTION__));
        /**
         * read data cron for available commands
         * execute from here, initialize...
         * service commands will be overwritten after read...
         */
        
        $method = $object->parameter($object->data('command'), 1);
        
        $class = 'Priya\\Module\\' . $object->data('command');
        
        if(empty($method)){
            $method = $class::DEFAULT_COMMAND;
        }
        if(!method_exists($class, $method)){
            throw new Exception('Method not exist in ' . $class);
        }              
        return $class::run($object);        
    }
    
    /*
    private static function update($object){
        $url = $object->data('priya.dir.root') . License::FILE;

        $read = File::read($url);

        $list = License::view('location');
        foreach($list as $url){
            if(File::exist($url)){
                return File::write($url, $read);
            }
        }
    }

    private static function require($object, $execute=''){
        $url = $object->data('priya.dir.root') . strtoupper(License::php_class());
        if(!file_exists($url)){
            $execute = License::update(License::execute($this, 'url'));
            $file = new File();
            $file->write($url, $execute);
        } else {
            $file = new File();
            $read = $file->read($url);
            if($execute != $read){
                $file->write($url, $execute);
            }
        }
        return $execute;
    }
    */
}