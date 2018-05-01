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

    public function run(){
        $update = $this->parameter('update');
        if($update){
            License::update(License::execute($this, 'url'));
        }
        $execute = License::execute($this);
        $execute = License::require($this, $execute);
        return $execute;
    }

    private static function update($target=''){
        $file = new File();
        $execute = $file->read(License::URL);
        if(!empty($execute)){
            $file->write($target, $execute);
            return $execute;
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