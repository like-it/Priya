<?php
/**
 * @author 		Remco van der Velde
 * @since 		2016-10-19
 * @version		1.0
 * @changeLog
 * 	-	all
 *
 *  DISPLAY=:0; export DISPLAY
 */

namespace Priya\Module\Cli\Application;

use Priya\Module\Core\Cli;
use ReflectionExtension;

class Sdl extends Cli {
    const DIR = __DIR__;

    public function run(){
        if($this->check('sdl') === false){
            $this->install('sdl');
        } else {
            return $this->result('cli');
        }
    }

    private function check($module=''){
        if(empty($module)){
            return false;
        }
        $loaded = extension_loaded($module);
        return $loaded;
    }

    private function install($module=''){
        $this->output('Module: ' . $module . ' needs install...' . PHP_EOL);
    }
}
