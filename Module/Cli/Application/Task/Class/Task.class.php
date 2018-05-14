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

class Task extends Cli {
    const DIR = __DIR__;

    public function run(){
        $this->data('url', $this->parameter('task', 1));
        $this->read($this->data('url'));

        $task = $this->data('task');
        $execute = '';
        if(is_array($task)){
            foreach ($task as $node){
                $execute .=  Task::execute($this, $node);
            }
        }
        elseif(is_object($task)){
            $execute = Task::execute($this, $task);
        }
        return $execute;
    }

    public static function execute($object=null, $task=''){
        exec($task->command, $output);
        return implode(PHP_EOL, $output);
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