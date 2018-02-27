<?php

namespace Priya\Module\Parser;

use Exception;

class Control_If extends Core {
    const MAX = 1024;

    private $parser;

    public function __construct($data=null, $random=null, $parser=null){
        $this->data($data);
        $this->random($random);
        $this->parser($parser);
    }

    public function parser($parser=null){
        if($parser !== null){
            $this->setParser($parser);
        }
        return $this->getParser();
    }

    private function setParser($parser=''){
        $this->parser= $parser;
    }

    private function getParser(){
        return $this->parser;
    }

    public static function has($list=array()){
        return (bool) Control_If::get($list);
    }

    public static function get($list=array()){
        foreach($list as $nr => $record){
            $tag = key($record);
            if(substr($tag, 0, 3) == '{if'){
                return $tag;
            }
        }
        return false;
    }

    public static function true($if_depth=array()){
        $depth = 0;
        $true = '';
        foreach ($if_depth as $nr => $part){
            //add elseif...
            if($part['depth'] == $depth && stristr($part['string'], '{else}') !== false){
                break;
            }
            $true .= $part['string'];
        }
        return $true;
    }

    public static function false($if_depth=array()){
        $depth = 0;
        $false = '';
        $collect = false;
        foreach ($if_depth as $nr => $part){
            if($part['depth'] == $depth && stristr($part['string'], '{else}') !== false){
                $collect = true;
                $temp = explode('{else}', $part['string'], 2);
                $false .= $temp[1];
                continue;
            }
            if($collect){
                $false .= $part['string'];
            }
        }
        if($collect === false){
            return false;
        }
        return $false;
    }

    public static function inner($raw=''){
        $explode = explode('{if', $raw);
        $open_count = count($explode);
        $close_count = 0;
        $inner = '';
        foreach($explode as $nr => $part){
            $count = substr_count($part, '{/if}');
            if($count >= 1 && $nr == 0){
                $temp = explode('{/if}', $part, 2);
                $inner .= $temp[0];
                break;
            }
            $close_count += $count;

            if($open_count == $close_count){
                if($count > 1){
                    $temp = explode('{/if}', $part);
                    foreach($temp as $nr => $value){
                        if($count == ($nr + 1)){
                            $inner .= $value;
                            break;
                        } else {
                            $inner .= '{if' . $value . '{/if}';
                        }
                    }
                } else {
                    $temp = explode('{/if}', $part, 2);
                    $inner .= '{if' . $temp[0];
                }
            } elseif($nr > 0) {
                $inner .= '{if' . $part;
            } else {
                $inner .= $part;
            }
        }
        return $inner;
    }

    public static function depth($inner=''){
        $depth = 0;
        $explode = explode('{', $inner);
        $result = array();
        foreach($explode as $nr => $row){
            if(substr($row,0, 2) == 'if'){
                $depth++;
                $record['depth'] = $depth;
            }
            elseif(substr($row,0, 3) == '/if'){
                $record['depth'] = $depth;
                $depth--;
            }
            elseif(substr($row,0, 4) == 'else'){
                $record['depth'] = $depth;
            } else {
                $record['depth'] = $depth;
            }
            $record['string'] = empty($nr) ? $row : '{' . $row;

            $result[] = $record;
        }
       return $result;
    }

    public static function create($list=array(), $string='', $random=''){
        $result = array();
        $result['string'] = $string;
        $if = Control_If::get($list);
        $explode = explode($if, $string, 2);
        $inner_raw = $explode[1];
        $inner = Control_If::inner($inner_raw);
        $depth = Control_If::depth($inner);
        $true = Control_If::true($depth);
        $false = Control_If::false($depth);
        if($false === false){
            $result['if']['true'] = $true;
            $result['if']['false'] = null;
        } else {
            $result['if']['true'] = $true;
            $result['if']['false'] = $false;
        }
        $result['if']['statement'] = $if;
        return $result;
    }

    public static function execute($record=array(), $math=null, $random=''){
        $record['if']['result'] = $math;
        if($math === true){
            if(
                isset($record['if']) &&
                isset($record['if']['true'])
            ){
                $record['if']['string'] = $record['if']['true'];
            } elseif(isset($record['if'])){
                $record['if']['string'] = '';
            } else {
                throw new Exception('Unknown record format...');
            }
        }
        elseif($math === false){
            if(
                isset($record['if']) &&
                isset($record['if']['false'])
            ){
                $record['if']['string'] = $record['if']['false'];
            }
            elseif(isset($record['if'])){
                $record['if']['string'] = '';
            } else {
                throw new Exception('Unknown record format...');
            }
        } else {
           throw new Exception('Unknown math result');
        }
        return $record;
    }

    public static function replace($record=array()){
        if($record['if']['false'] === null){
            $search = $record['if']['statement'] . $record['if']['true'] . '{/if}';
        } else {
            $search = $record['if']['statement'] . $record['if']['true'] . '{else}' . $record['if']['false'] . '{/if}';
        }
        $explode = explode($search, $record['string'], 2);
        $record['string'] = implode($record['if']['string'], $explode);
        return $record;
    }

    public function statement($record=array(), $parser=null){
        $create = Token::restore_return($record['if']['statement'], $this->random());
        $create = str_replace('{if ', '', substr($create, 0, -1));
        $variable = new Variable($this->data(), $this->random(), $parser);
        $parse = Token::parse($create);
        $parse = Token::variable($parse, $variable);

        $method = array();
        $method['parse'] = $parse;
        $method = Token::method($method, $variable, $this->parser());
        $parse = $method['parse'];
        $math = Token::create_equation($parse, $variable, $parser);
        $record = Control_If::execute($record, $math, $this->random());
        $record = Control_If::replace($record);
        return $record;
    }
}