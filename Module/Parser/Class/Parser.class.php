<?php
/**
 * @author 		Remco van der Velde
 * @since 		2016-10-19
 * @version		1.0
 * @changeLog
 * 	-	all
 */

namespace Priya\Module;

use Priya\Module\Parser\Core;
use Priya\Module\Parser\Variable;

class Parser extends Core {
    const DIR = __DIR__;
    const LITERAL = 'literal';
    const FUNCTION_LIST = 'Function.List.php';

    public function __construct($handler=null, $route=null, $data=null){
        if($data !== null){
            $this->handler($handler);
            $this->route($route);
            $this->data(Parser::object_merge($this->data(), $data));
        } else {
            $this->data(Parser::object_merge($this->data(), $handler));
        }
        $this->data($this->compile($this->data()));
    }

    public function read($url=''){
        $read = parent::read($url);
        if(!empty($read)){
            return $this->data($this->compile($this->data(), $this->data()));
        }
        return $read;
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
                $random = Parser\Random::create();
                while(stristr($string, $random) !== false){
                    $random = Parser\Random::create();
                }
                $this->random($random);
            }
            $string = Parser\Newline::replace($string, $this->random());

            $tag = new Parser\Tag($string, $this->random());
            $list = $tag->find();

            $data = $this->data();
            $assign = new Parser\Assign($data, $this->random(), $this);
            $if = new Parser\Control_If($data, $this->random(), $this);
            $variable = new Parser\Variable($data, $this->random(), $this);
            $method = new Parser\Method($data, $this->random());
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
                    $record = Parser\Assign::row($record, $this->random());

                    $variable->data($assign->data());
                    $record['variable']['tag'] = $key;
                    $record = $variable->find($record);

                    $method->data($variable->data());
                    $record['method']['tag'] = $key;
                    $record = $method->find($record, $variable, $this);

                    $assign->data($method->data());
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
                $record = Parser\Assign::row($record, $this->random());

                $variable->data($assign->data());
                $record['variable']['tag'] = $key;
//                 debug($record);
                $record = $variable->find($record);

//                 debug($record['variable']['tag']);

                $method->data($variable->data());
                $record['method']['tag'] = $key;
                $record = $method->find($record, $variable, $this);

                $assign->data($method->data());
            }
            $if->data($method->data());
            $this->data($if->data());

            $string = $record['string'];

            if(is_string($string)){
                $string = Parser\Token::restore_return($string, $this->random());
                $string = Parser\Literal::restore($string, $this->random());
                $string = Parser\Literal::remove($string);
                $string = Parser\Token::remove_comment($string);
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