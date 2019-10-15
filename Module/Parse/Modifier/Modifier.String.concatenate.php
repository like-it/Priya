<?php
/**
 * @author      Remco van der Velde
 * @since       2019-03-16
 * @version     1.0
 * @changeLog
 *     -    all
 */

use Priya\Module\Parse;

function modifier_string_concatenate(Parse $parse, $variable=[], $parameter=[], $token=[], $keep=false){
    if(!isset($parameter[0])){
        throw new Exception('Parse error: modifier.string.concatenate needs a parameter...');
    } else {
        $string = $parameter[0];
        $variable['execute'] .= $string;
        $variable['is_executed'] = true;
        $token[$variable['token']['nr']] = $variable;
    }
    return $token;
}