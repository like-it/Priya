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
use Priya\Module\Parse\Variable;

class Parse extends Core {
    const DIR = __DIR__;
    const LITERAL = 'literal';
    const FUNCTION_LIST = 'Function.List.php';

    public function __construct($handler=null, $route=null, $data=null){
        if($data !== null){
            $this->handler($handler);
            $this->route($route);
            $this->data(Parse::object_merge($this->data(), $data));
        } else {
            $this->data(Parse::object_merge($this->data(), $handler));
        }
    }

    public function compile($string, $keep=false){
        if(
            is_null($string) ||
            is_bool($string) ||
            is_float($string) ||
            is_int($string) ||
            is_numeric($string)
        ){
            return $string;
        }
        if (is_array($string)){
            foreach($string as $nr => $line){
                $string[$nr] = $this->compile($line, $keep);
            }
            return $string;
        }
        elseif(is_object($string)){
            foreach ($string as $key => $value){
                $string->{$key} = $this->compile($value, $keep);
            }
            return $string;
        } else {
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

            $data = $this->data();
            $assign = new Parse\Assign($data, $this->random(), $this);
            $if = new Parse\Control_If($data, $this->random(), $this);
            $variable = new Parse\Variable($data, $this->random());
            $method = new Parse\Method($data, $this->random());
            $if_counter = 0;

            $record = array();
            $record['string'] = $string;

//             debug($list, 'list');

            while($if::has($list)){
                foreach($list as $value){
                    $key = key($value);
                    if(substr($key, 0, 3) == '{if'){
                        break;
                    }
                    $assign->find($key);
                    $record['assign']['tag'] = $key;
                    $record = Parse\Assign::row($record, $this->random());
                    $variable->data($assign->data());
                    $record['variable']['tag'] = $key;
                    $record = $variable->find($record);
                    //add method
                }
                $if->data($assign->data());
                $record = $if::create($list, $record['string'], $this->random());
                $record = $if->statement($record, $this);
                $list = $tag->find($record['string']);
                if($if_counter >= $if::MAX){
                    debug('max reached in if::has');
                    break;
                }
                $if_counter++;
            }
            $assign->data($if->data());

            foreach($list as $value){
                $key = key($value);

                $assign->find($key);
                $record['assign']['tag'] = $key;
                $record = Parse\Assign::row($record, $this->random());

                $variable->data($assign->data());
                $record['variable']['tag'] = $key;
                //need parent
                $record = $variable->find($record);
                $method->data($variable->data());
                $record['method']['tag'] = $key;
                $record = $method->find($record, $variable, $this);
            }
            $if->data($method->data());
            $this->data($if->data());

            if(!isset($record['string'])){
                debug($record, 'no string');
                die;
            }

            $string = $record['string'];

            if(is_string($string)){
                $string = Parse\Token::restore_return($string, $this->random());
                $string = Parse\Literal::restore($string, $this->random());
                $string = Parse\Literal::remove($string);
                return $string;
            } else {
                /**
                 * do we need to parse the object ?
                 * - variables
                 * - methods
                 * - literal
                 */
                return $string;
            }
        }
    }
}