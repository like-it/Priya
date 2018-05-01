<?php

use Priya\Module\Core\Cli;

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_terminal_height($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $cli = new Cli();
    $function['execute'] = $cli->tput('height');
    return $function;
}