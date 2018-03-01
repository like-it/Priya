<?php
/**
 * @author         Remco van der Velde
 * @since         19-07-2015
 * @version        1.0
 * @changeLog
 *  -    all
 */
namespace Priya\Module;

use Priya\Module\Core\Main;
use Priya\Application;

class Javascript extends Main {
    const DIR = __DIR__;
    const FILE = __FILE__;

    public function run(){
        return $this->output();
    }

    public function output(){
        $url = $this->data('module.dir.data') .
            ucfirst($this->data('priya.environment')) .
            Application::DS .
            basename(str_replace('\\', Application::DS, __CLASS__)) .
            '.json';

        $data = new Data();
        $read = $data->read($url);
        if($read === false){
            return $read;
        }
        $parser = new Parser($this->data());
        foreach($read as $key => $value){
            $value = $parser->compile($value, $this->data());
            $this->data($key, $value);
            $parser->data($this->data());
            $read->$key = $value;
        }
        return $read;
    }

}