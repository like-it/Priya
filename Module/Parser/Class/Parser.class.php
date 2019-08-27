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
use Priya\Module\Parser\Tag;
use Priya\Module\Parser\Assign;
use Priya\Module\Parser\Method;
use Priya\Module\Parser\Control_If;
use Priya\Module\Parser\Token;

class Parser extends ParserCore {
    const DIR = __DIR__;
    const LITERAL = 'literal';
    const FUNCTION_LIST = 'Function.List.php';

    public $has_list = false;

    /**
     *
     * @param $handler
     * @param $route
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

    public function read($url=''){
        $file = new File();
        $ext = $file->extension($url);
        if($ext == '' || $ext == Autoload::EXT_JSON){
            $read = parent::read($url);
            if(!empty($read)){
                $read = $this->data(Parser::object_merge($this->data(), ($this->compile($this->data(), $this->data(), false, true))));
            }
        } else {
            $read = $file->read($url);
            $read = $this->compile($read, $this->data(), false, true);
        }
        return $read;
    }

    public function compile($string, $data=null, $keep=false, $root=true, $debug=false){
        if($this->data('priya.parser.halt')){
            return '';
        }
        if(is_array($string)){
            foreach($string as $nr => $line){
                $string[$nr] = $this->compile($line, $data, $keep, false, $debug);
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
            if(
                !is_string($string) ||
                is_numeric($string)
            ){
                return $string;
            }
            $random = $this->random();
            if(empty($random)){
                $random = Parser::random_create();
                while(stristr($string, $random) !== false){
                    $random = Parser::random_create();
                }
                $this->random($random);
            }
            $string = Token::comment_remove($string);
            $string = Token::literal_extra($string);
            $string = Token::newline_replace($string, $this->random());
            $string = Token::literal_replace($string, $this->random());
            $list = Tag::find($string);
            if($data !== null){
                $this->data($data);
            }
            $record = array();
            $record['string'] = $string;
            $record = $this->list($list, $record, $keep, $root);
            return $record['string'];
        }
    }

    public function list($list=array(), $record, $keep, $root){
        $record['original'] = $record['string'];
        //rename to priya.parser.halt
        if($this->data('priya.parser.halt')){
            $record['string'] = '';
            return $record;
        }
        foreach($list as $nr => $value){
            $key = key($value);
            if(!is_string($key)){
                var_dump($list);
                die;
            }
            if(strtolower(substr($key, 0, 3)) == '{if'){
                $record['if']['tag'] = $key;
                $record = Control_If::create($list, $record, $this->random());
                $record = Control_If::statement($record, $this);
                $list = Tag::find($record['string']);
                $next = [];
                $next['string'] = $record['string'];
                return $this->list($list, $next, $keep, $root);
            }
            if(empty($record['status'])){
                $record['assign']['tag'] = $key;
                $record = Assign::find($record, $this);
//                 var_dump($record);
//                 die;
                $record = Assign::row($record, $this->random());
                if(!empty($record['status'])){
                    $list = Assign::list($list, $record);
                    $next = [];
                    $next['string'] = $record['string'];
                    return $this->list($list, $next, $keep, $root);
                }
            }
            if(empty($record['status'])){
                $record['variable']['tag'] = $key;
                $record = Variable::find($record, $keep, $this);
                if(!empty($record['status'])){
                    $list = Variable::list($list, $record);
                    $next = [];
                    $next['string'] = $record['string'];                    
                    return $this->list($list, $next, $keep, $root);
                }
            }
            if(empty($record['status'])){
                $record['method']['tag'] = $key;
                $record = method::find($record, $this);
                if(!empty($record['status'])){
                    $list = Method::list($list, $record, $this);
                    $next = [];
                    $next['string'] = $record['string'];
                    return $this->list($list, $next, $keep, $root);
                }
            }
            unset($record['status']);
        }
        if(empty($list)){
            $key = $record['string'];
            if(empty($record['status'])){
                $record['assign']['tag'] = $key;                
                Assign::find($record, $this);
                $record = Assign::row($record, $this->random());
            }
            if(empty($record['status'])){
                $record['variable']['tag'] = $key;
                $record = Variable::find($record, $keep, $this);
            }
            if(empty($record['status'])){
                $record['method']['tag'] = $key;
                $record = Method::find($record, $this);
            }
        }
        $record['string'] = Token::newline_restore($record['string'], $this->random());
        if(is_string($record['string'])){
            $record['string'] = Token::literal_restore($record['string'], $this->random());
            //only root should remove
            if($root){
                $record['string'] = Token::literal_remove($record['string']);
            }
        }
        return $record;
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