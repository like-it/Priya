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
use Priya\Module\Parse\Core_if;

function function_if(Parse $parse, $method=[], $token=[], $keep=false){

    if(!isset($method['method'])){
        return $token;
    }
    if($method['method']['name'] != 'if'){
        return $token;
    }
    $method = Core_if::select($method, $token, true);
    $token = Core_if::execute($parse, $method, $token, $keep, true);
    return $token;
}