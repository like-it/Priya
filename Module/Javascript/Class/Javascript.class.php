<?php
/**
 * @author 		Remco van der Velde
 * @since 		19-07-2015
 * @version		1.0
 * @changeLog
 *  -	all
 */
namespace Priya\Module;

use Priya\Module\Core\Main;

class Javascript extends Main {

    public function run(){
//         $this->read(__CLASS__);
        return $this->output();
    }

    public function output(){
        $data = new Data();
        $read = $data->read(__CLASS__);
        $read = $this->parser('object')->compile($read, $this->data());
        var_dump($this->data('web'));
        var_dump($read);
        die;



//         $compile = $data->compile()


        $read = $data->read(__CLASS__);
//         $data = $this->data();

        var_dump($read);
        die;


    }
}