<?php
/**
 * @author         Remco van der Velde
 * @since         19-07-2015
 * @version        1.0
 * @changeLog
 *  -    all
 */
namespace Priya\Module\File\Zip;

use ZipArchive;
use Priya\Application;
use Priya\Module\File;
use Priya\Module\File\Dir;
use Priya\Module\Core\Cli as Core;

class Cli extends Core {
    const DIR = __DIR__;
    const FILE = __FILE__;

    public function run(){
        $pack = $this->parameter('pack');

        if($pack){
            $source = Cli::dir($this, $this->parameter('pack', 1));
            $target = Cli::dir($this, $this->parameter('pack', 2));

            Cli::pack($this, $source, $target);


            var_dump($source);
            var_dump($target);
            die;
        }
        $url = $this->parameter('task', 1);
        if($url){
            $this->data('url', $url);
            $this->read($this->data('url'));
            if($this->data('task')){
                $request = $this->data('task.request');
                switch(strtolower($request)){
                    case 'pack' :
                        $source = Cli::dir($this, $this->data('task.source'));
                        $target = Cli::dir($this, $this->data('task.target'));
                        $ignore = $this->data('task.ignore');
                        Cli::pack($this, $source, $target, $ignore);
                    break;
                    case 'extract':
                    break;
                }

            }
        }
    }

    private static function dir($object, $url=''){
        $name = Dir::name($url);
        if(empty($name)){
            return $object->cwd() . $url;
        }
        return $url;
    }

    public static function pack($object, $source, $target, $ignore=array()){
        if(Dir::is($source)){
            $object->output('[pack] Read directory: ' . $source . PHP_EOL);

            $dir = new Dir();
            $dir->ignore($ignore);
            $read = $dir->read($source, true);

            $object->request('date', date('Y-m-d' . ' ' . 'His'));

            $location = $object->data('priya.dir.data') . 'Pack' . Application::DS . $object->request('date') . Application::DS;

            if(!file_exists($location)){
                mkdir($location, Dir::CHMOD, true);
            }
            if(!is_dir($location)){
                return;
            }
            $count = count($read);
            $counter = 0;
            $mod = $count / 100;
            if($mod < 1){
                $mod = 1;
            }
            $object->output('[pack] Temp writing files: (amount: ' . $count  . ')'  . PHP_EOL);
            foreach($read as $file){
                $counter++;
                $src = $file->url;
                $src = explode($source, $src, 2);
                $file->target = $location . $src[1];

                if($file->type == File::TYPE){
                    copy($file->url, $file->target);
                } else {
                    mkdir($file->target, Dir::CHMOD, true);
                }
                if($counter % $mod == 0){
                    $object->output('[temp] ' . round(($counter / $count) * 100, 2) . '%' . PHP_EOL);
                }
                elseif($counter >= $count){
                    $object->output('[temp] ' . round(($counter / $count) * 100, 2) . '%' . PHP_EOL);
                }
            }
            Cli::create($object, $target, $location, $read);
            chmod($target, File::CHMOD);
            $object->output('[pack] Removing Directory: ' . $location . PHP_EOL);
            $dir = new Dir();
            $dir->delete($location);
            $object->output('[pack] Complete: ' . $target . PHP_EOL);
        } else {

        }


    }

    public static function create($object=null, $target='', $location='', $read=array()){
        $dirname = dirname($target);
        if(!is_dir($dirname)){
            mkdir($dirname, Dir::CHMOD, true);
        }
        if(!is_dir($dirname)){
            return;
        }
        $object->output('[archive] Creating archive: ' . $target  . PHP_EOL);

        $zip = new ZipArchive();
        $res = $zip->open($target, ZipArchive::CREATE);
        $count = count($read);
        if($count == 0){
            return;
        }
        $counter = 0;
        $mod = $count / 100;
        if($mod < 1){
            $mod = 1;
        }
        foreach($read as $node){
            $counter++;
            $skip = false;
            if($node->url == $target){
                $skip = true;
            }
            if($node->target == $target){
                $skip = true;
            }
            if($node->type != File::TYPE){
                $skip = true;
            }
            if(empty($skip)){
                $loc = explode($location, $node->target, 2);
                $loc = implode('', $loc);
                $zip->addFile($node->target, $loc);
            }
            if($counter % $mod == 0){
                $object->output('[archive] ' . round(($counter / $count) * 100, 2) . '%' . PHP_EOL);
            }
            elseif($counter >= $count){
                $object->output('[archive] ' . round(($counter / $count) * 100, 2) . '%' . PHP_EOL);
            }
        }
        $object->output('[archive] Closing archive...' . PHP_EOL);
        $zip->close();
    }
}