<?php
/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

use Priya\Module\File\Dir;

function modifier_dir_name($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $value = str_replace(array('\\', '\/'), DIRECTORY_SEPARATOR, $value);
    if(isset($argumentList[0])){
        $levels = $argumentList[0];
    } else {
        $levels = 1;
    }        
    $dirname = dir::name($value, $levels);
    if(empty($dirname)){
        return false;
    }
    return $dirname;
}
