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
use Priya\Module\Parse\Core_Foreach;

function function_for_each(Parse $parse, $method=[], $token=[], $keep=false){

    if(!isset($method['method'])){
        return $token;
    }
    if($method['method']['name'] != 'for.each'){
        return $token;
    }
    $method = Core_Foreach::select($method, $token, true);
    $token = Core_Foreach::execute($parse, $method, $token, $keep, true);
    return $token;
}