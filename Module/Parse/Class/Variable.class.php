<?php

namespace Priya\Module\Parse;

use Priya\Module\Core;
use Priya\Module\Parse;
use Exception;

class Variable extends Core {
    const SIGN = '$';
    const EMPTY = '';

    const QUOTE_SINGLE = '\'';
    const QUOTE_DOUBLE = '"';

    const NOT_BEFORE = array(
        Variable::QUOTE_SINGLE,
        Variable::QUOTE_DOUBLE,
    );

    public static function find($tag='', $string='', $keep=false, $parser=null){
        $start_tag = false;
        $end_tag = false;
        foreach($tag['statement'] as $nr => $token){
            if($token['string'] == '{' && $start_tag == false){
                unset($tag['statement'][$nr]);
                $start_tag = true;
                continue;
            }
            elseif($token['string'] == '}' && $start_tag === true && $end_tag === false){
                $end_tag = true;
                unset($tag['statement'][$nr]);
                continue;
            }
            elseif($token['type'] == Tag::TYPE_VARIABLE){
                $tag['statement'][$nr]['execute'] = $parser->data(substr($token['string'], 1));
            }
        }
        $set_counter = 0;
        while(Set::has($tag['statement'])){
            $set_counter++;
            $replace = array();

            $set = Set::get($tag['statement']);
            $tag['set'][] = $set;
            $search = $set;
            $replace['string'] = Set::string($tag['statement']);

            $operator_counter = 0;
            while (Operator::has($set)){
                $operator_counter++;
                $set = Operator::find($set, $parser);
                if($operator_counter > Operator::MAX){
                    break;
                }
            }
            $replace['type'] = strtolower(gettype($set));
            $replace['execute'] = $set;
            $tag['statement'] = Set::replace($tag['statement'], $search, $replace);
            var_dump($tag);
            die;
            if($set_counter > Set::MAX){
                break;
            }
        }


        /**
         * while set::depth
         */




        var_dump($string);
        var_dump($tag);
        die;

        return $string;
        /*
        if(
            substr($tag[Tag::TAG], 0, 1) == Tag::OPEN &&
            substr($tag[Tag::TAG], -1, 1) == Tag::CLOSE &&
            substr($tag[Tag::TAG], 1, 1) == Variable::SIGN
        ){
            if(strpos($tag[Tag::TAG], Assign::EQUAL) !== false){
                //we might have assign;
                $explode = explode(Assign::EQUAL, $tag[Tag::TAG]);
                $before = $parser->explode_multi(Variable::NOT_BEFORE, $explode[0], 2);

                if(!isset($before[1])){
                    //we have assign
                    return $string;
                }
            }
            //have variable...
            $attribute = substr($tag[Tag::TAG], 2, -1);
//             var_Dump($attribute);
            $result =  $parser->data($attribute);
            if($result === null && $keep){
                return $string;
            }

            $explode = explode($tag[Tag::TAG], $string, 2);
            $type = gettype($result);
            if($type == Parse::TYPE_ARRAY){
                $result = Variable::EMPTY;
            }
            elseif($type == Parse::TYPE_OBJECT){
                $result = Variable::EMPTY;
            }
            $string = implode($result, $explode);
            return $string;
        }
        return $string;
        */
    }

    /*
    public static function get($string='', $parser=null){
        if(substr($string, 0, 1) !== '$'){
            return $string;
        }
        //have variable
        $attribute = substr($string, 1);
        return $parser->data($attribute);

    }
    */
}