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
        return Bin::response($this);
    }
}