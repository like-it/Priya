<?php
/**
 * @author Remco van der Velde
 * @copyright(c) Remco van der Velde
 * @since 2019-03-18
 *
 *
 *
 */

// namespace Priya\Module\Parse;

use Priya\Module\Parse;
use Priya\Module\Parse\Compile;

function function_phpc_use(
    Parse $parse,
    $parameter = [],
    &$token = [],
    $method = []
){    
    $use = [];
    $use[] = Compile::use_name('stdClass');
    $use[] = Compile::use_name('Exception');
    $use[] = Compile::use_name('Priya\Module\Parse');
    $use[] = Compile::use_name('Priya\Module\Parse\Token');         
    $use[] = '';
    if(empty($use)){
        return null;
    }
    return implode(PHP_EOL, $use);
}
