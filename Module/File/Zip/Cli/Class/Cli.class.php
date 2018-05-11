<?php
/**
 * @author         Remco van der Velde
 * @since         19-07-2015
 * @version        1.0
 * @changeLog
 *  -    all
 */
namespace Priya\Module\File\Zip;

use Priya\Module\Core\Main;

class Cli extends Main {
    const DIR = __DIR__;
    const FILE = __FILE__;

    public function run(){
        $end = microtime(true);
        $duration = $end - $this->data('time.application.run');
        var_dump($duration);
        var_dump($this->data('time'));
        die;
    }

}