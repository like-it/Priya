<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_error($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $type = array_shift($argumentList);
    $attribute = array_shift($argumentList);
    $val = array_shift($argumentList);
    $result = $parser->error($type, $parser->random() . '.' . $attribute, $val);
    $random = $parser->error($parser->random());
    if(is_object($random)){
        $hasKey = false;
        foreach($random as $key){
            $hasKey = true;
            break;
        }
        if(empty($hasKey)){
            $parser->error('delete', $parser->random());
        }
    }
//     $parser->debug($result);
    return $result;
}
