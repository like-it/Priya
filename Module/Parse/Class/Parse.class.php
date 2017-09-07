<?php
/**
 * @author 		Remco van der Velde
 * @since 		2016-10-19
 * @version		1.0
 * @changeLog
 * 	-	all
 */

namespace Priya\Module;

use stdClass;
use Priya\Application;
use Priya\Module\Core\Object;

class Parse extends Data {
    const DIR = __DIR__;
    const LITERAL = 'literal';
    const PHP_MIN_VERSION = '7.0.0';
    const FUNCTION_LIST = 'Function.List.php';

    private $random;

    public function __construct($handler=null, $route=null, $data=null){
        $this->random(rand(1000,9999) . '-' . rand(1000,9999) . '-' . rand(1000,9999) . '-' . rand(1000,9999));
        if($data !== null){
            $this->handler($handler);
            $this->route($route);
            $this->data($this->object_merge($this->data(), $data));
        } else {
            $this->data($this->object_merge($this->data(), $handler));
        }
    }

    public function random($random=null){
        if($random !== null){
            $this->setRandom($random);
        }
        return $this->getRandom();
    }

    private function setRandom($random=''){
        $this->random = $random;
    }

    private function getRandom(){
        return $this->random;
    }

    public function compile($string, $data, $keep=false){
        $newline = new Parse\Newline($string, $this->random());
        $string = $newline->replace();

        $tag = new Parse\Tag($string);
        $list = $tag->find();

        $assign = new Parse\Assign($data);
        foreach($list as $key => $value){
            $assign->find($value);
        }
        $data = $assign->data();
        var_dump($data);
        die;
    }


}