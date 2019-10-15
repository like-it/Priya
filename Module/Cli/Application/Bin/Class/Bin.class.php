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
use Priya\Module\File;
use Exception;

class Bin extends Cli {
    const DIR = __DIR__;

    public function run(){
        $name = $this->parameter('bin', 1);
        if(empty($name)){
            $name = strtolower(Application::PRIYA);
        }
        $checkname = Bin::check_name($this, $name);
        if($checkname === false){
            throw new Exception('Name is not a valid name');
        }
        $bin = Bin::view($this, 'Create');
        $target = '/usr/bin/' . $name;
        $bytes = File::write($target, $bin);
        if($bytes > 0){
            Bin::execute('chmod +x ' . $target);
            return Bin::view($this);
        }
        throw new Exception('Could not create binary');
    }

    private static function check_name($object, $name=''){
        $list = $object->data('priya.application.execute');
        foreach($list as $item){
            $test = File::basename($item->command);
            if($test == $name){
                return true;
            }
        }
        return false;
    }


}