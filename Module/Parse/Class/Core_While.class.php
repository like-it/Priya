<?php

namespace Priya\Module\Parse;

use Priya\Module\Parse;
use Exception;

class Core_while extends Core {
    const WHILE = 'while';

    public static function select($while=[], $token=[], $need_tag_close=true){
        $skip = 0;
        $depth = null;
        $is_close = false;
        $is_select = false;
        $is_parameter = false;
        $is_whitespace = false;
        $is_complete = false;
        $content = [];
        $count_if = 0;
        $count_else = 0;
        foreach($token as $nr => $record){
            if($skip > 0){
                $skip--;
                continue;
            }
            if($nr < $while['token']['nr']){
                continue;
            }
            if($nr == $while['token']['nr']){
                $is_parameter = true;
                continue;
            }
            if(
                $depth === null &&
                $is_select === false &&
                $is_parameter === true &&
                $record['type'] == Token::TYPE_PARENTHESE_OPEN
            ){
                $depth = $record['depth'];
                continue;
            }
            if(
                $depth !== null &&
                $is_select === false &&
                $is_parameter === true &&
                $record['type'] == Token::TYPE_PARENTHESE_CLOSE &&
                $depth == $record['depth']
            ){
                $depth = 0;
                $is_close = true;
            }
            if(
                $is_close === true &&
                $is_select === false
            ){
                if($need_tag_close === true){
                    $end = end($token);
                    $next = null;
                    $next_next = null;
                    $next = null;
                    $next_next = null;
                    if(!isset($end['token'])){
                        var_dump($end);
                        die;
                    }
                    if(!isset($end['token']['nr'])){
                        var_dump($end);
                        die;
                    }
                    for($i = $nr + 1; $i <= $end['token']['nr']; $i++){
                        if(
                            $next === null &&
                            isset($token[$i])
                        ){
                            $next = $i;
                            continue;
                        }
                        elseif(
                            $next !== null &&
                            isset($token[$i])
                        ){
                            $next_next = $i;
                            break;
                        }
                    }
                    if(
                        $next !== null &&
                        $next_next !== null &&
                        $token[$next]['type'] == Token::TYPE_WHITESPACE &&
                        $token[$next_next]['type'] == Token::TYPE_CURLY_CLOSE
                    ){
                        $skip += 2;
                    }
                    elseif(
                        $next !== null &&
                        $token[$next]['type'] == Token::TYPE_CURLY_CLOSE
                    ){
                        $skip += 1;
                    }
                    $is_select = true;
                    continue;
                }
            }
            if($is_select){
                $record['while_depth'] = $depth;
                if(
                    $is_whitespace === false &&
                    $record['type'] == Token::TYPE_WHITESPACE
                ){
                    $record = Token::remove_empty_line($record, 'value');
                    $content[$record['token']['nr']] = $record;
                    $is_whitespace = true;
                    continue;
                }
                $is_whitespace = true;
                if(
                    $record['type'] == Token::TYPE_METHOD &&
                    $record['method']['name'] == Core_while::WHILE
                ){
                    $depth++;
                }
                elseif(
                    $record['type'] == Token::TYPE_TAG_CLOSE &&
                    $record['tag']['name'] == '/' . Core_while::WHILE
                ){
                    if($depth == 0){
                        $is_complete = true;
                        $while['token']['tag_close_nr'] = $nr;
                        break;
                    }
                    $depth--;
                }
                $content[$record['token']['nr']] = $record;
            }
        }
        if($need_tag_close === true){
            $end = array_pop($content);
            $end_end = array_pop($content);
            if(
                isset($end) &&
                isset($end_end) &&
                $end['type'] == Token::TYPE_WHITESPACE &&
                $end_end['type'] == Token::TYPE_CURLY_OPEN
            ){
                //do nothing
            }
            elseif(
                isset($end) &&
                $end['type'] == Token::TYPE_CURLY_OPEN
                ){
                    if(isset($end_end)){
                        $content[$end_end['token']['nr']] = $end_end;
                    }
            } else {
                if(isset($end_end)){
                    $content[$end_end['token']['nr']] = $end_end;
                }
                if(isset($end)){
                    $content[$end['token']['nr']] = $end;
                }
            }
        }
        $end = end($content);
        if($end['type'] == Token::TYPE_WHITESPACE){
            $explode = explode("\n", $end['value']);
            if(isset($explode[1])){
                $pop = array_pop($explode);
                $explode[] = rtrim($pop);
                $content[$end['token']['nr']]['value'] = implode("\n", $explode);
            }
        }
        $while['method']['content'] = $content;
        if($is_complete === true){
            return $while;
        } else {
            throw new Exception('Could not find close tag: /if');
        }
    }

    public static function execute(Parse $parse, $while=[], $token=[], $keep=false, $need_tag=true){
        if(!isset($while['type'])){
            return $token;
        }
        if(
            $while['type'] == Token::TYPE_METHOD &&
            $while['method']['name'] == Core_while::WHILE
        ){
            $parameter = $while['method']['parameter'];
            if(isset($parameter[1])){
                throw new Exception('Parse error: unexpected , in while statement starting at line: ' . $while['row'] . ' column: ' . $while['column'] . ' in: ' . $parse->data('priya.parse.read.url'));
                // we might do a logical and for this...
            }
            $before = [];
            foreach($token as $nr => $record){
                if($nr == $while['token']['nr']){
                    $end = array_pop($before);
                    $end_end = array_pop($before);
                    if(
                        isset($end) &&
                        isset($end_end) &&
                        $end['type'] == Token::TYPE_WHITESPACE &&
                        $end_end['type'] == Token::TYPE_CURLY_OPEN
                    ){
                        //do nothing
                    }
                    elseif(
                        isset($end) &&
                        $end['type'] == Token::TYPE_CURLY_OPEN
                    ){
                        $before[$end_end['token']['nr']] = $end_end;
                    }
                    $end = array_pop($before);
                    $end = Token::remove_empty_line($end, 'value', false);
                    $before[$end['token']['nr']] = $end;
                    $before[$nr] = $while;
                    $before[$nr]['in_execution'] = true;
                    break;
                }
                $before[$nr] = $record;
            }
            foreach($while['method']['content'] as $nr => $record){
                $before[$nr] = $record;
            }
            $level = (int) $parse->data('priya.parse.break.level');
            $level++;
            $parse->data('priya.parse.break.level', $level);
            $before[$while['token']['nr']]['execute'] = '';
            $count = 0;
            while($parameter[0]){
                $count++;
                if($count > 4){
                    die;
                }
                $before = $parse->execute($before, true);
                /*
                if(null !== $parse->data('priya.parse.flush.nr')){
                    var_Dump($parse->data('priya.parse.flush.nr'));
                    var_dump($before);
                    die;
                    //                     $parse->data('priya.parse.flush.nr', $if['token']['nr'] - 1);
                }
                */




//                 var_dump($before);
//                 die;
//                 var_dump($before);
//                 die;
                $execute = [];
                foreach($while['method']['content'] as $nr => $record){
                    if(isset($before[$nr])){
                        $execute[$nr] = $before[$nr];
                        unset($before[$nr]);
                    }
                }
                if(!isset($before[$while['token']['nr']])){
                    var_dump($while['token']['nr']);
                    var_dump($before);
//                     die;
                }
                unset($before[$while['token']['nr']]['is_parsed']);
                $before[$while['token']['nr']]['execute'] .= Token::string($execute);
//                 var_dump($before[$while['token']['nr']]['execute']);
                $before[$while['token']['nr']]['is_executed'] = true;
                $before[$while['token']['nr']] = Token::value_type($before[$while['token']['nr']], 'execute');
                if($parse->data('priya.parse.break.amount')){
//                     var_dump($before[$while['token']['nr']]);
                    /*
                    var_dump($before[$while['token']['nr']]['execute']);
                    var_dump($execute);
//                     var_dump($before);
                    die;
                    */
                    $amount = $parse->data('priya.parse.break.amount');
                    $amount--;
                    if($amount < 1){
                        $parse->data('delete', 'priya.parse.halt');
                        $parse->data('delete', 'priya.parse.break');
                        break;
                    } else {
                        $parse->data('priya.parse.break.amount', $amount);
                        break;
                    }
                }
                $execute = current($while['method']['token_parameter'][0]);
                $before = Token::set_execute($parse, $while['method']['token_parameter'][0], $execute, $before);
                $execute = $before[$execute['token']['nr']];
                unset($before[$execute['token']['nr']]);
                //need to remove execute from before
                foreach($while['method']['content'] as $nr => $record){
                    $before[$nr] = $record;
                }
                $parameter[0] = $execute['execute'];
            }
            $level = (int) $parse->data('priya.parse.break.level');
            $level--;
            $parse->data('priya.parse.break.level', $level);
            $is_tag_close = false;
            $current = null;
            $current_current = null;
            $need_next = false;
            foreach($token as $nr => $record){
                if($nr == $while['token']['tag_close_nr']){
                    $is_tag_close = true;
                    continue;
                }
                if($need_next === true){
                    $record = Token::remove_empty_line($record, 'value');
                    $need_next = false;
                }
                if($is_tag_close === true && $current === null){
                    $current = $nr;
                    continue;
                }
                elseif($is_tag_close === true &&
                    $current !== null &&
                    $current_current === null
                ){
                    $current_current = $nr;
                    if($token[$current]['type'] == Token::TYPE_CURLY_CLOSE){
                        $record = Token::remove_empty_line($record, 'value');
                        $before[$nr] = $record;
                        continue;
                    }
                    elseif(
                        $token[$current]['type'] == Token::TYPE_WHITESPACE &&
                        $token[$current_current]['type'] == Token::TYPE_CURLY_CLOSE
                    ){
                        $need_next = true;
                        continue;
                    }
                }
                if($is_tag_close === true){
                    $before[$nr] = $record;
                }
            }
            $before[$while['token']['nr']]['is_cleaned'] = true;
            return $before;
        }
        return $token;
    }
}