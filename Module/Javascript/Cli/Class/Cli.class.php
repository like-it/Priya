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
use Priya\Module\Parser;
use Priya\Module\Data;

class Cli extends Main {
    const DIR = __DIR__;
    const FILE = __FILE__;

    public function run(){
        if($this->parameter('create')){
            $this->createScript();
        }

    }

    public function createScript(){
        echo 'Creating script... ()' . PHP_EOL;

//         $root = dirname($this->data('module.dir.root')) . Application::DS . $this->data('public_html') . Application::DS . 'Js' . Application::DS . 'Priya.js';
//         $priya = dirname($this->data('module.dir.root')) . Application::DS . $this->data('public_html') . Application::DS . 'Prototype' . Application::DS . 'Priya' . Application::DS . 'Priya.prototype.js';

        $dir = new Dir();
        $url = dirname($this->data('module.dir.root')) . Application::DS . $this->data('public_html') . Application::DS . 'Prototype' . Application::DS . 'Core' . Application::DS ;
        $core = $dir->read($url);

        $file = new File();

        $module = '';
        foreach($core as $read){
            $module .= '/*' . "\n";
            $module .= ' * @name: ' . $read->name . "\n";
            $module .= ' * @url: ' . $read->url . "\n";
            $module .= ' */' . "\n";
            $module .= rtrim($file->read($read->url), "\n") . "\n" . "\n";
        }


        $url = dirname($this->data('module.dir.root')) . Application::DS . $this->data('public_html') . Application::DS . 'Js' . Application::DS . 'Bin' . Application::DS . 'Core-' . $this->data('priya.version') . '.js';
        $dirname = dirname($url);

        if(!is_dir($dirname)){
            mkdir($dirname, Dir::CHMOD, true);
        }
        $file = new File();
        $file->write($url, $module);

        echo 'created...' . PHP_EOL;
        echo $url . PHP_EOL;
    }

}