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
use Priya\Module\Parse\Core_while;

function function_while(Parse $parse, $method=[], $token=[], $keep=false){

    if(!isset($method['method'])){
        return $token;
    }
    if($method['method']['name'] != 'while'){
        return $token;
    }
    $method = Core_while::select($method, $token, true);
    $token = Core_while::execute($parse, $method, $token, $keep, true);
    return $token;
}