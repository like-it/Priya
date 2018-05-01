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

class Copyright extends Cli {
    const DIR = __DIR__;

    public function run(){
        $execute = Copyright::execute($this);
        return $execute;
    }
}