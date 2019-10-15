<?php
/**
 * @author         Remco van der Velde
 * @since         19-07-2015
 * @version        1.0
 * @changeLog
 *  -    all
 */
namespace Priya\Module\Javascript;

use Priya\Module\Core\Main;
use Priya\Application;
use Priya\Module\File\Dir;
use Priya\Module\File;
use Priya\Module\Data;
use Priya\Module\File\Zip;

class Cli extends Main {
    const DIR = __DIR__;
    const FILE = __FILE__;

    public function run(){
        if($this->parameter('create')){
            $this->createScript();
        }
    }

    public function createScript(){
        echo '[javascript] ';
        echo 'Reading Bootstrap.json...' . PHP_EOL;
        $url = dirname($this->data('module.dir.root')) . Application::DS  . 'Data' . Application::DS . 'Bootstrap.json';
        $this->read($url);
        $core = $this->data('require.core');
        $file = new File();
        $dirname =
            dirname($this->data('module.dir.root')) .
            Application::DS .
            $this->data('public_html') .
            Application::DS .
            'Js' .
            Application::DS .
            'Priya' .
             Application::DS .
             $this->data('priya.version') .
             Application::DS
        ;
        if(!is_dir($dirname)){
            mkdir($dirname, Dir::CHMOD, true);
        }
        $bin = dirname($this->data('module.dir.root')) . Application::DS . 'Data' . Application::DS . 'Bin' .  Application::DS;
        echo '[javascript] ';
        echo 'Copying Files...' . PHP_EOL;
        echo '[javascript] ';
        echo 'Reading Dir ('. $bin .') ...' . PHP_EOL;
        $dir = new Dir();
        $source_list  = $dir->read($bin, true);
        foreach($source_list as $source){
            if($source->type == Dir::TYPE){
                continue;
            }
            $explode = explode($bin, $source->url, 2);
            $target = $dirname . $explode[1];
            $target_dirname = dirname($target);
            if(!is_dir($target_dirname)){
                mkdir($target_dirname, Dir::CHMOD, true);
            }
            echo '[javascript] ';
            echo 'Copy ('.  $file->basename($target) .') ...' . PHP_EOL;
            $copy = $file->read($source->url);
            $copy = str_replace(
                array(
                    '{$priya.version}'
                ),
                array(
                    $this->data('priya.version')
                ),
                $copy
            );
            $file->write($target, $copy);
        }
//         $copyright = $this->data('priya.dir.data') . 'Copyright.txt';
        echo '[javascript] ';
        echo 'Creating Core...' . PHP_EOL;
        exec('priya copyright', $copyright);
        var_dump($copyright);
        die;
        $module = '';
        $read = $file->read($copyright);
        $module = str_replace(
            array(
                '{$version}',
                '{$built}'
            ),
            array(

            ),
            $read
        );
        $count = count($core);
        $counter = 0;
        foreach($core as $read){
            $location = dirname($this->data('module.dir.root')). Application::DS . 'Public' . Application::DS . 'Prototype' . Application::DS . 'Core' . Application::DS . $file->basename($read);
            $name = $file->basename($location, '.prototype.js');
            $module .= '/*' . "\n";
            $module .= ' * @name: ' . $name . "\n";
            $module .= ' * @url: ' . $location . "\n";
            $module .= ' */' . "\n";
            $module .= rtrim($file->read($location), "\n") . "\n" . "\n";
            $counter++;
            if($counter % 5 == 0){
                echo '[javascript] ';
                echo 'Progress: ' . $counter . '/' . $count . PHP_EOL;
            }
        }
        echo '[javascript] ';
        echo 'Progress: ' . $counter . '/' . $count . PHP_EOL;
        $target =
            dirname($this->data('module.dir.root')) .
            Application::DS .
            $this->data('public_html') .
            Application::DS .
            'Js' .
            Application::DS .
            'Priya' .
            Application::DS .
            $this->data('priya.version') .
            Application::DS .
            'Bin' .
            Application::DS .
            'Core-' .
            $this->data('priya.version') .
            '.js'
        ;
        $dirname = dirname($target);
        if(!is_dir($dirname)){
            mkdir($dirname, Dir::CHMOD, true);
        }
        $file = new File();
        $file->write($target, $module);
        echo '[javascript] ';
        echo 'created...' . PHP_EOL;
        echo '[javascript] ';
        echo $target . PHP_EOL;
        $source = dirname(dirname($target)) . Application::DS;
        $target = dirname($this->data('module.dir.root')) . Application::DS . $this->data('public_html') . Application::DS . 'Download' . Application::DS . 'Priya.Js-' .$this->data('priya.version') .'.zip';

        $archiver = $bin . 'Bin' . Application::DS . 'Archiver.json';
        exec('priya zip pack ' . $archiver, $output);
        foreach($output as $line){
            echo $line . PHP_EOL;
        }
        echo '[javascript] ';
        echo $target . PHP_EOL;
    }
}