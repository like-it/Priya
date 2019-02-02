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

class Help extends Cli {
    const DIR = __DIR__;

    public function run(){
        $this->read(__CLASS__);
        return $this->result('cli');
    }
}
