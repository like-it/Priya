<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

use Priya\Module\Parser\Control_Foreach;

function function_for_each($function=array(), $argumentList=array(), $parser=null, $data=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $list = Control_Foreach::list($function, $parser);
    $key = Control_Foreach::key($function);
    $record = Control_Foreach::record($function);
    $value = Control_Foreach::lower($function['string']);
//     echo __LINE__ . '::' . __FILE__ . $value;
    $value = Control_Foreach::get($value);

//     echo __LINE__ . '::' . __FILE__ . $value;
    $string = Control_Foreach::content($value);
    $string = Control_Foreach::literal($string, $parser);

    if($parser->data('priya.debug') === true){
//         var_Dump($string);
        //wrong position...
//         die;
    }

    if($string == ''){
        //can be caused by multiple foreaches with the same arguments...
        $function['execute'] = '';
    }
    elseif($string === false){
        $function['execute'] = '';
    } else {
        $function['execute'] = Control_Foreach::find($string, $list, $key, $record, $parser);
        $function['execute'] = Control_Foreach::replace($function['execute'], $parser);
        $string = Control_Foreach::replace($string, $parser);
        $function['string'] = Control_Foreach::finalize($string, $function);
    }
    return $function;
}
