<?php

namespace Priya\Module\Parse;

class Set extends Core {
    const MAX = 10;

    public static function has($parse=array()){
        foreach ($parse as $nr => $record){
            if(!empty($record['set']) && !empty($record['set']['depth'])){
                return true;
            }
        }
        return false;
    }

    public static function get($parse=array()){
        $highest = Set::highest($parse);
        $is_set = false;
        $set = array();
        $statement = '';
        foreach ($parse as $nr => $record){
            if(isset($record['set']) && isset($record['set']['depth']) && $record['set']['depth'] == $highest){
                //first one found
                $is_set = true;
                //till first end parenthese
            }
            if($is_set === true){
                if(!empty($record['is_cast']) && isset($record['cast'])){
                    $statement .= '(' . $record['cast'] . ')';
                }
                if(isset($record['value'])){
                    $statement .= $record['value'];
                }
                $set[] = $record;
                if(isset($record['type']) && $record['type'] == 'parenthese' && $record['value'] == ')' && $record['set']['depth'] == $highest){
                    $is_set = false;
                    foreach($set as $set_nr => $set_value){
                        if(!isset($set_value['set'])){
                            $set_value['set'] = array();
                        }
                        $set_value['set']['statement'] = $statement;
                        $set[$set_nr] = $set_value;
                    }
                    return $set;
                }
            }
        }
        foreach($set as $set_nr => $set_value){
            if(!isset($set_value['set'])){
                $set_value['set'] = array();
            }
            $set_value['set']['statement'] = $statement;
            $set[$set_nr] = $set_value;
        }
        return $set;
    }

    public static function statement($parse=array()){
        $set = array();
        $statement = '';
        foreach ($parse as $nr => $record){
            $statement .= $record['value'];
            if($record['type'] == Token::TYPE_PARENTHESE){
                continue;
            }
            if($record['type'] == Token::TYPE_WHITESPACE){
                //check for cast
                continue;
            }
            $set[] = $record;
        }
        foreach($set as $nr => $record){
            if(!isset($record['set'])){
                $record['set'] = array();
            }
//             $record['set']['statement'] = $statement;
            $set[$nr] = $record;
        }
        return $set;
    }

    public static function highest($parse=array()){
        $depth = 0;
        foreach ($parse as $nr => $record){
            if(!empty($record['set']) && isset($record['set']['depth']) && $record['set']['depth'] > $depth){
                $depth = $record['set']['depth'];
            }
        }
        return $depth;
    }

    public static function remove($parse=array()){
        $set = array();
        foreach ($parse as $nr => $record){
            if(isset($record['set'])){
                continue;
            }
            $set[] = $record;
        }
        return $set;
    }

    public static function replace($parse=array(), $search=array(), $replace=array()){
        $match = reset($search); //deep //left to right
        $remove = false;
        $is_replace = false;
        foreach ($parse as $nr => $record){
            if(isset($record['set']) && $record['set']['depth'] == $match['set']['depth']){
                $remove = true;
            }
            if(!empty($remove)){
                if(empty($is_replace)){
                    $parse[$nr] = $replace;
                    $is_replace = true;
                    continue;
                }
                unset($parse[$nr]);
                if($record['type'] == 'parenthese' && $record['value'] == ')'){
                    break;
                }
            }
        }
        return $parse;
    }
}