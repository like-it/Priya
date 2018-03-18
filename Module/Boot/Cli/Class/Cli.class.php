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

class Cli extends Core_Cli {
    const DIR = __DIR__;
    const FILE = __FILE__;

    public function run(){
        $this->read($this->data('module.dir.data') . 'Bootstrap.json');
        $this->queue($this->data('queue'));
        sleep(5);
//         var_dump($this->data());
//         var_dump($this->request());
        die;


        if($this->parameter('create')){
            $this->createScript();
        }
    }

    public function queue($queue=''){
        if(!is_object($queue)){
            return;
        }
        $length = $this->length($queue);
        if($length == 0){
            return;
        }
        $color = 16;
        $background = 10;
        system('clear');
        $this->tput('position');
        $this->color($color, $background);
        $this->output('  QUEUE  ');
        $this->color('reset');
        $this->output(' ' . 'Processing (' . $length .') items...' . PHP_EOL);

        $counter = 0;
        $background = 11;
        $y = 10;
        $x = 0;
        foreach ($queue as $jid => $task){
            //collect the tasks in a string variable
            //use clear & home on each line with max lines


            $columns = $this->tput('columns');
            $rows = $this->tput('rows');
            //task content to echo & parse with {vga.position(x,y)}
            echo $this->tput('position', array($x, $y));
            $result = $this->task($task);
            echo $result;
            $ups = substr_count($result, PHP_EOL) + 1;
//             echo 'Ups: ' . $ups . PHP_EOL;
            $y+= $ups;

            if($y >= ($rows - ($ups + 1))){
//                 echo $this->tput('screen.write');
                $result = ob_get_contents();
                ob_end_clean();
                $this->tput('clear');
                $this->tput('home');
                var_dump($result);
//                 $restore = $this->tput('screen.restore');
//                 echo strlen($restore);
                die;
                /*
                for($i=0; $i< $ups; $i++){
                    echo $this->tput('cuu1');

//                     echo $this->tput('up');
                }
                */
                $y -= $ups;
            }



            $counter++;
            $percentage = $counter / $length;


            $round = round($percentage*100, 2);

            $output = '  ' . $round . '%  ';

            $width = strlen($output);
            $bar = str_repeat(' ', (round($percentage * $columns) - $width));

//             $this->display('position', 1, 10);
            echo $this->tput('position', array(0, ($rows - 1)));
            $this->color($color, $background);
            $this->output($bar . $output);
            $this->color('reset');
//             $this->output(PHP_EOL);
        }
    }

    public function task($task=''){
        if(!isset($task->execute)){
            throw new Exception('Boot::Cli:task:Task has no execute...');
        }
        if(!isset($task->color)){
            $task->color = 16;
        }
        if(!isset($task->background)){
            $task->background = 2;
        }
        if(!isset($task->title)){
            $task->title = $task->execute;
        }
        $this->color($task->color, $task->background);
        $this->output('  TASK  ');
        $this->color('reset');
        $this->output(' ' . $task->title . PHP_EOL);
        return shell_exec('priya task ' . $task->execute) . PHP_EOL;
    }

    public function length($object=''){
        $length = 0;
        if(!is_object($object)){
            return $length;
        }
        foreach($object as $child){
            $length++;
        }
        return $length;
    }
}
