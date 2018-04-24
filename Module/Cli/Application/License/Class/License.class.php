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
            License::update(License::response($this, 'url'));
        }
        $response = License::response($this);
        $response = License::require($this, $response);
        return $response;
    }

    private static function update($target=''){
        $file = new File();
        $response = $file->read(License::URL);
        if(!empty($response)){
            $file->write($target, $response);
            return $response;
        }
    }

    private static function require($object, $response=''){
        $url = $object->data('priya.dir.root') . strtoupper(License::class());
        if(!file_exists($url)){
            $response = License::update(License::response($this, 'url'));
            $file = new File();
            $file->write($url, $response);
        } else {
            $file = new File();
            $read = $file->read($url);
            if($response != $read){
                $file->write($url, $response);
            }
        }
        return $response;
    }

}