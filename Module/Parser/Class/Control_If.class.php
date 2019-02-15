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

    public static function depth($raw='', $random=''){
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

    public static function create($list=array(), $record=[], $random=''){
        if(isset($record['if']) && isset($record['if']['tag'])){
            $record['key'] = $record['if']['tag'];
        }
        if(!isset($record['key'])){
            return $record;
        }
        $if = Control_If::get($list);
        $explode = explode($if, $record['string'], 2);
        if(!isset($explode[1])){
            throw new Exception('Malformed if tag...');
        }
        $inner_raw = $explode[1];
        $depth = Control_If::depth($inner_raw, $random);
        $true = Control_If::true($depth);
        $false = Control_If::false($depth);

        $record['if']['original']['true'] = $true;
        if($false === false){
            $record['if']['original']['false'] = null;
        } else {
            $record['if']['original']['false'] = $false;
        }
        $explode = explode('[' . $random . '][newline]', $true);
        if(isset($explode[1])){
            $empty = trim(end($explode));
            if(empty($empty)){
                array_pop($explode);
                $true = implode('[' . $random . '][newline]', $explode);
            }
        }
        if($false !== false){
            $explode = explode('[' . $random . '][newline]', $false);
            if(isset($explode[1])){
                $empty = trim(end($explode));
                if(empty($empty)){
                    array_pop($explode);
                    $false = implode('[' . $random . '][newline]', $explode);
                }
            }
        }
        $record['if']['true'] = $true;
        if($false === false){
            $record['if']['false'] = null;
        } else {
            $record['if']['false'] = $false;
        }
        $record['if']['statement'] = $if;
        return $record;
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

    public static function replace($record=array(), $parser=null){
        if($record['if']['false'] === null){
            $search = $record['if']['statement'] . $record['if']['original']['true'] . '{/if}';
        } else {
            $search = $record['if']['statement'] . $record['if']['original']['true'] . '{else}' . $record['if']['original']['false'] . '{/if}';
        }
        $explode = explode($search, $record['string'], 2);
        $temp = explode('[' . $parser->random() . '][newline]', $explode[1], 2);
        if(isset($temp[1])){
            $empty = trim($temp[0]);
            if(empty($empty)){
                $explode[1] = $temp[1];
            }
        }
        $temp = explode('[' . $parser->random() .'][newline]', $record['if']['string'], 2);
        if(empty(trim($temp[0])) && isset($temp[1])){
            $record['if']['string'] = $temp[1];
        }
        $record['string'] = implode($record['if']['string'], $explode);
        return $record;
    }

    public static function statement($record=array(), $parser=null){
        $create = Token::newline_restore($record['if']['statement'], $parser->random());
        $create = str_replace('{if', '', substr($create, 0, -1));
        $parse = Token::parse($create);
        $parse = Token::variable($parse, '', $parser);
        $record['parse'] = $parse;
        $record = Token::method($record, $parser);
        $parse = $record['parse'];
        $math = Token::create_equation($parse, $parser);
        $record = Control_If::execute($record, $math, $parser->random());
        $record = Control_If::replace($record, $parser);
        return $record;
    }
}