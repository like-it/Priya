<?php
/**
 * @author Remco van der Velde
 * @copyright(c) Remco van der Velde
 * @since 2019-03-18
 *
 *
 * multiple parameters uses a logical AND
 */

use Priya\Module\Parse;

function function_math_round(
    Parse $parse, 
    $parameter = []
){            
    $value = key_exists(0, $parameter) ? $parameter[0] : null;
    $precision = key_exists(1, $parameter) ? $parameter[1] : 0;
    $mode = key_exists(2, $parameter) ? $parameter[2] : PHP_ROUND_HALF_UP;    
    $value = round($value, $precision, $mode);    
    return $value;
}
