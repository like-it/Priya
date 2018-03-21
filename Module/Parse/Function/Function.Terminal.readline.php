<?php

use Priya\Module\Core\Cli;

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_terminal_readline($tag, $parser=null){
    $argumentList = $tag['parameter'];
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $input = array_shift($argumentList);
    $hidden = array_shift($argumentList);
    $timeout = array_shift($argumentList);
    $cli = new Cli($parser->handler(), $parser->route(), $parser->data());
    $tag['execute'] = $cli->input($input, $hidden, $timeout);
    return $tag;
}