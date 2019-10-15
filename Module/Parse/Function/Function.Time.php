<?php
/**
 * @author Remco van der Velde
 * @copyright(c) Remco van der Velde
 * @since 2019-03-18
 *
 *
 *
 */

//namespace Priya\Module\Parse;

use Priya\Module\Parse;

function function_time(
    Parse $parse,
    $parameter = []
){
    $hour = key_exists(0, $parameter) ? $parameter[0] : null;
    $minute = key_exists(1, $parameter) ? $parameter[1] : null;
    $second = key_exists(2, $parameter) ? $parameter[2] : null;
    $month = key_exists(3, $parameter) ? $parameter[3] : null;
    $day = key_exists(4, $parameter) ? $parameter[4] : null;
    $year = key_exists(5, $parameter) ? $parameter[5] : null;    
    
    $execute = '';
    if($hour === null){
        $execute = time();
    }
    elseif(is_bool($hour)){
        $execute = microtime(true);
    } else {
        $execute = mktime(
            $hour,        
            $minute,
            $second,
            $month,
            $day,
            $year 
        );
    }    
    return $execute;    
}
