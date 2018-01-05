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

class Javascript extends Main {
    const DIR = __DIR__;
    const FILE = __FILE__;

    public function run(){
//         $this->read(__CLASS__);
        return $this->output();
    }

    public function output(){
        $data = new Data();
        $read = $data->read(__CLASS__);
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