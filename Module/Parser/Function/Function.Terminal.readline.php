<?php

use Priya\Module\Core\Cli;
use Priya\Module\Parser\Token;

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_terminal_readline($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $input = array_shift($argumentList);
    $hidden = array_shift($argumentList);
    $timeout = array_shift($argumentList);
    $cli = new Cli($parser->handler(), $parser->route(), $parser->data());

    $explode = explode($function['key'], $function['string'], 2);
    $before = $explode[0];
    echo Token::comment_remove(Token::literal_remove(Token::literal_restore(Token::newline_restore($before, $parser->random()), $parser->random())));
    $function['string'] = $function['key'] . $explode[1];
    $function['execute'] = $cli->input($input, $hidden, $timeout);
    return $function;
}