<?php

namespace Priya\Module\Parse;

use Priya\Module\Parse;
use Exception;

class Core_if extends Core {
    const IF = 'if';
    const ELSE_IF = 'elseif';
    const ELSE = 'else';

    public static function select($if=[], $token=[], $need_tag_close=true){
        $elseif_nr = -1;
        $skip = 0;
        $depth = null;
        $is_close = false;
        $is_select = false;
        $is_parameter = false;
        $is_whitespace = false;
        $is_elseif = false;
        $is_else = false;
        $is_complete = false;
        $content = [
            'if' => null,
            'elseif' => [],
            'else' => null
        ];
        $count_if = 0;
        $count_else = 0;
        foreach($token as $nr => $record){
            if($skip > 0){
                $skip--;
                continue;
            }
            if($nr < $if['token']['nr']){
                continue;
            }
            if($nr == $if['token']['nr']){
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
                $record['if_depth'] = $depth;
                if(
                    $is_whitespace === false &&
                    $record['type'] == Token::TYPE_WHITESPACE
                ){
                    $record = Token::remove_empty_line($record);
                    $content['if'][$record['token']['nr']] = $record;
                    $count_if++;
                    $is_whitespace = true;
                    continue;
                }
                $is_whitespace = true;
                if(
                    $record['type'] == Token::TYPE_METHOD &&
                    $record['method']['name'] == Core_if::IF
                ){
                    $depth++;
                }
                elseif(
                    $record['type'] == Token::TYPE_TAG_CLOSE &&
                    $record['tag']['name'] == '/' . Core_if::IF
                ){
                    if($depth == 0){
                        $is_complete = true;
                        $if['token']['tag_close_nr'] = $nr;
                        break;
                    }
                    $depth--;
                }
                if(
                    $depth == 0 &&
                    $record['type'] == Token::TYPE_METHOD &&
                    $record['method']['name'] == Core_if::ELSE_IF
                ){
                    $elseif_nr++;
                }
                if(
                    $depth == 0 &&
                    $is_else === false &&
                    $record['type'] == Token::TYPE_STRING &&
                    $record['value'] == Core_if::ELSE
                ){
                    $elseif_nr = -1;
                    $else_start = null;
                    $else_start_start = null;
                    $else_start_start_start = null;
                    $is_else = true;
                    continue;
                }
                if($elseif_nr >= 0){
                    $content['elseif'][$elseif_nr][$record['token']['nr']] = $record;
                }
                elseif($is_else === true){
                    $content['else'][$record['token']['nr']] = $record;
                    if($need_tag_close === true){
                        if($else_start === null){
                            $else_start = $record['token']['nr'];
                        }
                        elseif($else_start_start === null){
                            $else_start_start = $record['token']['nr'];
                        }
                        elseif($else_start_start_start === null){
                            $else_start_start_start = $record['token']['nr'];
                        }
                    }
                    $count_else++;
                }
                else {
                    $content['if'][$record['token']['nr']] = $record;
                    $count_if++;
                }
            }
        }
        if($need_tag_close === true){
            $end = array_pop($content['if']);
            $end_end = array_pop($content['if']);
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
                //do nothing
            } else {
                if(isset($end_end)){
                    $content['if'][$end_end['token']['nr']] = $end_end;
                }
                if(isset($end)){
                    $content['if'][$end['token']['nr']] = $end;
                }
            }
            if(is_array($content['elseif'])){
                foreach ($content['elseif'] as $nr => $elseif){
                    $end = array_pop($content['elseif'][$nr]);
                    $end_end = array_pop($content['elseif'][$nr]);
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
                            //do nothing
                    } else {
                        if(isset($end_end)){
                            $content['elseif'][$nr][$end_end['token']['nr']] = $end_end;
                        }
                        if(isset($end)){
                            $content['elseif'][$nr][$end['token']['nr']] = $end;
                        }
                    }
                }
            }
            if($count_else > 0){
                $end = array_pop($content['else']);
                $end_end = array_pop($content['else']);
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
                            $content['else'][$end_end['token']['nr']] = $end_end;
                        }
                } else {
                    if(isset($end_end)){
                        $content['else'][$end_end['token']['nr']] = $end_end;
                    }
                    if(isset($end)){
                        $content['else'][$end['token']['nr']] = $end;
                    }
                }
                if($else_start !== null){
                    $start = $content['else'][$else_start];
                }
                if($else_start_start !== null){
                    $start_start = $content['else'][$else_start_start];

                }
                if(
                    isset($start) &&
                    isset($start_start) &&
                    $start['type'] == Token::TYPE_WHITESPACE &&
                    $start_start['type'] == Token::TYPE_CURLY_CLOSE
                ){
                    unset($content['else'][$else_start]);
                    unset($content['else'][$else_start_start]);
                    if($else_start_start_start !== null){
                        $content['else'][$else_start_start_start] = Token::remove_empty_line($content['else'][$else_start_start_start]);
                    }
                }
                elseif(
                    isset($start) &&
                    $start['type'] == Token::TYPE_CURLY_CLOSE
                ){
                    unset($content['else'][$else_start]);
                    if($else_start_start !== null){
                        $content['else'][$else_start_start] = Token::remove_empty_line($content['else'][$else_start_start]);
                    }
                }
            }
        }
        foreach($content as $attribute => $record){
            $if['method'][$attribute] = $record;
        }
        if($is_complete === true){
            return $if;
        } else {
            throw new Exception('Could not find close tag: /if');
        }
    }

    public static function create_elseif($list=[], $need_tag_close=true){
        if(!is_array($list)){
            return [];
        }
        $token = [];
        foreach ($list as $elseif_nr => $elseif){
            $is_parameter = false;
            $is_content = false;
            $depth = null;
            $count = 0;
            $parameter = [];
            $content = [];
            $nr_1 = null;
            $nr_2 = null;
            $nr_3 = null;
            $method_nr = null;
            foreach($elseif as $nr => $record){
                if(
                    $method_nr === null &&
                    $record['type'] == Token::TYPE_METHOD &&
                    $record['method']['name'] == Core_if::ELSE_IF
                ){
                    $method_nr = $nr;
                }
                $record['token']['nr'] = $nr;
                if(
                    $is_content === false &&
                    $is_parameter === false &&
                    $record['type'] == Token::TYPE_PARENTHESE_OPEN
                ){
                    $depth = $record['depth'];
                    $is_parameter = true;
                    continue;
                }
                elseif(
                    $is_content === false &&
                    $record['type'] == Token::TYPE_COMMA &&
                    $depth == $record['depth']
                ){
                    $count++;
                    continue;
                }
                elseif(
                    $is_content === false &&
                    $record['type'] == Token::TYPE_PARENTHESE_CLOSE &&
                    $depth == $record['depth']
                ){
                    $is_parameter = false;
                    $is_content = true;
                }
                elseif($is_parameter === true){
                    $parameter[$count][$record['token']['nr']] = $record;
                }
                elseif($is_content === true){
                    $content[$record['token']['nr']] = $record;
                    if($need_tag_close === true){
                        if($nr_1 === null){
                            $nr_1 = $record['token']['nr'];
                        }
                        elseif($nr_2 === null){
                            $nr_2 = $record['token']['nr'];
                        }
                        elseif($nr_3 === null){
                            $nr_3 = $record['token']['nr'];
                        }
                    }
                }
            }
            if($need_tag_close === true){
                if(
                    isset($content[$nr_1]) &&
                    $content[$nr_1]['type'] == Token::TYPE_CURLY_CLOSE
                ){
                    unset($content[$nr_1]);
                    if(isset($content[$nr_2])){
                        $content[$nr_2] = Token::remove_empty_line($content[$nr_2]);
                    }
                }
                elseif(
                    isset($content[$nr_1]) &&
                    isset($content[$nr_2]) &&
                    $content[$nr_1]['type'] == Token::TYPE_WHITESPACE &&
                    $content[$nr_2]['type'] == Token::TYPE_CURLY_CLOSE
                ){
                    unset($content[$nr_1]);
                    unset($content[$nr_2]);
                    if(isset($content[$nr_3])){
                        $content[$nr_3] = Token::remove_empty_line($content[$nr_3]);
                    }
                }
            }
            if(
                isset($elseif[$method_nr]) &&
                $elseif[$method_nr]['type'] == Token::TYPE_METHOD &&
                $elseif[$method_nr]['method']['name'] == Core_if::ELSE_IF
            ){
                $record = $elseif[$method_nr];
                $record['method']['parameter'] = $parameter;
                $record['method']['content'] = $content;
                $list[$elseif_nr] = $record;
            }
        }
        return $list;
    }

    /*
    public static function value($if=[]){
        $value = $if['value'];
        if(isset($if['method']['if'])){
            $value .= Token::string($if['method']['if']);
        }
        if(!empty($if['method']['elseif'])){
            foreach($if['method']['elseif'] as $nr => $elseif){
                $value .= Token::string($elseif['method']['content']);
            }
            var_dump($value);
            die;
        }
        if(!empty($if['method']['else'])){
            $value .= Token::string($if['method']['else']);
        }
        $if['value'] = $value;
        return $if;
    }
    */

    public static function execute(Parse $parse, $if=[], $token=[], $keep=false, $need_tag=true){
        if(!isset($if['type'])){
            return $token;
        }
        if(
            $if['type'] == Token::TYPE_METHOD &&
            $if['method']['name'] == Core_if::IF
        ){
            //we can solve if statement first...
            $parameter = $if['method']['parameter'];
            if(isset($parameter[1])){
                throw new Exception('Parse error: unexpected , in if statement starting at line: ' . $if['row'] . ' column: ' . $if['column'] . ' in: ' . $parse->data('priya.parse.read.url'));
                // we might do a logical and for this...
            }
            $count = 0;
            if($parameter[0] === false){
                if(isset($if['method']['elseif'][0])){
                    $if['method']['elseif'] = Core_if::create_elseif($if['method']['elseif'], $need_tag);
                    foreach($if['method']['elseif'] as $nr => $elseif){
                        $count++;
                        var_dump($count);
                        ob_flush();
                        $parameter = Token::set_execute($parse, $elseif['method']['parameter'][0], $token);
                        if(isset($parameter[1])){
                            throw new Exception('Parse error: unexpected , in if statement starting at line: ' . $elseif['row'] . ' column: ' . $elseif['column'] . ' in: ' . $parse->data('priya.parse.read.url'));
                            // we might do a logical and for this...
                        }
                        elseif(
                            isset($parameter[0]['is_executed']) &&
                            $parameter[0]['execute'] === true
                        ){
                            $execute = $parse->execute($elseif['method']['content'], true);
                            $if['execute'] = Token::string($execute);
                            $if['is_executed'] = true;
                            $if = Token::value_type($if, 'execute');
                            $token[$if['token']['nr']] = $if;
                            break;
                        }
                    }
                }
            }
            elseif($parameter[0] === true){
                $execute = $parse->execute($if['method']['if'], true);
                $if['execute'] = Token::string($execute);
                $if['is_executed'] = true;
                $if = Token::value_type($if, 'execute');
                $token[$if['token']['nr']] = $if;
            } else {
                throw new Exception('Parameter should have been executed....');
            }
        }
        return $token;
    }
}