<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

use Priya\Application;
use Priya\Module\File\Dir;

function function_import_function($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $name = key_exists(0, $argumentList) ? $argumentList[0] : null;
    $from = key_exists(1, $argumentList) ? $argumentList[1] : null;

    if(Dir::exist($name)){
        if(substr($name, -1, 1) != Application::DS){
            $name .= Application::DS;
        }
        $dir = $parser->data('priya.parser.function.dir');
        if($dir === null){
            $dir = [];
        }
        $dir[] = $name;
        $parser->data('priya.parser.function.dir', $dir);        
    }
    $function['execute'] = null;
    return $function;    

}
