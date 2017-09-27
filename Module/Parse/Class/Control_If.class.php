<?php

namespace Priya\Module\Parse;

class Control_If extends Core {
    const MAX = 3;

    public function __construct($data=null, $random=null){
        $this->data($data);
        $this->random($random);
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

    public static function execute($record=array(), $math=null){
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
        $explode = explode($record['if']['value'], $record['string'], 2);
        $record['execute'] = implode($record['if']['string'], $explode);
        $record['string'] = $record['execute'];
        return $record;
    }

    public function statement($record=array()){
        $create = Token::restore_return($record['if']['statement'], $this->random());
        $create = str_replace('{if ', '', substr($create, 0, -1));
        $variable = new Variable($this->data(), $this->random());
        // an equation can be a variable, if it is undefined it will be + 0
        // an equeation can have functions.
        $parse = Token::parse($create);
        $parse = Token::variable($parse, $variable);
        $math = Token::create_equation($parse);
        $record = Control_If::execute($record, $math);	//rename to execute...
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

    public static function explode($delimiter=array(), $string='', $internal=array()){
        $result = array();
        if(is_array($delimiter)){
            foreach($delimiter as $nr => $delim){
                if(strpos($string, $delim) === false){
                    continue; //speed... & always >=2
                }
                $tmp = Control_If::explode($delim, $string, $result);
                foreach ($tmp as $tmp_nr => $tmp_value){
                    $result[] = $tmp_value;
                }
            }
            $list = array();
            foreach ($result as $nr => $part){
                $splitted = false;
                foreach ($delimiter as $delim){
                    if(strpos($part, $delim) === false){
                        continue; //speed... & always >=2
                    }
                    $tmp = explode($delim, $part);
                    $splitted = true;
                    foreach($tmp as $part_splitted){
                        $list[$part_splitted][] = $part_splitted;
                    }
                }
                if(empty($splitted)){
                    $list[$part][] = $part;
                }
            }
            foreach($list as $part => $value){
                foreach ($delimiter as $delim){
                    if(strpos($part, $delim) !== false){
                        unset($list[$part]);
                    }
                }
            }
            $result = array();
            foreach($list as $part => $value){
                $result[] = $part;
            }
            if(empty($result)){
                $result[] = $string;
            }
            return $result;
        } else {
            $result = explode($delimiter, $string);
        }
        if(empty($result)){
            $result[] = $string;
        }
        return $result;
    }
}