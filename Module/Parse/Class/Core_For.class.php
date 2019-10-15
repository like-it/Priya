<?php

namespace Priya\Module\Parse;

use Priya\Module\Parse;
use Exception;

class Core_for extends Core {
    const FOR = 'for';

    public static function select($for=[], $token=[], $need_tag_close=true){
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
            if($nr < $for['token']['nr']){
                continue;
            }
            if($nr == $for['token']['nr']){
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
                $record['for_depth'] = $depth;
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
                    $record['method']['name'] == Core_for::FOR
                ){
                    $depth++;
                }
                elseif(
                    $record['type'] == Token::TYPE_TAG_CLOSE &&
                    $record['tag']['name'] == '/' . Core_for::FOR
                ){
                    if($depth == 0){
                        $is_complete = true;
                        $for['token']['tag_close_nr'] = $nr;
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
        $for['method']['content'] = $content;
        if($is_complete === true){
            return $for;
        } else {
            throw new Exception('Could not find close tag: /for');
        }
    }

    //rename need_tag in tag_remove
    public static function execute(Parse $parse, $for=[], $token=[], $keep=false, $need_tag=true){
        if(!isset($for['type'])){
            return $token;
        }
        if(
            $for['type'] == Token::TYPE_METHOD &&
            $for['method']['name'] == Core_for::FOR
        ){            
            foreach($for['method']['parameter'] as $nr => $parameter){                
                foreach($parameter as $key => $list){
                    if(is_array($list)){
                        foreach($list as $token_nr => $value){                        
                            unset($for['method']['parameter'][$nr][$key][$token_nr]['hold_execute']);                       
                        }   
                    }                                                         
                }
            }                      
            if(!isset($for['method']['parameter'][1])){
                $condition[] = true;
            } else {
                $condition = $for['method']['parameter'][1];
            }

            foreach($condition as $nr => $boolean){
                $condition = $boolean;
                if($condition === false){                    
                    break;
                }
            }          
            $before = [];
            foreach($token as $nr => $record){
                if($nr == $for['token']['nr']){
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
                    $before[$nr] = $for;
                    $before[$nr]['in_execution'] = true;
                    break;
                }
                $before[$nr] = $record;
            }
            $level = (int) $parse->data('priya.parse.break.level');
            $level++;
            $parse->data('priya.parse.break.level', $level);
            $before[$for['token']['nr']]['execute'] = '';
            $count = 0;
            for(;$condition;){                
                foreach($for['method']['content'] as $nr => $record){
                    $before[$nr] = $record;
                }      
                $count++;
                if(null !== $parse->data('priya.parse.for.count') &&
                    $count > $parse->data('priya.parse.for.count')){
                        var_dump('fucking');
                    die;
                }       
                $condition_count = 0;                    
                $before = $parse->execute($before, true);                                                                 
                if(isset($for['method']['token_parameter'][2])){
                    foreach($for['method']['token_parameter'][2] as $condition_nr => $condition_list){
                        foreach($condition_list as $condition_list_nr => $condition_list_value){
                            unset($condition_list[$condition_list_nr]['hold_execute']);
                        }
                        $condition_count++;
                        $execute = reset($condition_list);
                        $execute['is_debug'] = true;                        
                        $before = Token::set_execute($parse, $condition_list, $execute, $before);                                      
                        $execute = $before[$execute['token']['nr']];                        
                        unset($before[$execute['token']['nr']]);                            
                        $end  = end($before);  
                        $token_nr = $execute['token']['nr'];
                        $execute['token']['nr'] = $end['token']['nr'] + 1;
                        if(isset($execute['token']['tag_close_nr'])){
                            $execute['token']['parenthese_close_nr'] = $execute['token']['nr'] - $token_nr + $execute['token']['tag_close_nr']; 
                        }
                        if(isset($execute['token']['parenthese_close_nr'])){
                            $execute['token']['parenthese_close_nr'] = $execute['token']['nr'] - $token_nr + $execute['token']['parenthese_close_nr']; 
                        }                        
                        $before[$execute['token']['nr']] = $execute;                        
                        // var_dump($end);
                        // var_dump($before);
                        // die;   
                    }
                    var_dump($before);                    

                    /*
                    foreach($for['method']['token_parameter'][2] as $condition_nr => $condition_list){
                        var_dump($condition_list);
                        die;
                        foreach($condition_list as $nr => $record){
                            unset($before[$record['token']['nr']]);
                            $record['token']['nr'] += $end['token']['nr'] + 1;     
                            $end['token']['nr'] = $record['token']['nr'];
                            if(isset($record['token']['parenthese_close_nr'])){
                                $record['token']['parenthese_close_nr'] += $end['token']['nr'];     
                                $end['token']['nr'] = $record['token']['parenthese_close_nr'];
                            }
                            if(isset($record['token']['tag_close_nr'])){
                                $record['token']['tag_close_nr'] += $end['token']['nr'];     
                                $end['token']['nr'] = $record['token']['tag_close_nr'];
                            }
                            unset($record['hold_execute']);  
                            if(
                                $record['type'] == Token::TYPE_VARIABLE && 
                                isset($record['variable']['value'])
                            ){
                                $value = [];
                                foreach($record['variable']['value'] as $value_nr => $value_record){
                                    $value_nr = $value_nr += $end['token']['nr'] + 1;
                                    $value_record['token']['nr'] = $value_nr;
                                    $value[$value_nr] = $value_record;                                    
                                }
                                $record['variable']['value'] = $value;                                
                            }                              
                            $curly_open = [
                                'column' => $record['column'],
                                'row' => $record['row'],
                                'is_operator' => false,
                                'depth' => $record['depth'],
                                'token' => [
                                    'nr' => $record['token']['nr'] - 1
                                ]
                            ];                       
                            $curly_close = [
                                'column' => $record['column'],
                                'row' => $record['row'],
                                'is_operator' => false,
                                'depth' => $record['depth'],
                                'token' => [
                                    'nr' => $record['token']['nr'] + 1
                                ]
                            ];                                                
                            $before[$curly_open['token']['nr']] = Token::create_token(Token::TYPE_CURLY_OPEN, $curly_open);                            
                            $before[$record['token']['nr']] = $record;                                                     
                            $before[$curly_close['token']['nr']] = Token::create_token(Token::TYPE_CURLY_CLOSE, $curly_close);                                                                       
                            var_dump($before);
                            var_dump($record);                               
                        }                        
                    } 
                    */                   
                }                                                                                              
                $execute = [];
                foreach($for['method']['content'] as $nr => $record){
                    if(isset($before[$nr])){
                        $execute[$nr] = $before[$nr];
                        unset($before[$nr]);
                    }
                }
                if($condition_count > 1){
                    $end = end($before);
                    for($i = $end['token']['nr'] - $condition_count; $i <= $end['token']['nr']; $i++){
                        if(isset($before[$i])){
                            $execute[$i] = $before[$i];
                            unset($before[$i]);
                        }
                    }   
                }                             
                unset($before[$for['token']['nr']]['is_parsed']);
                $before[$for['token']['nr']]['execute'] .= Token::string($execute);
                $before[$for['token']['nr']]['is_executed'] = true;
                $before[$for['token']['nr']] = Token::value_type($before[$for['token']['nr']], 'execute');
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

                if(!isset($for['method']['token_parameter'][1])){
                    $condition = true;
                } else {                    
                    foreach($for['method']['token_parameter'][1] as $nr => $condition){
                        $execute = reset($condition);
                        // var_dump($condition);
                        // var_dump($execute);                                              
                        $before = Token::set_execute($parse, $condition, $execute, $before);
                        $execute = $before[$execute['token']['nr']];                        
                        unset($before[$execute['token']['nr']]);
                        // var_dump($execute);
                        //need to remove execute from before
                        $condition = $execute['execute'];
                        if($condition === false){                    
                            break 2;
                        }                        
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
                if($nr == $for['token']['tag_close_nr']){
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
            $before[$for['token']['nr']]['is_cleaned'] = true;
            return $before;
        }
        return $token;
    }
}