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

function function_math_random(
    Parse $parse,
    $parameter = [],
    &$token = [],
    $method = []
){    
    $min = key_exists(0, $parameter) ? $parameter[0] : null;
    $max = key_exists(1, $parameter) ? $parameter[1] : null;
    
    if($min === null && $max === null){
        return rand();        
    } else {
        if(version_compare(PHP_VERSION, Parse::RANDOM_INT_VERSION, '>=')){
            return random_int($min, $max);            
        } else {
            return rand($min, $max);            
        }
    }
}