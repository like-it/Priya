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

function function_phpc_parse_id(
    Parse $parse,
    $parameter = [],
    &$token = [],
    $method = []
){   
    $id = $parse->data(Compile::DATA_ID, $parse->data(Compile::DATA_ID) + 1);
    return $id;
}
