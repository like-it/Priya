<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

use Priya\Module\Parser\Control_Foreach;
use Priya\Module\Parser\Control_If;

function function_for_each($function=array(), $argumentList=array(), $parser=null, $data=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $list = Control_Foreach::list($function, $parser);

    $key = Control_Foreach::key($function);
    $record = Control_Foreach::record($function);

    $value = Control_Foreach::lower($function['string']);
    $value = Control_Foreach::get($value);

    $string = Control_Foreach::content($value);
    if($string === false){
        $function['execute'] = '';
    } else {
        $function['execute'] = Control_Foreach::find($string, $list, $key, $record, $parser);
        $function['string'] = Control_Foreach::finalize($string, $function);
    }
    return $function;
}
