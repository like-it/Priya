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

class Version extends Cli {
    const DIR = __DIR__;

    public function run(){        
        if($this->parameter('update')){
            Version::update($this);
            exec('priya cache clear', $output);
            exec('priya version', $output);
            exec('priya', $output);
            return implode(PHP_EOL, $output) . PHP_EOL;
        } else {
            return Version::execute($this);
        }
    }

    private static function update($object){
        $data = $object->request('data');
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
            $major = $object->data('priya.major') + 0;
            $minor = $object->data('priya.minor') + 0;
            $patch = $object->data('priya.patch') + 1;
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
        $data->read($object->data('priya.dir.data') . Application::CONFIG);
        $data->data('priya.major', $major);
        $data->data('priya.minor', $minor);
        $data->data('priya.patch', $patch);
        $data->data('priya.built', date('Y-m-d H:i:s'));
        $data->write();
    }
}