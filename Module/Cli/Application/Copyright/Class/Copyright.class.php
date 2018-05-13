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
        $this->data('type', $this->parameter('type', 1));
        if($this->data('type') == 'document'){
            $execute = Copyright::document(Copyright::execute($this));
        } else {
            $execute = Copyright::execute($this);
        }
        return $execute;
    }

    public static function document($output=''){
        $result = '/**';
        $explode = explode(PHP_EOL, $output);
        foreach($explode as $line){
            $result .= ' * ' . $line . PHP_EOL;
        }
        $result .= '**/';
        return $result;
    }
}