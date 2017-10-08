<?php

namespace Priya\Module\Parse;

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

    public static function true($parse=array(), $if=''){
        $reset = reset($parse);
        $collect = false;
        $value = '';
        foreach($parse as $nr => $record){
            if($record['depth'] == $reset['depth']){
                $collect = true;
            }
            if($record['depth'] == $reset['depth'] && stristr($record['string'], '{else}') !== false){
                $part = explode('{else}', $record['string'], 2);
                if(count($part) == 1){
                    $value .= $record['string'];
                } else {
                    $value .= $part[0];;
                }
                continue;
            }
            if($collect === true){
                $value .= $record['string'];
            }
            elseif($record['depth'] == $reset['depth']){
                $collect = false;
            }
        }
        $explode = explode($if, $value);
        if(count($explode) == 2){
            return $explode[1];
        }
    }

    public static function false($parse=array()){
        $reset = reset($parse);
        $collect = false;
        $value = '';
        foreach($parse as $nr => $record){
            if($record['depth'] == $reset['depth'] && stristr($record['string'], '{else}') !== false){
                $explode = explode('{else}', $record['string'], 2);
                $value .= $explode[1];
            }
        }
        return substr($value, 0, -5);
    }



    public static function create($list=array(), $string='', $random=''){
        $result = array();
        $result['string'] = $string;
        $if = Control_If::get($list);
        $explode = explode($if, $string, 2);
        $inner_raw = $explode[1];
        //bug inner raw should go to the end of this if...
        //will be set when available
        $explode = explode('{if', $inner_raw);

        $list = array();
        foreach($explode as $nr => $record){
            $item = array();
            $item['if_open_count'] = $nr + 1;
            $item['if_close_count'] = substr_count($record, '{/if}');
            if($nr == 0){
                $item['part'] = $if . $record;
            } else {
                $item['part'] = '{if' . $record;
            }
            $list[] = $item;
            if($item['if_open_count'] == $item['if_close_count']){
                $value = '';
                foreach($list as $list_nr => $list_record){
                    $temp = explode('{/if}', $list_record['part']);
                    if(count($temp) > 1){
                        array_pop($temp);
                        $value .= implode('{/if}', $temp) . '{/if}';
                    } else {
                        $value .= $list_record['part'];
                    }
                }
                $temp = explode($if, $value, 2);
                $inner_raw = substr($temp[1], 0, -5);
                $raw = $value;
                $depth = Control_If::depth($raw);
                $true = Control_If::true($depth, $if);
                $false = Control_If::false($depth);
                if($false === false){
                    $result['if']['true'] = $true;
                    $result['if']['false'] = null;
                } else {
                    $result['if']['true'] = $true;
                    $result['if']['false'] = $false;
                }
                $result['if']['value'] = $value;
            }
        }
        $result['if']['statement'] = $if;
        return $result;
    }

    public static function row($record=array(), $random=''){
        $string = Token::restore_return($record['string'], $random);
        $statement = Token::restore_return($record['if']['value'], $random);
        $if = Token::restore_return($record['if']['string'], $random);
        $anchor = '[' . $random . '][anchor]';
        $string = str_replace($statement, $anchor . $if, $string);
        $explode = explode("\n", $string);
        foreach($explode as $nr => $row){
            if(trim($row) == $anchor){
                unset($explode[$nr]);
                break;
            }
            $explode[$nr] = str_replace($anchor, '', $row, $count);
            if($count > 0){
                break;
            }
        }
        $string = implode("\n", $explode);
        $record['string'] = Newline::replace($string, $random);
        return $record;
    }

    public static function execute($record=array(), $math=null, $random=''){
        $record['if']['result'] = $math;
        if($math === true){
            $record['if']['string'] = $record['if']['true'];
        }
        elseif($math === false){
            $record['if']['string'] = $record['if']['false'];
        } else {
            debug('unknown math in execute');
            debug($math, 'math');
            debug($record, 'record');
        }
        $record = Control_if::row($record, $random);

        /**
         * get the row of $record['if']['value']
         * if trim row == $record['if']['value'] explode on the row...
         */
        /*
        $explode = explode($record['if']['value'], $record['string'], 2);
        $record['execute'] = implode($record['if']['string'], $explode);
        $record['string'] = $record['execute'];
        */
        return $record;
    }

    public function statement($record=array(), $parser=null){
        $create = Token::restore_return($record['if']['statement'], $this->random());
        $create = str_replace('{if ', '', substr($create, 0, -1));
        $variable = new Variable($this->data(), $this->random());
        // an equation can be a variable, if it is undefined it will be + 0
        // an equeation can have functions.
        $parse = Token::parse($create);
        $parse = Token::variable($parse, $variable);
        //method before create_equation ?

        $method = array();
        $method['parse'] = $parse;
        $method = Token::method($method, $variable, $this->parser());
        $parse = $method['parse'];
        debug($method, 'method');
        die;
        $math = Token::create_equation($parse, $variable, $parser);
        $record = Control_If::execute($record, $math, $this->random());	//rename to execute...
        return $record;
    }

    public static function depth($string=''){
        $depth = 0;
        $list = array();

        $explode = explode('{', $string);
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
            if($nr > 0){
                $record['string'] = '{' . $row;
            } else {
                if(empty($row)){
                    continue;
                }
                $record['string'] = $row;
            }
            $previous = end($list);
            if($previous['depth'] == $record['depth']){
                $key = key($list);
                if(!isset($list[$key]['part'])){
                    $list[$key]['part'][] = $previous['string'];
                }
                $list[$key]['part'][] = $record['string'];
                if(isset($key)){
                    $list[$key]['string'] .= $record['string'];
                    continue;
                }
            }
            $list[] = $record;
        }
        return $list;
    }
}