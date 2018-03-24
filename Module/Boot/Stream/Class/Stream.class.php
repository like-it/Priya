<?php
/**
 * @author         Remco van der Velde
 * @since         19-07-2015
 * @version        1.0
 * @changeLog
 *  -    all
 */
namespace Priya\Module\Boot;

use Priya\Module\Core\Cli as Core_Cli;
use Exception;
use Priya\Module\Parse;

class Stream extends Core_Cli {
    const DIR = __DIR__;
    const FILE = __FILE__;
    const EXT = '.stream';

    const BOOT = Stream::DIR . '/../' . 'Boot' . '/';
    const HTTP = Stream::DIR . '/../' . 'Http' . '/';
    const TASK = Stream::DIR . '/../' . 'Task' . '/';

    public function run(){
        $parse = new Parse($this->handler(), $this->route(), $this->data());
        $boot = trim($this->request('boot'), " \n");
        if($boot){
            $read = $parse->read(Stream::BOOT . ucfirst($boot) . Stream::EXT);
            echo $read . PHP_EOL;
        }
    }
}
