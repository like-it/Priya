<?php

namespace Priya\Module\Parser;

use Exception;

class Control_If extends Core {
    const MAX = 1024;

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

    public static function depth($raw=''){
        $depth = 0;
        $new_depth = 0;
        $explode = explode('{', $raw);
        $result = array();
        foreach($explode as $nr => $row){
            if(substr($row, 0, 2) == 'if'){
                $depth++;
                $record['depth'] = $depth;
            }
            elseif(substr($row, 0, 3) == '/if'){
                $record['depth'] = $depth;
                $depth--;
            }
            elseif(substr($row, 0, 4) == 'else'){
                $record['depth'] = $depth;
            } else {
                $record['depth'] = $depth;
            }
            $record['string'] = empty($nr) ? $row : '{' . $row;

            $result[] = $record;
        }
        $list = array();
        foreach($result as $record){
            if(
                substr($record['string'], 0, 4) == '{/if' &&
                $record['depth'] == 0
            ){
                break;
            }
            $list[] = $record;
        }
        return $list;
    }

    /*
    public static function depth($inner=''){
        $depth = 0;
        $new_depth = 0;
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
            if(substr($row,0, 2) == 'if'){
                $new_depth++;
                $record['new_depth'] = $new_depth;
            }
            if(substr($row,0, 4) == 'else'){
//                 $record['new_depth'] = $depth;
//                 $depth--;
            }
            if(substr($row,0, 3) == '/if'){
                $record['new_depth'] = $new_depth;
                $new_depth--;
            }
            $record['new_depth'] = $new_depth;
            $record['string'] = empty($nr) ? $row : '{' . $row;

            $result[] = $record;
        }
       return $result;
    }
    */

    public static function create($list=array(), $string='', $random=''){
        $result = array();
        $result['string'] = $string;
        $if = Control_If::get($list);
        $explode = explode($if, $string, 2);
        $inner_raw = $explode[1];
        $depth = Control_If::depth($inner_raw);
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

    public static function if_execute($record=array(), $math=null, $random=''){
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

    public static function statement($record=array(), $parser=null){
        $create = Token::restore_return($record['if']['statement'], $parser->random());
        $create = str_replace('{if ', '', substr($create, 0, -1));
        $parse = Token::parse($create);
        $parse = Token::variable($parse, '', $parser);

        $method = array();
        $method['parse'] = $parse;
        $method = Token::method($method, $parser);
        $parse = $method['parse'];
        $math = Token::create_equation($parse, $parser);
        $record = Control_If::if_execute($record, $math, $parser->random());
        $record = Control_If::replace($record);
        return $record;
    }
}