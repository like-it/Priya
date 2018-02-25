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
use Priya\Module\File\Dir;

class Check extends Cli {
    const DIR = __DIR__;

    public function run(){
        if($this->parameter('install')){
            $this->install();
        }
        return $this->result('cli');
    }

    private function install(){
        $dir = $this->data('dir.vendor');
        $explode = explode('Priya', $dir, 2);
        $test = explode(Application::DS, trim($explode[0], Application::DS));
        if(end($test) !== Application::VENDOR){
            $this->move();
        }
    }

    private function move(){
        $dir = $this->data('dir.vendor');
        $explode = explode(Application::VENDOR, $dir, 2);
        if(count($explode) == 2){
            //ok
        } else {
            $explode = explode(Application::DS, $dir);
            if($this->data('dir.root') == $this->data('dir.vendor')){
                $dir_root = dirname($this->data('dir.root'));
                $dir_vendor = $dir_root . Application::DS . Application::VENDOR;
                $dir_priya = $dir_vendor . Application::DS . Application::PRIYA;
                if(!is_dir($dir_priya)){
                    mkdir($dir_priya, Dir::CHMOD, true);
                }
                $dir = new Dir();
                $read = $dir->read($this->data('dir.vendor'), true);
                foreach($read as $nr => $file){
                    $file->destination = str_replace($this->data('dir.root'), $dir_priya . Application::DS, $file->url);
                    $read[$nr] = $file;
                }
                foreach($read as $nr => $file){
                    $dir = dirname($file->destination);
                    if(!is_dir($dir)){
                        var_dump($dir);
                        mkdir($dir, Dir::CHMOD, true);
                    }
                    if($file->type != File::TYPE){
                        continue;
                    }
                    copy($file->url, $file->destination);
                }
                var_dump($read);
                die;
                $file = new File();
                $url =
                    $dir_priya .
                    Application::DS .
                    Application::DATA .
                    Application::DS .
                    Application::BIN .
                    Application::DS .
                    Application::PRIYA .
                    '.tpl'
                ;
                var_dump($url);
                $read = $file->read($url);
                var_dump($read);
                die;
                //change bin in /usr/bin
            }
        }
    }
}