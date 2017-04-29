<?php
/**
 * @author 		Remco van der Velde
 * @since 		2016-10-19
 * @version		1.0
 * @changeLog
 * 	-	all
 */

namespace Priya\Module\Cli;

use Priya\Module\Core\Cli;

class Test extends Cli {
    const DIR = __DIR__;

    public function run(){
        $name = $this->parameter(2) ? $this->parameter(2) : 'help';
        $name = ucfirst(strtolower($name));
        return $this->result('cli', $name);
    }
}
