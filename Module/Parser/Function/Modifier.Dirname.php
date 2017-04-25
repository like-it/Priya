<?php
/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function modifier_dirname($value=null, $argumentList=array()){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $value = str_replace(array('\\', '\/'), DIRECTORY_SEPARATOR, $value);
    $dirname = dirname($value);
    if(empty($dirname)){
        return false;
    }
    return $dirname . DIRECTORY_SEPARATOR;
}