<?php
/**
 * @author 		Remco van der Velde
 * @since 		2016-10-19
 * @version		1.0
 * @changeLog
 * 	-	all
 */

namespace Priya\Module\Cli\Application;

use Priya\Module\Core\Cli;
use ReflectionExtension;

class Gui extends Cli {
    const DIR = __DIR__;

    public function run(){
        if($this->check('wxWidgets') === false){
            $this->install('wxWidgets');
        } else {
            return $this->result('cli');
        }
    }

    private function check($module=''){
        if(empty($module)){
            return false;
        }
        echo phpversion();
        $loaded = extension_loaded($module);
        return $loaded;
    }

    private function install($module=''){
        $this->output('Module: ' . $module . ' needs install...' . PHP_EOL);
    }
}
