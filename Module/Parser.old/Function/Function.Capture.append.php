<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_capture_append($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $value = str_replace('{capture.append', '{capture.append[' . $parser->random() . ']', $value);

    $explode = $parser->explode_single(array('{/capture}', '{capture.append'), $value);

    foreach($explode as $string){
        $temp = explode('[' . $parser->random() . '](', $string, 2);
        if(count($temp) == 2){
            $tmp = explode(')}', $temp[1], 2);
            if(count($tmp) != 2){
                continue;
            }
            $key = trim(reset($tmp), '\'"');

            $array = $parser->data($key);
            if(empty($array) || !is_array($array)){
                $array = array();
            }
            $parser->test = true;
            $tmp[1] = $parser->compile($tmp[1], $parser->data());
            $array[] = $tmp[1];
            $parser->data($key, $array);
            $search = '{capture.append' . $string . '{/capture}';
            $value = str_replace($search, '', $value);
        }
    }
    return $value;
}
