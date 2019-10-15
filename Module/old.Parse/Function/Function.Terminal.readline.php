<?php
/**
 * @author Remco van der Velde
 * @copyright(c) Remco van der Velde
 * @since 2019-03-18
 *
 *
 *
 */

//namespace Priya\Module\Parse;

use Priya\Module\Parse;
use Priya\Module\Parse\Token;
use Priya\Module\Core\Cli;

function function_terminal_readline(Parse $parse, $method=[], $token=[], $keep=false, $tag_remove=true){

    if(
        !isset($method['method']) ||
        !isset($method['token']) ||
        !isset($method['token']['nr'])
    ){
        return $token;
    }
    if($method['method']['name'] != 'terminal.readline'){
        return $token;
    }
    $input = array_shift($method['method']['parameter']);
    $hidden = array_shift($method['method']['parameter']);
    $timeout = array_shift($method['method']['parameter']);
    $cli = new Cli($parse->handler(), $parse->route(), $parse->data());
    $before = [];

    $previous = null;
    $previous_previous = null;
    $has_operator = false;
    $to = null;
    for($i = $method['token']['nr'] -1; $i >= 0; $i--){
        if(isset($token[$i])){
            if($previous === null){
                $previous = $i;
                if(
                    $has_operator === false &&
                    $token[$previous]['is_operator'] == true
                ){
                    $previous = null;
                    $has_operator = true;
                }
            }
            elseif($previous_previous === null){
                $previous_previous = $i;
                if(
                    $has_operator === false &&
                    $token[$previous]['type'] == Token::TYPE_WHITESPACE &&
                    $token[$previous_previous]['is_operator'] == true
                ){
                    $previous = null;
                    $previous_previous = null;
                    $has_operator = true;
                } else {
                    break;
                }
            }
        }
    }
    if(
        $previous !== null &&
        $token[$previous]['type'] == Token::TYPE_VARIABLE &&
        !empty($token[$previous]['variable']['is_assign'])
    ){
        $to = $previous;
        $assign = $to;
    }
    elseif(
        $previous_previous !== null &&
        $token[$previous_previous]['type'] == Token::TYPE_VARIABLE &&
        !empty($token[$previous_previous]['variable']['is_assign'])
    ){
        $to = $previous_previous;
        $assign = $to;
    }
    if($to === null){
        $to = $method['token']['nr'];
    }
    foreach($token as $nr => $record){
        if($nr == $to){
            break;
        }
        $before[$nr] = $record;
        //we can't unset due to while / foreach so we make sure they don't output again and get ignored by the parser
        $token[$nr]['type'] = null;
        $token[$nr]['execute'] = null;
        $token[$nr]['is_executed'] = true;
        $token[$nr]['is_parsed'] = true;
    }
    $end = array_pop($before);
    $end_end = array_pop($before);
    if(
        isset($end) &&
        isset($end_end) &&
        $end['type'] == Token::TYPE_WHITESPACE &&
        $end_end['type'] == Token::TYPE_CURLY_OPEN
    ){
        //do nothing

        unset($token[$end['token']['nr']]);
        unset($token[$end_end['token']['nr']]);
    }
    elseif(
        isset($end) &&
        $end['type'] == Token::TYPE_CURLY_OPEN
    ){
        $before[$end_end['token']['nr']] = $end_end;
        unset($token[$end['token']['nr']]);
    } else {
        $before[$end_end]['token']['nr'] = $end_end;
        $before[$end]['token']['nr'] = $end;
    }
    $end = end($before);
    if($end['type'] == Token::TYPE_WHITESPACE){
        $explode = explode("\n", $end['value']);
        if(isset($explode[1])){
            $pop = array_pop($explode);
            $explode[] = rtrim($pop);
            $before[$end['token']['nr']]['value'] = implode("\n", $explode);
            $token[$end['token']['nr']]['value'] = $before[$end['token']['nr']]['value'];
        }
    }


    /*
    $end = array_pop($before);
    $end_end = array_pop($before);
    if(
        isset($end) &&
        isset($end_end) &&
        $end['type'] == Token::TYPE_WHITESPACE &&
        $end_end['type'] == Token::TYPE_VARIABLE &&
        isset($end_end['variable']['is_assign']) &&
        $end_end['variable']['is_assign'] === true
    ){
        $assign = $end_end;
    }
    elseif(
        isset($end) &&
        $end['type'] == Token::TYPE_VARIABLE &&
        isset($end['variable']['is_assign']) &&
        $end['variable']['is_assign'] === true
    ){
        $assign = $end;
        $before[$end_end['token']['nr']] = $end_end;
    } else {
        $before[$end_end['token']['nr']] = $end_end;
    }
    */
    if($assign !== null){
        $previous = null;
        $previous_previous = null;
        $previous_previous_previous = null;
        for($i = $assign['token']['nr'] -1; $i >= 0; $i--){
            if(isset($before[$i])){
                if($previous === null){
                    $previous = $i;
                }
                elseif($previous_previous === null){
                    $previous_previous = $i;
                } else {
                    $previous_previous_previous = $i;
                    break;
                }
            }
        }
        if(
            $previous !== null &&
            $previous_previous !== null &&
            $before[$previous]['type'] == Token::TYPE_WHITESPACE &&
            $before[$previous_previous]['type'] == Token::TYPE_CURLY_OPEN
        ){
            unset($before[$previous]);
            unset($before[$previous_previous]);
            if(
                $previous_previous_previous !== null &&
                $before[$previous_previous_previous]['type'] == Token::TYPE_WHITESPACE
            ){
                $explode = explode("\n", $before[$previous_previous_previous]['value']);
                if(isset($explode[1])){
                    $end = array_pop($explode);
                    $explode[] = rtrim($end);
                    $before[$previous_previous_previous]['value'] = implode("\n", $explode);
                    $token[$previous_previous_previous]['value'] = $before[$previous_previous_previous]['value'];
                }
            }
        }
        elseif(
            $previous !== null &&
            $before[$previous]['type'] == Token::TYPE_CURLY_OPEN
        ){
            unset($before[$previous]);
            if(
                $previous_previous !== null &&
                $before[$previous_previous]['type'] == Token::TYPE_WHITESPACE
            ){
                $explode = explode("\n", $before[$previous_previous]['value']);
                if(isset($explode[1])){
                    $end = array_pop($explode);
                    $explode[] = rtrim($end);
                    $before[$previous_previous]['value'] = implode("\n", $explode);
                    $token[$previous_previous]['value'] = $before[$previous_previous]['value'];
                }
            }
        }
    }
    $string = Token::string($before);

//     $parse->data('priya.parse.flush.nr', $to);
    $method['execute'] = $cli->input($string . $input, $hidden, $timeout);
    $method = Token::value_type($method, 'execute');
    $method['is_debug'] = true;
    $token[$method['token']['nr']] = $method;

//     var_dump($token);
//     die;

    return $token;
}