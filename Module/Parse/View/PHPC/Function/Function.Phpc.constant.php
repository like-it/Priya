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

function function_phpc_constant(
    Parse $parse,
    $parameter = [],
    &$token = [],
    $method = []
){            
    $constant = [];
    $constant[] = Compile::constant_meta($parse);          
    if(empty($constant)){
        return null;
    }
    
    return implode(PHP_EOL, $constant);
}
