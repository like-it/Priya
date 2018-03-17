<?php
/**
 * @author         Remco van der Velde
 * @since         2016-10-19
 * @version        1.0
 * @changeLog
 *     -    all
 */

namespace Priya\Module;

use Priya\Module\Parser\Core as ParserCore;
use Priya\Module\Parser\Variable;
use Priya\Module\Parser\Newline;
use Priya\Module\Parser\Literal;
use Priya\Module\Parser\Tag;
use Priya\Module\Parser\Random;
use Priya\Module\Parser\Assign;
use Priya\Module\Parser\Method;
use Priya\Module\Parser\Control_If;
use Priya\Module\Parser\Token;
use Exception;

class Parser extends ParserCore {
    const DIR = __DIR__;
    const LITERAL = 'literal';
    const FUNCTION_LIST = 'Function.List.php';

    public $has_list = false;

    /**
     *
     * @param Priya\Module\Handler $handler
     * @param Priya\Module\Route $route
     * @param array/object $data
     */
    public function __construct($handler=null, $route=null, $data=null){
        if($data !== null){
            $this->handler($handler);
            $this->route($route);
            $this->data(Parser::object_merge($this->data(), $data));
        } else {
            if(!$handler instanceof \Priya\Module\Handler){
                $this->data(Parser::object_merge($this->data(), $handler));
            } else {
                $this->handler($handler);
                $this->route($route);
            }
        }
        $this->data($this->compile($this->data(), $this->data()));
    }

    /**
     * can only read .json files...
     * {@inheritDoc}
     * @see \Priya\Module\Data::read()
     */
    public function read($url=''){
        $file = new File();
        $ext = $file->extension($url);
        if($ext == '' || $ext == Autoload::EXT_JSON){
            $read = parent::read($url);
            if(!empty($read)){
                $read = $this->data($this->compile($this->data(), $this->data(), false, false));
            }
            //might need to add comment...
            $read = $this->data(Literal::remove($this->data()));
        } else {
            $read = $file->read($url);
            $read = $this->compile($read, $this->data(), false, false);
            $read = Token::remove_comment($read);
//             debug($read, __LINE__ . '::' . __FILE__);
        }
        return $read;
    }

    public function compile($string, $data=null, $keep=false, $root=true){
//         $this->input($string);
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
                $string[$nr] = $this->compile($line, $data, $keep, false);
            }
            return $string;
        }
        elseif(is_object($string)){
            foreach ($string as $key => $value){
                //add key compile
                $string->{$key} = $this->compile($value, $data, $keep, false);
            }
            return $string;
        } else {
            $random = $this->random();
            if(empty($random)){
                $random = Random::create();
                while(stristr($string, $random) !== false){
                    $random = Random::create();
                }
                $this->random($random);
            }
            $string = Literal::extra($string);
            $string = Newline::replace($string, $this->random());
            $string = Literal::replace($string, $this->random());

            $list = Tag::find($string);

            /**
             * invalid for capture.append
             *
             * dont know for for.each (it removes the content)
             */
//             $list = Tag::control($list);

            if($data !== null){
                $this->data($data);
            }
            $if_counter = 0;

            $record = array();
            $record['string'] = $string;

            while(Control_If::has($list)){
                foreach($list as $value){
                    $key = key($value);
                    if(substr($key, 0, 3) == '{if'){
                        break;
                    }
                    Assign::find($key, $this);
                    $record['assign']['tag'] = $key;
                    $record = Assign::row($record, $this->random());

                    $record['variable']['tag'] = $key;
                    $record = Variable::find($record, $keep, $this);

                    $record['method']['tag'] = $key;
                    $record = Method::find($record, $this);
                }
                $record = Control_If::create($list, $record['string'], $this->random());
                $record = Control_If::statement($record, $this);
                $list = Tag::find($record['string']);
                if($if_counter >= Control_If::MAX){
                    throw new Exception('Parser::compile:$if_counter>=Control_If::MAX');
                    break;
                }
                $if_counter++;
            }
            foreach($list as $value){
                $key = key($value);
                Assign::find($key, $this);
                $record['assign']['tag'] = $key;
                $record = Assign::row($record, $this->random());

                $record['variable']['tag'] = $key;
                $record = Variable::find($record, $keep, $this);

                $record['method']['tag'] = $key;
                $record = method::find($record, $this);
            }
            if(empty($list)){
                $key = $record['string'];
                Assign::find($key, $this);
                $record['assign']['tag'] = $key;
                $record = Assign::row($record, $this->random());

                $record['variable']['tag'] = $key;
                $record = Variable::find($record, $keep, $this);

                $record['method']['tag'] = $key;
                $record = method::find($record, $this);
            }
            $string = $record['string'];
            $string = Token::restore_return($string, $this->random());
            if(is_string($string)){
                $string = Literal::restore($string, $this->random());
                $string = str_replace(
                    array('[literal][rand:' .  $this->random() .']{literal}', '[/literal][rand:' .  $this->random() .']{/literal}'),
                    array('{literal}', '{/literal}'),
                    $string
                );
                //only root should remove
                if($root){
                    $string = Literal::remove($string);
                    $string = Token::remove_comment($string);
                }
                return $string;
            } else {
                return $string;
            }
        }
    }

    public function recursive_compile($list='', $children='nodeList'){
        if(is_object($list)){
            foreach ($list as $jid => $node){
                if(isset($node->{$children})){
                    $node->{$children} = $this->recursive_compile($node->{$children}, $children);
                }
                $node = $this->compile($node, $node);
            }
        }
        return $list;
    }
}