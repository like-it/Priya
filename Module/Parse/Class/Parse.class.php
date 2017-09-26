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
        $if = new Parse\Control_If($data, $this->random());

        $if_counter = 0;

        $record = array();
        $record['string'] = $string;

        while($if::has($list)){
            $record = $if::create($list, $record['string'], $this->random());
            $record = $if->statement($record);
            $record['execute'] = Parse\Token::restore_return($record['execute'], $this->random());

            $list = $tag->find($record['string']);
            if($if_counter >= $if::MAX){
                debug('max reached in if::has');
                break;
            }
            $if_counter++;
        }
        $string = $record['string'];

        $string= Parse\Token::restore_return($string, $this->random());

        debug($string, 'string');

        foreach($list as $key => $value){
            $assign->find($value);
            $this->data($assign->data());
        }
        die('end parser tests');
    }
}