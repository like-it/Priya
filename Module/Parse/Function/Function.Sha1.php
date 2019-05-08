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
use Priya\Module\Parse\Token;

function function_sha1(
    Parse $parse,
    $parameter = [],
    &$token = [],
    $method = []
){       
    $string = key_exists(0, $parameter) ? $parameter[0] : null;     
    // var_dump($string);
    $raw_output = key_exists(1, $parameter) ? $parameter[1] : null;     
    if($raw_output !== null){
        return sha1($string, $raw_output);
    } else {
        return sha1($string);
    }
}