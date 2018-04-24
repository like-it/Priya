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

class Licence extends Cli {
    const DIR = __DIR__;

    public function run(){
        $response = Licence::response($this);
        var_dump($response);
        die;
    }

}