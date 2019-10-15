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
use Priya\Module\Parse\Compile;
use Priya\Module\Parse\Variable;
use Priya\Module\Parse\Method;
use Priya\Module\Parse\Code;
use Priya\Module\File\Extension;
use Exception;
use Priya\Module\Core\Result;

class Parse extends Base {
    const DIR = __DIR__;

    const RANDOM_INT_VERSION = '7.0';

    const DATA_PREFIX = 'priya.parse.';
    const DATA_TOKEN = Parse::DATA_PREFIX . 'token';
    const DATA_READ_URL = Parse::DATA_PREFIX . 'read.url';
    const DATA_FUNCTION =  Parse::DATA_PREFIX . 'function';
    const DATA_MODIFIER =  Parse::DATA_PREFIX . 'modifier';    
    const DATA_DIR_FUNCTION =  Parse::DATA_PREFIX . 'dir.function';
    const DATA_DIR_MODIFIER =  Parse::DATA_PREFIX . 'dir.modifier';
    const DATA_DIR_CLASS =  Parse::DATA_PREFIX . 'dir.class';
    const DATA_DIR_TRAIT =  Parse::DATA_PREFIX . 'dir.trait';
    const DATA_DIR_CACHE =  Parse::DATA_PREFIX . 'dir.cache';
    const DATA_COMPILE_EXECUTE_HOLD = Parse::DATA_PREFIX . 'compile.execute.hold';

    //should go...
    const TYPE_TRANSLATION = 'translation';
    const TYPE_FUNCTION = Token::TYPE_FUNCTION;
    const TYPE_MODIFIER = Token::TYPE_MODIFIER;    
    const TYPE_CLASS = Token::TYPE_CLASS;
    const TYPE_TRAIT = Token::TYPE_TRAIT;
    const TYPE_LITERAL = 'literal';
    const TYPE_REM = 'rem';

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
        $parse = new Parser();
        $parse->data($this->data());
        $read = $parse->read(__CLASS__);
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
        $count_execute = 0;
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
                $token[$nr]['value'] == '{' &&
                $token[$nr]['type'] != Token::TYPE_TAG_LITERAL            
            ){
                $depth++;
                if(
                    $depth == 1 &&
                    $tag_open_nr === null
                ){
                    $tag_open_nr = $nr;
                }                
                if($depth==2){
                    
                    // var_dump($token);
                    // die;
                }
            }
            elseif(
                isset($token[$nr]) &&
                $token[$nr]['value'] == '}' &&
                $token[$nr]['type'] != Token::TYPE_TAG_LITERAL
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
                $count_execute++;
                // var_dump($token);                
                $create = Token::create($token, $tag_open_nr, $tag_close_nr);    
                $count = count($create);
                if($count == 0){
                    $source = $this->data('priya.parse.read.url');
                    if($source !== null){
                        throw new Exception('No expression found on line: ' . $create[1]['row'] . ' column: ' . $create[1]['column'] . ' in: ' . $source);
                    } else {
                        throw new Exception('No expression found on line: ' . $create[1]['row'] . ' column: ' . $create[1]['column']);
                    }
                }
                elseif($count > 1){  
                    var_Dump($create);
                    var_Dump($create[91]['variable']['value']);
                    var_dump($count);
                    die;                  
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
                        $token = Method::execute($this, $create, $token, $keep, true, $count_execute);
                        $token[$create['token']['nr']]['is_parsed'] = true;
                        $token = $this->execute($token);                                                
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
                $tag_open_nr = null;
                $tag_close_nr = null;
            }
        }        
        return $token;
    }
    
    public function create($token=[]){
        $tag_open_nr = null;
        $tag_close_nr = null;
        $depth = 0;        
        foreach($token as $nr => $record){       
            if(
                isset($token[$nr]) &&
                $token[$nr]['value'] == '{' &&
                $token[$nr]['type'] != Token::TYPE_LITERAL
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
                $token[$nr]['value'] == '}' &&
                $token[$nr]['type'] != Token::TYPE_LITERAL
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
                $create = Token::create($token, $tag_open_nr, $tag_close_nr); 
                $count = count($create);                            
                if($count == 1){
                    $create = reset($create);
                    //maybe not needed...
                    if($create['type'] == Token::TYPE_TAG_CLOSE){
                        $method_name = substr($create['tag']['name'], 1);     
                        $depth = 1;               
                        for($i = $create['token']['nr'] -1; $i >= 0; $i--){
                            if(!isset($token[$i])){
                                continue;
                            }
                            elseif(
                                $token[$i]['type'] == Token::TYPE_TAG_CLOSE &&
                                $token[$i]['tag']['name'] == $create['tag']['name']
                            ){
                                $depth++;
                            }                        
                            elseif(
                                $token[$i]['type'] == Token::TYPE_METHOD &&
                                $token[$i]['method']['name'] == $method_name
                            ){
                                if($depth == 1){
                                    $token[$i]['token']['tag_close_nr'] = $create['token']['nr'];
                                    break;                                    
                                }
                                $depth--;
                            }
                        }                        
                        $depth = 0;                        
                    }
                    for($i = $tag_open_nr; $i <= $tag_close_nr; $i++){
                        if($i == $create['token']['nr']){
                            $token[$create['token']['nr']] = $create;
                        } else {
                            unset($token[$i]);
                        }
                    }                    
                }
                elseif($count == 0){
                    //can be literal
                    /*
                    $source = $this->data('priya.parse.read.url');
                    if($source !== null){
                        throw new Exception('No expression found on line: ' . $token[$tag_open_nr]['row'] . ' column: ' . $token[$tag_open_nr]['column'] . ' in: ' . $source);
                    } else {
                        throw new Exception('No expression found on line: ' . $token[$tag_open_nr]['row'] . ' column: ' .$token[$tag_open_nr]['column']);
                    }
                    */
                } else {                    
                    $source = $this->data('priya.parse.read.url');
                    if($source !== null){
                        var_dump($create);
                        die;
                        throw new Exception('Multiple expressions found in single expression mode on line: ' . $token[$tag_open_nr]['row'] . ' column: ' . $token[$tag_open_nr]['column'] . ' in: ' . $source);
                    } else {
                        throw new Exception('Multiple expressions found in single expression mode on line: ' .$token[$tag_open_nr]['row'] . ' column: ' . $token[$tag_open_nr]['column']);
                    }
                }
                $tag_open_nr = null;
                $tag_close_nr = null;        
            }
        }
        return $token;        
    }

    public static function modifier($name, $parameter=[]){
        $filename = ucfirst(Token::TYPE_MODIFIER) . '.' . ucfirst($name);
        $function_name = strtolower(str_replace('.', '_', $filename));
        var_dump($function_name);
        var_Dump($parameter);
        die;
    }

    public static function require(Parse $parse, $name='', $type=null){
        // return true;
        $exist = false;
        switch($type){
            case Parse::TYPE_FUNCTION :
                $dir = $parse->data('priya.parse.dir.function');
                foreach($dir as $nr => $directory){
                    $url = $directory . $name . Extension::PHP;
                    if(File::exist($url)){
                        require_once($url);
                        $exist = true;
                        break;
                    }
                    //need function exist
                }
            break;
            case Parse::TYPE_MODIFIER :            
                $dir = $parse->data('priya.parse.dir.modifier');
                foreach($dir as $nr => $directory){
                    $url = $directory . $name . Extension::PHP;                    
                    if(File::exist($url)){
                        require_once($url);
                        $function_name = strtolower(str_replace('.', '_', $name));                        
                        if(function_exists($function_name) === false){
                            throw new Exception('Function: ' . $function_name . ' does not exist...');                            
                        }
                        $exist = true;
                        break;
                    }
                    //need modifier exist
                }
            break;
        }
        if($exist === false){
            throw new Exception('File not found: ' . $url);
        }        
    }

    public function tokenize($string, $data=null, $keep=false, $root=true){
        if($data !== null){
            $this->data($data);
            $data = null;
        }
        if(is_array($string)){
            foreach($string as $nr => $line){
                $string[$nr] = $this->tokenize($line, $data, $keep, false);
            }
            return $string;
        }
        elseif(is_object($string)){
            foreach ($string as $key => $value){
                //add key compile
                $string->{$key} = $this->tokenize($value, $data, $keep, false);
            }
            return $string;
        } else {
            /*
            if(
                !is_string($string) ||
                is_numeric($string)
            ){
                return $string;
            }
            */
            $token = Token::all($string);        
            $token = Token::tag_activate($token, Parse::TYPE_LITERAL, true, true);
            $token = Token::tag_activate($token, Parse::TYPE_REM);            
            $token = Token::comment_remove($token);                        
            $token = $this->create($token);
        }
        return $token;
    }

    public function compile($string, $data=null, $keep=false, $root=true){
        //activate middleware before
        // or hook before
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
            $token = Token::tag_activate($token, Parse::TYPE_LITERAL, true, true);
            $token = Token::tag_activate($token, Parse::TYPE_REM);            
            $token = Token::comment_remove($token);                                 
            $token = $this->create($token);
            $this->data(Parse::DATA_TOKEN, $token);  

            if($this->data(Parse::DATA_COMPILE_EXECUTE_HOLD) === true){

            }
            $url = Compile::execute($this, $token, $class);
            if($url === false){
                throw new Exception('Parse error: Could not execute compile, file not created...');
                die;
            }
            if(
                $this->data('phpc.namespace') !== null && 
                $this->data('phpc.class') !== null
            ){                
                require_once $url;

                // var_Dump(get_included_files());                

                // var_Dump($this->data('phpc.class'));

                $compile = $this->data('phpc.namespace') . '\\' . $this->data('phpc.class');
                
                  
                $duration = microtime(true) - $this->data('time.start');
                
                // die;

                ob_start();                
                $compile::execute($this);     
                
                $string = ob_get_contents();       

                var_dump($duration);
                var_dump($string);
                die;

                
                if($root === true){
                    $tag = Token::tag_find($string);
                    $string = Token::literal_remove($string, $tag);
                }                
                // ob_end_clean();                                             
                //activate middleware after
                return $string;
                
            } else {
                throw new Exception('PHPC: could not find namespace or generated classname...');
                die;
            }            
        }
    }

    public static function plus($left=null, $right=null){
        if(
            is_string($left) ||
            is_string($right)
        ){
            return $left . $right;
        } else {
            return $left + $right;
        }    
    }

}