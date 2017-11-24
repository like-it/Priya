<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

use Priya\Module\Parser\Token;
use Priya\Module\Parser\Newline;

function function_capture_append($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $attribute = trim(array_shift($argumentList), '"\'');
    $value = $function['string'];
    $value = str_ireplace('{capture.append', '{capture.append', $value);
    $value = str_ireplace('{/capture}', '{/capture}', $value);
    $value = str_replace('{capture.append', '{capture.append[' . $parser->random() . ']', $value);
    $explode = $parser->explode_single(array('{/capture}', '{capture.append'), $value, 2);

    foreach($explode as $string){
        $temp = explode('[' . $parser->random() . '](', $string, 2);
        if(count($temp) == 2){
            $tmp = explode(')}', $temp[1], 2);
            if(count($tmp) != 2){
                continue;
            }
            $array = $parser->data($attribute);
            if(empty($array) || !is_array($array)){
                $array = array();
            }
            $tmp[1] = $parser->compile($tmp[1], $parser->data());
            $array[] = trim($tmp[1]);
            $parser->data($attribute, $array);
            $search = '{capture.append' . $string . '{/capture}';
            $string_length = strlen($value);
            $replace_length = str_replace($search, '', $value);

            $key = '[' . $parser->random() . '][capture]';
            $value = str_replace($search, $key, $value);
            if(strlen($string_length) == $replace_length){
                debug('str_replace not working');
            } else {
                $value = Token::restore_return($value, $parser->random());
                $var = explode("\n", $value);
                foreach($var as $nr => $var_value){
                    if(trim($var_value) == $key){
                        unset($var[$nr]);
                        break;
                    }
                }
                $value = implode("\n", $var);
                $value= Newline::replace($value, $parser->random());
            }
            break;
        }
    }
    $function['string'] = $value;
    $function['execute'] = '';
    return $function;
}
