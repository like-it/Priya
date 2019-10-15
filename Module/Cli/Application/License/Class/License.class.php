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

class License extends Cli {
    const DIR = __DIR__;
    const URL = 'https://priya.software/update/license';
    const FILE = 'LICENSE';

    public function run(){
        $update = $this->parameter('update');
        if($update){
            License::update($this);
        }
        $list = License::view('location');
        foreach($list as $url){
            if(File::exist($url)){
                return File::read($url);
            }
        }
    }

    private static function update($object){
        $url = $object->data('priya.dir.root') . License::FILE;

        $read = File::read($url);

        $list = License::view('location');
        foreach($list as $url){
            if(File::exist($url)){
                return File::write($url, $read);
            }
        }
    }

    private static function require($object, $execute=''){
        $url = $object->data('priya.dir.root') . strtoupper(License::php_class());
        if(!file_exists($url)){
            $execute = License::update(License::execute($this, 'url'));
            $file = new File();
            $file->write($url, $execute);
        } else {
            $file = new File();
            $read = $file->read($url);
            if($execute != $read){
                $file->write($url, $execute);
            }
        }
        return $execute;
    }

}