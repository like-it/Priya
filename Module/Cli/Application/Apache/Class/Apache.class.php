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

class Apache extends Cli {
    const NAMESPACE = __NAMESPACE__;
    const NAME = __CLASS__;

    const DIR = __DIR__;
    const FILE = __FILE__;

    CONST DATA_DIR_APACHE = Application::DS . 'etc' . Application::DS . 'apache2' . Application::DS;
    const DATA_DIR_SITES_AVAILABLE = Apache::DATA_DIR_APACHE . 'sites-available' . Application::DS;
    const DATA_DIR_SITES_ENABLED = Apache::DATA_DIR_APACHE . 'sites-enabled' . Application::DS;

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

    public static function start($object){
        $class = __CLASS__;
        $object->data('command', ucfirst(__FUNCTION__));
        Apache::execute($object, 'service apache2 start');
        $object->data('command', ucfirst(__FUNCTION__));
        return $class::view($object, $object->data('command'));
    }

    public static function restart($object){
        $class = __CLASS__;
        $object->data('command', ucfirst(__FUNCTION__));
        Apache::execute($object, 'service apache2 restart', $output);
        $object->data('command', ucfirst(__FUNCTION__));
        return $class::view($object, $object->data('command'));
    }

    public static function stop($object){
        $class = __CLASS__;
        $object->data('command', ucfirst(__FUNCTION__));
        Apache::execute($object, 'service apache2 stop', $output);
        $object->data('command', ucfirst(__FUNCTION__));
        return $class::view($object, $object->data('command'));
    }

    public static function create($object){
        $class = __CLASS__;
        $host = $object->input('Host (example): ');
        $extension = $object->input('Extension (com): ');
        $nr = $object->input('Nr (01-99): ');
        $admin = $object->input('Server administrator: ');
//         $directory = $object->input('Installation directory: ')

        $object->data('node.host', $host);
        $object->data('node.extension', $extension);
        $object->data('node.admin', $admin);
        $object->data('node.directory', $object->data('dir.root') . $object->data('public_html'));

        $write = $class::view($object, 'Configuration');
        $domain = $host . '.' . $extension;
        $basename = $nr . '-' . $domain;
        $file =  $basename . '.conf';
        $object->data('node.domain', $domain);
        $object->data('node.basename', $basename);
        $object->data('node.file', $file);
        $url = '/etc/apache2/sites-available/' . $file;

        if(File::exist($url)){
            throw new Exception('File (' . $url . ') exists');
        }
        File::write($url, $write);
        $object->data('command', ucfirst(__FUNCTION__));
        return $class::view($object, $object->data('command'));
    }

    /**
     * backup original files
     * restore put back files and create symlinks (overwrite)
     */
    public static function backup($object){
        $class = __CLASS__;
        $object->data('command', ucfirst(__FUNCTION__));

        $cwd = $object->cwd();

        $dir = new Dir();
        $read = $dir->read(Apache::DATA_DIR_SITES_ENABLED);
        $read = Sort::natural($read);

        $module = File::basename($object->data('module.name'));

        $target = $object->data('dir.data') .
            Application::BACKUP .
            Application::DS .
            $module .
            Application::DS .
            date('Ymd Hi00') .
            Application::DS
        ;
        Dir::create($target, Dir::CHMOD);
        foreach($read as $nr => $file){
            $destination = $target . $file->name;
            if(file_exists($destination)){
                throw new Exception('Destination (' . $destination .') already exists');
            }
        }
        $counter = 0;
        foreach($read as $nr => $file){
            $dir_current = Dir::name($file->url);
            $object->cwd($dir_current);
            $destination = $target . $file->name;
            if(
                property_exists($file, 'link') &&
                $file->link == true
            ){
                $link = Link::read($file->url);
                if(file::exist($link)){
                    File::copy($link, $destination);
                }
            } else {
                File::copy($file->url, $destination);
            }
            $counter++;
        }
        $object->data('nodeList.target', $target);
        $object->data('nodeList.count', $counter);
        $object->cwd($cwd);
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

        $target_source = Apache::DATA_DIR_SITES_AVAILABLE;
        $target_link = Apache::DATA_DIR_SITES_ENABLED;

        $counter = 0;
        foreach($read as $nr => $file){
            $destination_source = $target_source . $file->name;
            $destination_link = $target_link . $file->name;
            $source_link = '../' . 'sites-available' . '/' . $file->name;

            File::delete($destination_link);
            File::delete($destination_source);

            File::copy($file->url, $destination_source);
            Link::create($source_link, $destination_link);
            $counter++;
        }
        $object->data('nodeList.count', $counter);
        $object->data('command', ucfirst(__FUNCTION__));
        return $class::view($object, $object->data('command'));
    }


    public static function enable($object){
        $class = __CLASS__;
        $object->data('command', ucfirst(__FUNCTION__));
        $domain = $object->parameter($object->data('command'), 1);
        if(empty($domain)){
            throw new Exception('Domain empty, please provide a domain');
        }
        $dir = new Dir();
        $read = $dir->read(Apache::DATA_DIR_SITES_AVAILABLE);
        $read = Sort::natural($read);
        foreach($read as $nr => $file){
            $name = File::removeExtension($file->name, File::extension($file->name));
            $explode = explode('-', $name, 2);
            if(
                isset($explode[1]) &&
                $explode[1] == $domain
            ){
                $target = Apache::DATA_DIR_SITES_ENABLED . $file->name;
                $object->data('node', $file);
                $object->data('node.target', $target);
                if(File::exist($target)){
                    throw new Exception('Target file (' . $target .') already exists');
                }
                Link::create($file->url, $target);
                break;
            }
        }
        $object->data('node.domain', $domain);
        $object->data('command', ucfirst(__FUNCTION__));
        return $class::view($object, $object->data('command'));
    }

    public static function disable($object){
        $class = __CLASS__;
        $object->data('command', ucfirst(__FUNCTION__));
        $domain = $object->parameter($object->data('command'), 1);
        $dir = new Dir();
        $read = $dir->read(Apache::DATA_DIR_SITES_AVAILABLE);
        $read = Sort::natural($read);
        foreach($read as $nr => $file){
            $name = File::removeExtension($file->name, File::extension($file->name));
            $explode = explode('-', $name, 2);
            if(
                isset($explode[1]) &&
                $explode[1] == $domain
                ){
                    $target = Apache::DATA_DIR_SITES_ENABLED . $file->name;
                    $object->data('node', $file);
                    $object->data('node.target', $target);
                    if(!File::exist($target)){
                        throw new Exception('Target file (' . $target .') already exists');
                    }
                    File::delete($target);
                    break;
            }
        }
        $object->data('node.domain', $domain);
        $object->data('command', ucfirst(__FUNCTION__));
        return $class::view($object, $object->data('command'));
    }



//     003-mondaytofriday.local.conf
    //create ask nr, host extension

    //next one edit hosts for local domains too and then add local domain with both commands



}