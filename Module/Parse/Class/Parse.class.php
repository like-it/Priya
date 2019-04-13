<?php
/**
 * @author         Remco van der Velde
 * @since         2016-10-19
 * @version        1.0
 * @changeLog
 *     -    all
 */

namespace Priya\Module;

use Priya\Module\Parse\Core as Base;
use Priya\Module\Parse\Token;
use Priya\Module\Parse\Variable;
use Priya\Module\Parse\Method;
use Priya\Module\Parse\Code;
use Exception;

class Parse extends Base {
    const DIR = __DIR__;

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
            $this->data(Parse::object_merge($this->data(), $data));
        } else {
            if(!$handler instanceof Handler){
                $this->data(Parse::object_merge($this->data(), $handler));
            } else {
                $this->handler($handler);
                $this->route($route);
            }
        }
        $parser = new Parser();
        $parser->data($this->data());
        $read = $parser->read(__CLASS__);
        $this->data(Parse::object_merge($this->data(), $read));
//         $this->data($this->compile($this->data(), $this->data()));
    }

    public function read($url=''){
        $extension = File::extension($url);
        if($extension == '' || $extension == Autoload::EXT_JSON){
            $data = new Data();
            $read = $data->read($url);
            if(!empty($read)){
                try {
                    $this->data('priya.parse.read.url', $url);
                    $read = $data->data($this->compile($data->data()));
                    $this->data('delete', 'priya.parse.read.url');
                } catch (Exception $e) {
                    return $e;
                }
            }
        } else {
            $read = File::read($url);
            if($read === false){
                throw new Exception('File (' . $url .') not exists');
            }
            try {
                $this->data('priya.parse.read.url', $url);
                $read = $this->compile($read);
                $this->data('delete', 'priya.parse.read.url');
            } catch (Exception $e) {
                return $e;
            }
        }
        return $read;
    }

    public function execute($token=[], $is_debug=false){
        if($this->data('priya.parse.halt')){
            $break = [];
            foreach($token as $nr => $record){
                if(
                    null !== $this->data('priya.parse.break.nr') &&
                    $nr > $this->data('priya.parse.break.nr')
                ){
                    break;
                }
                $break[$nr] = $record;
            }
            return $break;
        }
        $tag_open_nr = null;
        $tag_close_nr = null;
        $depth = 0;
        $keep = false;
        $count = 0;
        foreach($token as $nr => $record){
            if($this->data('priya.parse.halt')){
                $break = [];
                foreach($token as $nr => $record){
                    if(
                        null !== $this->data('priya.parse.break.nr') &&
                        $nr > $this->data('priya.parse.break.nr')
                    ){
                            break;
                    }
                    $break[$nr] = $record;
                }
                return $break;
            }
            if(
                isset($token[$nr]['is_parsed']) ||
                isset($token[$nr]['in_execution'])
            ){
                if(
                    $tag_open_nr !== null &&
                    $tag_close_nr !== null
                ){
                    for($i = $tag_open_nr; $i <= $tag_close_nr; $i++){
                        if($i == $nr){
                            continue;
                        } else {
                            unset($token[$i]);
                        }
                    }
                }
                continue;
            }
            if(
                isset($token[$nr]) &&
                $token[$nr]['value'] == '{'
            ){
                $depth++;
                if(
                    $depth == 1 &&
                    $tag_open_nr === null
                ){
                    $tag_open_nr = $nr;
                }
            }
            elseif(
                isset($token[$nr]) &&
                $token[$nr]['value'] == '}'
            ){
                if(
                    $depth == 1 &&
                    $tag_open_nr !== null
                ){
                    $tag_close_nr = $nr;
                }
                $depth--;
            }
            if(
                $tag_open_nr !== null &&
                $tag_close_nr !== null
            ){
                $count++;
                $create = Token::create($token, $tag_open_nr, $tag_close_nr);

                if(isset($create[1])){
                    var_dump($create[0]['method']['parameter']);
                    die;
                    var_dump($create);
                    $source = $this->data('priya.parse.read.url');
                    if($source !== null){
                        throw new Exception('Multiple expressions found in single expression mode on line: ' . $create[1]['row'] . ' column: ' . $create[1]['column'] . ' in: ' . $source);
                    } else {
                        throw new Exception('Multiple expressions found in single expression mode on line: ' . $create[1]['row'] . ' column: ' . $create[1]['column']);
                    }
                }
                $create = array_shift($create);
                switch($create['type']){
                    case Token::TYPE_VARIABLE :
                        $token = Variable::execute($this, $create, $token, $keep);
                        $token[$create['token']['nr']]['is_parsed'] = true;
                        $token = $this->execute($token);
                        $create = null;
                    break;
                    case Token::TYPE_METHOD :
                        //should return token
                        $token = Method::execute($this, $create, $token, $keep, true, $count);
                        $token[$create['token']['nr']]['is_parsed'] = true;
                        $token = $this->execute($token);
                        if($create['method']['name'] == 'break'){
//                             var_Dump($token);
//                             die;
                        }
                        $create = null;
                    break;
                    case Token::TYPE_TAG_CLOSE :
                        $create['execute'] = null;
                        $create['is_executed'] = true;
                        $create['is_parsed'] = true;
                    break;
                    default:
                        if(!isset($create['is_executed'])){
                            var_dump($create);
                            die;
                            $create['execute'] = $create['value'];
                            $create['is_executed'] = true;
                        }
                        break;
                }

                if($create !== null){
                    var_dump($create);
                    die;
                }
                $tag_open_nr = null;
                $tag_close_nr = null;
            }
        }
        return $token;
    }

    public function compile($string, $data=null, $keep=false, $root=true){
        if($data !== null){
            $this->data($data);
            $data = null;
        }
        if(is_array($string)){
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
            if(
                !is_string($string) ||
                is_numeric($string)
            ){
                return $string;
            }
            $token = Token::all($string);
            $token = Token::tag_activate($token, Token::TYPE_LITERAL, true, true);
            $token = Token::tag_activate($token, Token::TYPE_REM);
            $token = Token::comment_remove($token);


            if($this->data('priya.parse.is.code') === true){
                var_dump($token);
                die;
            }
            $token = $this->execute($token, true);
            $string = Token::string($token);
            if($root === true){
                $tag = Token::tag_find($string);
                $string = Token::literal_remove($string, $tag);
            }
            return $string;
        }
    }
}