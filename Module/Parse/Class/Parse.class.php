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
 *     - 	modifier
 *     -	literal
 */

namespace Priya\Module;

use Exception;
use Priya\Module\Parse\Tag;
use Priya\Module\Parse\Assign;
use Priya\Module\Parse\Variable;
use Priya\Module\Parse\Method;

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

    const SPACE = ' ';
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
            //             debug($read, __LINE__ . '::' . __FILE__);
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
        Parse::token(Parse::INIT, $data, $keep, $this);
        return Parse::token($string, $data, $keep, $this);
    }

    public static function token($string, $data=null, $keep=false, $parser=null){
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
            $parser->data('priya.module.parser.document.size', strlen($string));
            $parser->data('priya.module.parser.document.content', $string);
            $tags = Tag::find($string, $parser);
            $string = $string;
            foreach($tags as $nr => $tag){
                $string = Method::find($tag, $string, $parser);
                $string = Variable::find($tag, $string, $keep, $parser);
                $string = Assign::find($tag, $string, $parser);
            }
            return $string;
            //first tags, rows + cols
        }
    }
}