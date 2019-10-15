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

function function_phpc_header(
    Parse $parse,
    $parameter = [],
    &$token = [],
    $method = []
){    
    $date = new DateTime();
    $parse->data('priya.version');
    $duration = microtime(true) - $parse->data('time.start');    
    $execute = ltrim('
/**
 * @copyright	(c) 2015 - ' . $date->format('Y') .' priya.software
 * @license		https://priya.software/license/
 * @author		Priya Module Parse Compile - ' . $parse->data('priya.version') . '
 * @version	    ' . $date->format('Y-m-d H:i:sP') . ' ' . $parse->data('priya.version') . '-PMPC-' . $parse->data('priya.parse.compile.version') . '
 * @support		https://priya.software/support/
 * @package		Priya\Module\Parse\Compile
 * @category	Conversion
 * @duration    ' . round($duration * 1000, 2) .  ' msec
 * @note        This is an automated file creation, please use the source file to edit
 * @source      ' . $parse->data('priya.parse.read.url') . '
 */
 ');
    return $execute;    
}
