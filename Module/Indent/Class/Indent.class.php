<?php
/**
 * @author 		Remco van der Velde
 * @since 		2016-10-19
 * @version		1.0
 * @changeLog
 * 	-	all
 */
namespace Priya\Module;

class Indent extends \Priya\Module\Core\NodeList {
    const DIR = __DIR__;

    public function run(){
        $this->read(__CLASS__);
        return $this->result('template');
    }

    public function css(){
        $this->read(__CLASS__);
        return $this->result('template');
    }
}
