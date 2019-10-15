<?php

namespace Priya\Module\Parse;

use Priya\Module\Parse;
use Exception;

class Core_foreach extends Core {
    const FOREACH = 'for.each';

    public static function select($foreach=[], $token=[], $need_tag_close=true){
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
            if($nr < $foreach['token']['nr']){
                continue;
            }
            if($nr == $foreach['token']['nr']){
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
                $record['foreach_depth'] = $depth;
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
                    $record['method']['name'] == Core_foreach::FOREACH
                ){
                    $depth++;
                }
                elseif(
                    $record['type'] == Token::TYPE_TAG_CLOSE &&
                    $record['tag']['name'] == '/' . Core_foreach::FOREACH
                ){
                    if($depth == 0){
                        $is_complete = true;
                        $foreach['token']['tag_close_nr'] = $nr;
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
        $foreach['method']['content'] = $content;
        if($is_complete === true){
            return $foreach;
        } else {
            throw new Exception('Could not find close tag: /for.each');
        }
    }

    public static function execute(Parse $parse, $foreach=[], $token=[], $keep=false, $need_tag=true){
        if(!isset($foreach['type'])){
            return $token;
        }
        if(
            $foreach['type'] == Token::TYPE_METHOD &&
            $foreach['method']['name'] == Core_foreach::FOREACH
        ){
            foreach($foreach['method']['parameter'] as $nr => $parameter){
                foreach($parameter as $key => $record){
                    unset($foreach['method']['parameter'][$nr][$key]['hold_execute']);
                }
            }
            $source = $foreach['method']['parameter'][0];
            $key = null;
            $value = null;
            $execute = reset($source);
            if($execute !== null){
                $token = Token::set_execute($parse, $source, $execute, $token, $keep, $need_tag);
                $execute = $token[$execute['token']['nr']];
                $source = $execute['execute'];
            } else {
                $source = [];
            }
            if(
                isset($foreach['method']['parameter'][1]) &&
                isset($foreach['method']['parameter'][2])
            ){
                //we have key => value
                $key = Token::create($foreach['method']['parameter'][1]);
                if(isset($key[1])){
                    $source = $parse->data('priya.parse.read.url');
                    if($source !== null){
                        throw new Exception('Parse error: multiple keys found in for.each starting at line: ' . $foreach['row'] . ' column: ' . $foreach['column'] . ' in: ' . $source);
                    } else {
                        throw new Exception('Parse error: multiple keys found in for.each starting at line: ' . $foreach['row'] . ' column: ' . $foreach['column']);
                    }
                }
                $key = $key[0];
                if($key['type'] != Token::TYPE_VARIABLE){
                    $source = $parse->data('priya.parse.read.url');
                    if($source !== null){
                        throw new Exception('Parse error: key is not a variable in for.each starting at line: ' . $foreach['row'] . ' column: ' . $foreach['column'] . ' in: ' . $source);
                    } else {
                        throw new Exception('Parse error: key is not a variable in for.each starting at line: ' . $foreach['row'] . ' column: ' . $foreach['column']);
                    }
                }
                $value = Token::create($foreach['method']['parameter'][2]);
                if(isset($value[1])){
                    $source = $parse->data('priya.parse.read.url');
                    if($source !== null){
                        throw new Exception('Parse error: multiple values found in for.each starting at line: ' . $foreach['row'] . ' column: ' . $foreach['column'] . ' in: ' . $source);
                    } else {
                        throw new Exception('Parse error: multiple values found in for.each starting at line: ' . $foreach['row'] . ' column: ' . $foreach['column']);
                    }
                }
                $value = $value[0];
                if($value['type'] != Token::TYPE_VARIABLE){
                    $source = $parse->data('priya.parse.read.url');
                    if($source !== null){
                        throw new Exception('Parse error: value is not a variable in for.each starting at line: ' . $foreach['row'] . ' column: ' . $foreach['column'] . ' in: ' . $source);
                    } else {
                        throw new Exception('Parse error: value is not a variable in for.each starting at line: ' . $foreach['row'] . ' column: ' . $foreach['column']);
                    }
                }
            }
            elseif(isset($foreach['method']['parameter'][1])){
                //we have => value
                $value = Token::create($foreach['method']['parameter'][1]);
                if(isset($value[1])){
                    $source = $parse->data('priya.parse.read.url');
                    if($source !== null){
                        throw new Exception('Parse error: multiple values found in for.each starting at line: ' . $foreach['row'] . ' column: ' . $foreach['column'] . ' in: ' . $source);
                    } else {
                        throw new Exception('Parse error: multiple values found in for.each starting at line: ' . $foreach['row'] . ' column: ' . $foreach['column']);
                    }
                }
                $value = $value[0];
            }
            $before = [];
            foreach($token as $nr => $record){
                if($nr == $foreach['token']['nr']){
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
                    $before[$nr] = $foreach;
                    $before[$nr]['in_execution'] = true;
                    break;
                }
                $before[$nr] = $record;
            }
            $level = (int) $parse->data('priya.parse.break.level');
            $level++;
            $parse->data('priya.parse.break.level', $level);
            $before[$foreach['token']['nr']]['execute'] = '';
            $count = 0;
            foreach($source as $source_key => $source_value){
                foreach($foreach['method']['content'] as $nr => $record){
                    $before[$nr] = $record;
                }
                if(
                    $key !== null &&
                    $value !== null
                ){
                    $key['is_executed'] = true;
                    $key['execute'] = $source_key;
                    $key_name = substr($key['variable']['name'], 1);
                    $parse->data($key_name, $key['execute']);
                    unset($key['variable']['is_modifier_execute']);
                    $attribute = $foreach['method']['parameter'][1];
                    $attribute[$key['token']['nr']] = $key;
                    $attribute = Variable::modify($parse, $key, $attribute, $keep);
                    $key = $attribute[$key['token']['nr']];
                    $key_name = substr($key['variable']['name'], 1);
                    $parse->data($key_name, $key['execute']);
                    $value['is_executed'] = true;
                    $value['execute'] = $source_value;
                    $value_name = substr($value['variable']['name'], 1);
                    $parse->data($value_name, $value['execute']);
                    unset($value['variable']['is_modifier_execute']);
                    $attribute[$value['token']['nr']] = $value;
                    $attribute = Variable::modify($parse, $value, $attribute, $keep);
                    $value = $attribute[$value['token']['nr']];
                    $value_name = substr($value['variable']['name'], 1);
                    $parse->data($value_name, $value['execute']);
                }
                $count++;
                if(null !== $parse->data('priya.parse.foreach.count') &&
                    $count > $parse->data('priya.parse.foreach.count')){
                    die;
                }
                $before = $parse->execute($before, true);
                $execute = [];
                foreach($foreach['method']['content'] as $nr => $record){
                    if(isset($before[$nr])){
                        $execute[$nr] = $before[$nr];
                        unset($before[$nr]);
                    }
                }
                unset($before[$foreach['token']['nr']]['is_parsed']);
                $before[$foreach['token']['nr']]['execute'] .= Token::string($execute);
                $before[$foreach['token']['nr']]['is_executed'] = true;
                $before[$foreach['token']['nr']] = Token::value_type($before[$foreach['token']['nr']], 'execute');
                //add continue
                if($parse->data('priya.parse.break.amount')){
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
            }
            $level = (int) $parse->data('priya.parse.break.level');
            $level--;
            $parse->data('priya.parse.break.level', $level);
            $is_tag_close = false;
            $current = null;
            $current_current = null;
            $need_next = false;
            foreach($token as $nr => $record){
                if($nr == $foreach['token']['tag_close_nr']){
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
            $before[$foreach['token']['nr']]['is_cleaned'] = true;
            return $before;
        }
        return $token;
    }
}