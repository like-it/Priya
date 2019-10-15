<?php

use Priya\Module\Core\Cli;

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_system_user_name($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $output = [];
    Cli::execute($parser, 'whoami', $output);
    $function['execute'] = implode(PHP_EOL, $output);
    return $function;
}