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
        $string = Parse\Newline::replace($string, $this->random());

        $tag = new Parse\Tag($string, $this->random());
        $list = $tag->find();

        $assign = new Parse\Assign($data, $this->random());
        $if = new Parse\Control_if($data, $this->random());

        $if_counter = 0;

        $record = array();
        $record['string'] = $string;

        while($if::has($list)){
            if($if_counter >= $if::MAX){
                break;
            }
            $record = $if::create($list, $string);
            $list = $tag->find($record['string']);
        }
        $string = $record['string'];

        foreach($list as $key => $value){
            $assign->find($value);
            $this->data($assign->data());
        }
    }
}