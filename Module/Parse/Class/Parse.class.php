<?php
/**
 * @author                Remco van der Velde
 * @since                 2018-03-19
 * @version               2.0
 * @changeLog
 *     -    detoken parse
 * @todo
 *     -    if function
 *     -    foreach function
 *     -    while function
 *     -    switch function
 *     -    sets, from inside to outside
 *     -    operators
 *     -     modifier
 *     -    literal
 */

namespace Priya\Module;

use Exception;
use Priya\Module\Parse\Tag;
use Priya\Module\Parse\Assign;
use Priya\Module\Parse\Variable;
use Priya\Module\Parse\Method;
use Priya\Module\Parse\Priya;
use Priya\Module\Parse\Literal;
use Priya\Module\Parse\Operator;

class Parse extends Data {
    const TYPE_INTEGER = 'integer';
    const TYPE_FLOAT = 'double';
    const TYPE_BOOLEAN= 'boolean';
    const TYPE_STRING = 'string';
    const TYPE_ARRAY = 'array';
    const TYPE_OBJECT = 'object';
    const TYPE_RESOURCE = 'resource';
    const TYPE_NULL = 'NULL';
    const TYPE_UNKNOWN = 'unknown';

    const INIT = '{import("{$priya.dir.module}Parse/Function/")}';

    const EQUAL = '=';
    const SPACE = ' ';
    const DOT = '.';
    const MIN = '-';
    const UNDERSCORE = '_';
    const NEWLINE = "\n"; //move to parse
    const SLASH_FORWARD = '/';
    const SLASH_BACKWARD = '\\';
    const QUOTE_SINGLE = '\'';
    const QUOTE_DOUBLE = '"';
    const STRING_EMPTY = '';

    public function read($url=''){
        $file = new File();
        $ext = $file->extension($url);
        if($ext == '' || $ext == Autoload::EXT_JSON){
            $this->data('priya.module.parser.document.url', $url);
            $read = parent::read($url);
            if(!empty($read)){
                $read = $this->data($this->compile($this->data(), $this->data(), false));
            }
        } else {
            $read = $file->read($url);
            $this->data('priya.module.parser.document.url', $url);
            $read = $this->compile($read, $this->data(), false);
        }
        return $read;
    }

    public function compile($string, $data=null, $keep=false){
        if($data !== null){
            //might need to restore previous data at the end of compile.
            $this->data($data);
        }
        $import = $this->data('priya.module.parser.import');
        if(empty($import)){
            $this->data('priya.module.parser.import', array());
        }
        $require = $this->data('priya.module.parser.require');
        if(empty($require)){
            $this->data('priya.module.parser.require', array());
        }
        if($this->data('priya.module.parser.literal') === true){
            return $string;
        }
        Parse::token(Parse::INIT, $data, $keep, $this);
        return Parse::token($string, $data, $keep, $this);
    }

    public static function random(){
        return rand(1000, 9999) . Parse::MIN . rand(1000,9999) . Parse::MIN . rand(1000,9999) . Parse::MIN . rand(1000,9999);
    }

    public static function token($string, $data=null, $keep=false, $parser=null){
        if($parser->data('priya.module.parser.literal') === true){
            return $string;
        }
        $type = getType($string);
        if(
            in_array(
                $type,
                array(
                    Parse::TYPE_NULL,
                    Parse::TYPE_BOOLEAN,
                    Parse::TYPE_FLOAT,
                    Parse::TYPE_INTEGER,
                )
            )
        ){
            return $string;
        }
        if(
            $type == Parse::TYPE_STRING &&
            is_numeric($string)
        ){
            return $string;
        }
        if ($type == Parse::TYPE_ARRAY){
            foreach($string as $nr => $line){
                $string[$nr] = Parse::token($line, $data, $keep, $parser);
            }
            return $string;
        }
        elseif($type == Parse::TYPE_OBJECT){
            foreach ($string as $key => $value){
                $key_type = gettype($key);
                //add key compile
                if($key_type == Parse::TYPE_STRING){
                    //keep the key value otherways it might be overwritten
                    $string->{Parse::token($key, $data, true, $parser)} = Parse::token($value, $data, $keep, $parser);
                } else {
                    $string->{$key} = Parse::token($value, $data, $keep, $parser);
                }
            }
            return $string;
        } else {
            if($parser->data('priya.debug') === true){
//                 var_dump($string);
//                 var_dump(debug_backtrace(true));
//                 die;
            }
            $parser->data('priya.module.parser.document.size', strlen($string));
            $parser->data('priya.module.parser.document.content', $string);

            if($parser->data('priya.module.parser.assign.operator') === true){
                $string = Operator::find($string, $parser);
                $parser->data('delete', 'priya.module.parser.assign.operator');
            }

            /*
            if($parser->data('priya.module.parser.literal') !== true){
                $string = Operator::find($string, $parser);
            }
            */
            $tags = Tag::find($string, $parser);
//             var_dump($tags);
            foreach($tags as $nr => $tag){
                $string = Literal::find($tag, $string, $parser); //can trigger literal mode
                if($parser->data('priya.module.parser.literal') === true){
                    continue;
                }
                $string = Priya::find($tag, $string, $parser); //can trigger literal mode
                if($parser->data(Priya::DATA_LITERAL) === true){
                    continue;
                }
                $string = Assign::find($tag, $string, $parser);
                $string = Variable::find($tag, $string, $keep, $parser);
                $string = Method::find($tag, $string, $parser);
//                 var_dump($string);
            }
            return $string;
        }
    }
}