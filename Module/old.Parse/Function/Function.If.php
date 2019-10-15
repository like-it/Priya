<?php
/**
 * @author Remco van der Velde
 * @copyright(c) Remco van der Velde
 * @since 2019-03-18
 *
 *
 *
 */

namespace Priya\Module\Parse;

use Priya\Module\Parse;

const IF_TAG_CLOSE = true;

function function_if(Parse $parse, $value){
    var_dump($value);
    die;
    $method = Core_if::select($method, $token, true);
    $token = Core_if::execute($parse, $method, $token, $keep, true);
    return $token;
}