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
        $tag_open_nr = null;
        $tag_close_nr = null;
        $depth = 0;
        $keep = false;
        $count = 0;
//         var_dump($token);
        foreach($token as $nr => $record){
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
                /*
                if(isset($create[1])){
                    var_dump($create);
                    $source = $this->data('priya.parse.read.url');
                    if($source !== null){
                        throw new Exception('Multiple expressions found in single expression mode on line: ' . $create[1]['row'] . ' column: ' . $create[1]['column'] . ' in: ' . $source);
                    } else {
                        throw new Exception('Multiple expressions found in single expression mode on line: ' . $create[1]['row'] . ' column: ' . $create[1]['column']);
                    }
                }
                */
                if($count >= 10){
                    var_dump('ww');
                    die;
                }
                if($is_debug === true && $count > 2){
                    if(empty($create)){
                        var_dump($token);
                        var_dump($tag_open_nr);
                        var_dump($tag_close_nr);
//                         var_dump(debug_backtrace(true));
                        die;
                    }
                    var_Dump($count);
                    var_dump($create);
                    var_dump($tag_open_nr);
                    var_dump($tag_close_nr);
                    var_dump($token);
//                     var_dump($create[0]['method']['parameter'][0]);
                    die;
                }


//                 var_dump($create);
                $create = array_shift($create);
                switch($create['type']){
                    case 'variable' :
                        $create = Variable::execute($this, $create, $token, $keep);
                        break;
                    case 'method' :
                        //should return token
                        $token = Method::execute($this, $create, $token, $keep, true, $count);
                        $token = $this->execute($token);
                        $create = null;
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
                    $token[$tag_open_nr] = $create;
                    for($i = $tag_open_nr + 1; $i <= $tag_close_nr; $i++){
                        unset($token[$i]);
                    }
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
        if($this->data('priya.parser.halt')){
            return '';
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
            $tag_open_nr = null;
            $tag_close_nr = null;

            if($this->data('priya.parse.is.code') === true){
                var_dump($token);
                die;
            }
            $depth = 0;

//             $token = $this->execute($token);

            foreach($token as $nr => $record){
//                 var_dump($record);

                /*
                if($record['type'] == Token::TYPE_REM){
                    $token[$nr] = Code::find($this, $record);
                    var_dump($token[$nr]);
                    die;
                }
                */

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
                    $token[$nr]['value'] == '}'){
                    if(
                        $depth == 1 &&
                        $tag_open_nr !== null
                    ){
                        $tag_close_nr = $nr;
                    }
                    $depth--;
                }


/*
                if($record['value'] == '{'){
                    $depth++;
                    if($depth == 1){
                        $tag_open_nr = $nr;
                    }
                }
                elseif($record['value'] == '}'){
                    if($depth == 1){
                        $tag_close_nr = $nr;
                    }
                    $depth--;
                }
*/
                if(
                    $tag_open_nr !== null &&
                    $tag_close_nr !== null
                ){
                    $create = Token::create($token, $tag_open_nr, $tag_close_nr);
                    if(isset($create[1])){
                        var_dump($create);
                        $source = $this->data('priya.parse.read.url');
                        if($source !== null){
                            throw new Exception('Multiple expressions found in single expression mode on line: ' . $create[1]['row'] . ' column: ' . $create[1]['column'] . ' in: ' . $source);
                        } else {
                            throw new Exception('Multiple expressions found in single expression mode on line: ' . $create[1]['row'] . ' column: ' . $create[1]['column']);
                        }
                    }
                    if(!isset($create[0])){
                        var_dump($create);
                        var_dump($tag_open_nr);
                        var_dump($tag_close_nr);
                        var_dump($token);
                        die;
                    }
                    $create = $create[0];
                    switch($create['type']){
                        case 'variable' :
                            $create = Variable::execute($this, $create, $token, $keep);
                        break;
                        case 'method' :
                            //should return token
                            //see readline function
                            $token = Method::execute($this, $create, $token, $keep, true);
                            $create = null;
                        break;
                        default:
                            break;
                    }
                    if($create !== null){
                        $token[$tag_open_nr] = $create;
                        for($i = $tag_open_nr + 1; $i <= $tag_close_nr; $i++){
                            unset($token[$i]);
                        }
                    }
                    $tag_open_nr = null;
                    $tag_close_nr = null;
                }
            }
            $string = Token::string($token);
            if($root === true){
                $tag = Token::tag_find($string);
                $string = Token::literal_remove($string, $tag);
            }
            return $string;
        }
    }
}