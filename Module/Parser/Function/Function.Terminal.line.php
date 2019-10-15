<?php

use Priya\Module\Core\Cli;

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_terminal_line($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $string = (string) array_shift($argumentList);
    $length = (int) array_shift($argumentList);
    $char = (string) array_shift($argumentList);
    $add = array_shift($argumentList);

    if(!isset($add)){
        $add = '...';
    }

    if(empty($char)){
        $char = ' ';
    }

    if($length != 0){
        $cli = new Cli();
        $length = $cli->tput('width');
    }

    $width = strlen($string);

    if($width > $length){
        $string = substr($string, 0, $length - strlen($add)) . $add;
    }

    $width = strlen($string);

    if($width < $length){
        $string .= str_repeat($char, ($length - $width));
    }
    $function['execute'] = $string;

    return $function;
}