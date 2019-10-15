<?php
/**
 * @author      Remco van der Velde
 * @since       2019-03-16
 * @version     1.0
 * @changeLog
 *     -    all
 */

use Priya\Module\Parse;

function modifier_default2(Parse $parse, $parameter=[], &$token=[], $variable=[]){            
    if(empty($variable['execute'])){
        $value = key_exists(0, $parameter) ? $parameter[0] : null;   
        return $value;
    }
    return $variable['execute'];
}