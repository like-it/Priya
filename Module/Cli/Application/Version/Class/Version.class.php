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
            $this->update();
            exec('priya cache clear', $output);
            exec('priya version', $output);
            return implode(PHP_EOL, $output) . PHP_EOL;
        } else {
            return $this->result('cli');
        }

    }

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
}