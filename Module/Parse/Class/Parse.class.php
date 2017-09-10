<?php
/**
 * @author 		Remco van der Velde
 * @since 		2016-10-19
 * @version		1.0
 * @changeLog
 * 	-	all
 */

namespace Priya\Module;

use Priya\Module\Parse\Core;

class Parse extends Core {
    const DIR = __DIR__;
    const LITERAL = 'literal';
    const PHP_MIN_VERSION = '7.0.0';
    const FUNCTION_LIST = 'Function.List.php';

    public function __construct($handler=null, $route=null, $data=null){
        if($data !== null){
            $this->handler($handler);
            $this->route($route);
            $this->data($this->object_merge($this->data(), $data));
        } else {
            $this->data($this->object_merge($this->data(), $handler));
        }
    }

    public function compile($string, $data, $keep=false){
        $random = $this->random();
        if(empty($random)){
            $random = Parse\Random::create();
            while(stristr($string, $random) !== false){
                $random = Parse\Random::create();
            }
            $this->random($random);
        }
        $newline = new Parse\Newline($string, $this->random());
        $string = $newline->replace();

        $tag = new Parse\Tag($string);
        $list = $tag->find();

        $assign = new Parse\Assign($data, $this->random());
        foreach($list as $key => $value){
            $assign->find($value);
        }
        $data = $assign->data();
        var_Dump($data);
    }


}