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
use Priya\Module\File;
use Priya\Application;
use Exception;

class Version extends Cli {
    const DIR = __DIR__;

    public function run(){
        if($this->parameter('update')){
            $execute = $this->data('priya.application.binary.user.execute');
            //need in cache clear also object in application and in autoload data
            try {
                $output = [];
                Cli::execute($this, $execute . ' cache clear', $output);
                echo implode(PHP_EOL, $output);
                Version::update($this);
                $output = [];
                Cli::execute($this, $execute . ' version', $output);
                echo implode(PHP_EOL, $output);
            } catch (Exception $e) {
                echo $e;
            }
        } else {
            $this->data('php.version', PHP_VERSION);
            return $this->view($this);
        }
    }

    private static function update($object){
        $data = $object->request('data');
        $is_update = $object->parameter('update');

        $major = $object->parameter('update', 1);
        $minor = $object->parameter('update', 2);
        $patch = $object->parameter('update', 3);

        $version = null;
        if($major !== null){
            if(empty($minor) && empty($patch)){
                $temp = explode('.', $major, 3);
                $major = $temp[0];
                if(isset($temp[1])){
                    $minor = $temp[1] + 0;
                } else {
                    $minor = 0;
                }
                if(isset($temp[2])){
                    $patch = $temp[2] + 0;
                } else {
                    $patch = 0;
                }
            }
            elseif(empty($patch)){
                $patch = 0;
            }
        } else {
            $data = new Data();
            $data->read($object->data('priya.dir.data') . Application::CONFIG);

            $major = $data->data('priya.major') + 0;
            $minor = $data->data('priya.minor') + 0;
            $patch = $data->data('priya.patch') + 0;

            if($major <= 0){
                $major = 0;
            }
            if($minor <= 0){
                $minor = 0;
            }
            if($patch <= 0){
                $patch = 0;
            }
            $patch++;
        }
        $version = $major . '.' . $minor . '.' . $patch;
        $data = new Data();
        $data->read($object->data('priya.dir.data') . Application::CONFIG);
        $data->data('priya.major', $major);
        $data->data('priya.minor', $minor);
        $data->data('priya.patch', $patch);
        $data->data('priya.version', $version);
        $data->data('priya.built', date('Y-m-d H:i:s'));
        $write = $data->write();
        if($write > 0){
            return true;
        }
        throw new Exception('Failed to update to version: ' . $version);
    }

    /*
    private function update(){
        $data = $this->request('data');
        array_shift($data);
        array_shift($data);
        foreach($data as $parameter){
            if($parameter == '--update'){
                continue;
            }
            elseif($parameter == 'update'){
                continue;
            }
            $version = $parameter;
        }
        if(empty($version)){
            $major = $this->data('priya.major');
            $minor = $this->data('priya.minor');
            $patch = $this->data('priya.patch') + 1;
            $version = $major . '.' . $minor . '.' . $patch;
        }
        $explode = explode('.', $version);
        $count = count($explode);
        if($count == 1){
            $major = $explode[0] + 0;
            $minor = 0;
            $patch = 0;
        }
        elseif($count == 2){
            $major = $explode[0] + 0;
            $minor = $explode[1] + 0;
            $patch = 0;
        }
        elseif($count >= 3){
            $major = $explode[0] + 0;
            $minor = $explode[1] + 0;
            $patch = $explode[2] + 0;
        }
        $data = new Data();
        $data->read($this->data('priya.dir.data') . Application::CONFIG);
        $data->data('priya.major', $major);
        $data->data('priya.minor', $minor);
        $data->data('priya.patch', $patch);
        $data->data('priya.built', date('Y-m-d H:i:s'));
        $data->write();
    }
    */
}