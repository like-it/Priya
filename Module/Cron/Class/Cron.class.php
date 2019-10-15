<?php
/**
 * @author         Remco van der Velde
 * @since          27-07-2019
 * @version        1.0
 * @changeLog
 *  -    all
 */
namespace Priya\Module;

use Exception;
use Priya\Module\Core\Result;
use Priya\Module\File\Dir;
use Priya\Module\File;
use stdClass;

class Cron extends Result {
    const NAMESPACE = __NAMESPACE__;
    const NAME = __CLASS__;
    const DIR = __DIR__;
    const FILE = __FILE__;

    const DEFAULT_COMMAND = 'info';

    const LOCK = 'Cron/.Lock';

    public function run($object=null){
        if($object === null){
            $object = $this;
        }
        $class = __CLASS__;
        $object->read($class);
        $command = $object->parameter($class, 1);
        if(in_array($command, $object->data('command'))){
            // do nothing for now...
            // add logger
            // add minesweeper encryption ®aXon
        } else {
            $command = $object->data('default.command');
        }
        if(!method_exists($class, $command)){
            throw new Exception('Command (' . $command . ') not found');
        }
        return $class::{$command}($object);
    }

    public static function info($object){
        $class = __CLASS__;
        $object->data('command', ucfirst(__FUNCTION__));
        return $class::view($object, $object->data('command'));
    }

    public static function task($object){
        $list = [];
        $read = '';
        foreach($object->data('cron.dir') as $file){
            $read .= File::read($file->url);
        }
        $read = $object->compile($read);

        $explode = explode(PHP_EOL, $read);

        foreach($explode as $nr => $line){
            $line = trim($line);
            if(empty($line)){
                continue;
            }
            $record = new stdClass();
            $temp = explode(' ', $line, 6);

            if(isset($temp[5])){
                $record->minute = $temp[0];
                $record->hour = $temp[1];
                $record->day_of_the_month = $temp[2];
                $record->day_of_the_week = $temp[4];
                $record->month = $temp[3];
                $record->binary = $temp[5];
            }
            $list[] = $record;
        }
        $object->data('cron.task', $list);
    }

    public static function now_attribute($record, $date, $attribute=null){
        if(
            isset($record->$attribute) &&
            isset($date->$attribute)
            ){
                if(
                    !in_array(
                        $record->$attribute,
                        [
                            '*',
                            $date->$attribute
                        ]
                        )
                    ){
                        return false;
                } else {
                    return true;
                }
        }
        return false;
    }

    public static function now($record, $date){
        if(Cron::now_attribute($record, $date, 'minute') === false){
            return false;
        }
        if(Cron::now_attribute($record, $date, 'hour') === false){
            return false;
        }
        if(Cron::now_attribute($record, $date, 'day_of_the_month') === false){
            return false;
        }
        if(Cron::now_attribute($record, $date, 'month') === false){
            return false;
        }
        if(Cron::now_attribute($record, $date, 'day_of_the_week') === false){
            return false;
        }
        return true;
    }

    public static function stop(Core $object){
        $lock =  $object->data('cron.lock') !== null ? $object->data('cron.lock') : $object->data('dir.data') . Cron::LOCK;
        $read = File::read($lock);
        $explode = explode(';', $read);
        if(File::exist($lock)){
            File::delete($lock);
        }
        foreach($explode as $pid){
            if(empty($pid)){
                continue;
            }
            $output = [];
            $exec = 'ps -p ' . $pid;
            Core::execute($object, $exec, $output);
            if(array_key_exists(1, $explode)){
                $execute = 'kill '  . $pid;
                Core::async($object, $execute);
            } else {
                $object->data('error', 'no.running.process');
            }
        }
        $class = __CLASS__;
        $object->data('command', ucfirst(__FUNCTION__));
        return $class::view($object, $object->data('command'));
    }

    public static function lock(Core $object, $type=null){
        $lock =  $object->data('cron.lock') !== null ? $object->data('cron.lock') : $object->data('dir.data') . Cron::LOCK;
        if($type == 'url'){
            return $lock;
        }
        if(File::Exist($lock)){
            if($type == 'has'){
                return true;
            }
            $class = __CLASS__;
            $object->data('command', ucfirst(__FUNCTION__));
            $url = Cron::lock($object, 'url');
            $object->data('pid', File::read($url));
            echo $class::view($object, 'Busy');
            die;
        }
        elseif($type !== 'has'){
            $pid = getmypid();
            $read = File::read($lock);
            if(empty($read)){
                $write = File::Write($lock, $pid);
            } else {
                $write = File::Write($lock, $read . ';' . $pid);
            }
            return true;
        } else {
            return false;
        }
    }

    public static function start(Core $object){
        if(!is_array($object->data('cron.dir'))){
            throw new Exception('cron.dir not set properly');
            die;
        }
        if(Cron::lock($object, 'has') === false){
            Cron::service($object);
        } else {
            $class = __CLASS__;
            $object->data('command', ucfirst(__FUNCTION__));
            $url = Cron::lock($object, 'url');
            $object->data('pid', File::read($url));
            return $class::view($object, 'Busy');
        }
    }

    public static function schedule(Core $object){
        Cron::interactive();
        Cron::Lock($object, 'create');
        Cron::task($object);
        while(true){
            if(Cron::lock($object, 'has') === false){
                return;
            }
            $task = $object->data('cron.task');
            $time_current = microtime(true);
            $date = date('Y-m-d H:i:00', $time_current);
            $time_next = strtotime($date) + 60;
            $sleep = $time_next - $time_current;
            $usleep = $sleep * 1000000;
            usleep($usleep);
            echo 'Cron ticker: '. $date . PHP_EOL;
            $active = 0;
            $current = new stdClass();
            $current->minute = date('i', $time_next);
            $current->hour = date('H', $time_next);
            $current->day_of_the_month = date('j', $time_next);
            $current->month = date('n', $time_next);
            $current->day_of_the_week  = date('w', $time_next);
            if(
                $task !== null &&
                is_array($task)
            ){
                foreach($task as $record){
                    if(Cron::now($record, $current) === false){
                        continue;
                    }
                    $active++;
                    Cron::async($object, $record->binary);
                }
                if($active > 0){
                    $object->data('cron.active', $active);
                    //use template Active.tpl
                    //active to variable cron.active
                    echo 'Cron jobs tasks activated (' . $active .') at: '. $date . PHP_EOL;
                }
            }
        }
    }

    public static function service(Core $object){
        //init schedule as process...

        $command = $object->data('binary') . ' service cron schedule';
        Core::async($object, $command); //do async
        return;
    }
}