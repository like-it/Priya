<?php

namespace Priya\Module\Parse;

class Control_If extends Core {
    const MAX = 2;

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

    public static function create($list=array(), $string=''){
        $result = array();
        $result['string'] = $string;
        $if = Control_If::get($list);

        $explode = explode($if, $string, 2);
        $inner_raw = $explode[1];
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
                $result['if']['value'] = $value;
            }
        }
        return $result;
    }

    /**
     * - create left right
     * - create true & false
     * - execute statement see operator::statement
     */
    public static function statement($statement=array()){
        if(in_array(
            $statement['operator'],
            Operator::Compare()
        )){
            switch($statement['operator']){
                case '&&' :
                    if($statement['left'] && $statement['right']){
                        $statement['output'] = true;
                    } else {
                        $statement['output'] = false;
                    }
                break;
                case '||' :
                    if($statement['left'] || $statement['right']){
                        $statement['output'] = true;
                    } else {
                        $statement['output'] = false;
                    }
                break;
                case 'and' :
                    if($statement['left'] and $statement['right']){
                        $statement['output'] = true;
                    } else {
                        $statement['output'] = false;
                    }
                break;
                case 'or' :
                    if($statement['left'] or $statement['right']){
                        $statement['output'] = true;
                    } else {
                        $statement['output'] = false;
                    }
                break;
                case 'xor' :
                    if($statement['left'] xor $statement['right']){
                        $statement['output'] = true;
                    } else {
                        $statement['output'] = false;
                    }
                break;
                case '==' :
                    if($statement['left'] == $statement['right']){
                        $statement['output'] = true;
                    } else {
                        $statement['output'] = false;
                    }
                break;
                case '===' :
                    if($statement['left'] === $statement['right']){
                        $statement['output'] = true;
                    } else {
                        $statement['output'] = false;
                    }
                break;
                case '<>' :
                    if($statement['left'] <> $statement['right']){
                        $statement['output'] = true;
                    } else {
                        $statement['output'] = false;
                    }
                break;
                case '!=' :
                    if($statement['left'] != $statement['right']){
                        $statement['output'] = true;
                    } else {
                        $statement['output'] = false;
                    }
                break;
                case '!==' :
                    if($statement['left'] !== $statement['right']){
                        $statement['output'] = true;
                    } else {
                        $statement['output'] = false;
                    }
                break;
                case '<' :
                    if($statement['left'] < $statement['right']){
                        $statement['output'] = true;
                    } else {
                        $statement['output'] = false;
                    }
                break;
                case '<=' :
                    if($statement['left'] <= $statement['right']){
                        $statement['output'] = true;
                    } else {
                        $statement['output'] = false;
                    }
                break;
                case '>' :
                    if($statement['left'] > $statement['right']){
                        $statement['output'] = true;
                    } else {
                        $statement['output'] = false;
                    }
                break;
                case '>=' :
                    if($statement['left'] >= $statement['right']){
                        var_dump('__STATEMENT______________________');
                        var_dump($statement['left']);
                        var_dump($statement['right']);
                        $statement['output'] = true;
                    } else {
                        $statement['output'] = false;
                    }
                break;
                case '<=>' :

                    /*
                     if($statement['left'] <=> $statement['right']){
                     $statement['output'] = true;
                     } else {
                     $statement['output'] = false;
                     }
                     break;
                     */
            }
        }
        return $statement;
    }
}