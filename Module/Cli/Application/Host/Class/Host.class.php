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
use Priya\Module\File;
use Priya\Module\File\Dir;
use Priya\Module\File\Link;
use Priya\Module\Sort;
use Priya\Application;
use Exception;

class Host extends Cli {
    const NAMESPACE = __NAMESPACE__;
    const NAME = __CLASS__;

    const DIR = __DIR__;
    const FILE = __FILE__;

    CONST DATA_FILE_HOST = Application::DS . 'etc' . Application::DS . 'hosts';

    public function run($object=null){
        if($object === null){
            $object = $this;
        }
        $class = __CLASS__;
        $object->read($class);
        $command = $object->parameter($class, 1);
        $list = $object->data('command');
        if($list == null || !is_array($list)){
            throw new Exception('Json command list is empty');
        }
        if(in_array($command, $list)){
            // do nothing for now...
            // add logger
            // add minesweeper encryption ®aXon
        } else {
            $command = $object->data('default.command');
        }
        if(empty($command)){
            throw new Exception('Command is empty use default.command');
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

    public static function create($object){
        $class = __CLASS__;
        $create_ip = $object->parameter('create', 1);
        $create_host = trim($object->parameter('create', 2));
        if(empty($create_host)){
            throw new Exception('At least 2 parameters required...');
        }
        $read = explode(PHP_EOL, File::read(Host::DATA_FILE_HOST));
        $is_found = false;
        foreach($read as $nr => $line){
            $line = trim($line);
            if(substr($line, 0, 1) == '#'){
                continue;
            }
            $explode = explode(' ', $line);
            if(!isset($explode[1])){
                $explode = explode("\t", $line);
            }
            $is_comment_start = false;
            $ip = null;
            $hostname = [];
            foreach($explode as $explode_nr => $part){
                $part = trim($part);
                if(empty($part)){
                    continue;
                }
                if(substr($part, 0, 1) == '#'){
                    break;
                }
                if(empty($ip)){
                    $ip = $part;
                } else {
                    $hostname[] = $part;
                }
            }
            if(in_array($create_host, $hostname)){
                $is_found = true;
            }
        }
        if($is_found == true){
            throw new Exception('Hostname (' . $create_host . ') exists in hostfile');
        }
        $string = $create_ip . str_repeat(' ', 4) . $create_host . ' # generated with Priya' . PHP_EOL;
        $write = File::append(Host::DATA_FILE_HOST, $string);
        if($write > 0){
            $object->data('node.host', $create_host);
            $object->data('command', ucfirst(__FUNCTION__));
            return $class::view($object, $object->data('command'));
        }
        throw new Exception('Could not write to file: ' . Host::DATA_FILE_HOST);
    }

    public static function delete($object){
        $class = __CLASS__;
        $delete_host = $object->parameter('delete', 1);
        $read = explode(PHP_EOL, File::read(Host::DATA_FILE_HOST));
        $is_found = false;
        foreach($read as $nr => $line){
            $line = trim($line);
            if(substr($line, 0, 1) == '#'){
                continue;
            }
            $explode = explode(' ', $line);
            if(!isset($explode[1])){
                $explode = explode("\t", $line);
            }
            $is_comment_start = false;
            $ip = null;
            $hostname = [];
            foreach($explode as $explode_nr => $part){
                $part = trim($part);
                if(empty($part)){
                    continue;
                }
                if(substr($part, 0, 1) == '#'){
                    break;
                }
                if(empty($ip)){
                    $ip = $part;
                } else {
                    $hostname[] = $part;
                }
            }
            if(in_array($delete_host, $hostname)){
                unset($read[$nr]);
                $is_found = true;
            }
        }
        if(empty($is_found)){
            throw new Exception('Could not find hostname: ' . $delete_host);
        } else {
            $write = implode(PHP_EOL, $read);
            File::write(Host::DATA_FILE_HOST, $write);
        }
    }

    /**
     * backup original files
     * restore put back files and create symlinks (overwrite)
     */
    public static function backup($object){
        $class = __CLASS__;
        $object->data('command', ucfirst(__FUNCTION__));

        $cwd = $object->cwd();
        $module = File::basename($object->data('module.name'));


        $target = $object->data('dir.data') .
            Application::BACKUP .
            Application::DS .
            $module .
            Application::DS .
            date('Ymd Hi00') .
            Application::DS
        ;
        $source = Host::DATA_FILE_HOST;
        $basename = File::basename($source);
        $destination = $target . $basename;
        if(file_exists($destination)){
            throw new Exception('Destination (' . $destination .') already exists');
        }
        Dir::create($target, Dir::CHMOD);
        File::copy($source, $destination);
        $object->data('command', ucfirst(__FUNCTION__));
        return $class::view($object, $object->data('command'));
    }

    public static function restore($object){
        $class = __CLASS__;
        $object->data('command', ucfirst(__FUNCTION__));
        $date = $object->parameter($object->data('command'), 1);
        $time = $object->parameter($object->data('command'), 2);
        $module = File::basename($object->data('module.name'));

        $dir_backup = $object->data('dir.data') .
        Application::BACKUP .
        Application::DS .
        $module .
        Application::DS;

        if(empty($date) || $date == 'list'){
            $date = 'list';

            $dir = new Dir();
            $read = $dir->read($dir_backup);
            $read = Sort::natural($read, Sort::ATTRIBUTE_NAME, SORT::ATTRIBUTE_DESCENDING);

            foreach($read as $nr => $entry){
                if($entry->type != Dir::TYPE){
                    continue;
                }
                $temp = $dir->read($entry->url);
                $entry->amount = count($temp);
            }
            $object->data('nodeList', $read);
            $object->data('command', ucfirst(__FUNCTION__) . '.' . ucfirst($date));
            return $class::view($object, $object->data('command'));
        }
        $dir_point = $dir_backup . $date . ' ' . $time . Application::DS;
        if(!file_exists($dir_point)){
            //maybe list with error <code> so i can show a nice error
            throw new Exception('Restore point doesn\'t exist');
        }

        $dir = new Dir();
        $read = $dir->read($dir_point);
        $read = Sort::natural($read);

        $target_source = Dir::name(host::DATA_FILE_HOST);

        $counter = 0;
        foreach($read as $nr => $file){
            $destination_source = $target_source . $file->name;
            File::delete($destination_source);
            File::copy($file->url, $destination_source);
            $counter++;
        }
        $object->data('nodeList.count', $counter);
        $object->data('command', ucfirst(__FUNCTION__));
        return $class::view($object, $object->data('command'));
    }
}