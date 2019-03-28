<?php

namespace Priya\Module\Parse;

use Exception;

class Core_if extends Core {
    const IF = 'if';
    const ELSE_IF = 'elseif';
    const ELSE = 'else';

    public static function select($if=[], $token=[], $need_tag_close=true){
        $elseif_nr = -1;
        $depth = 0;
        $previous = null;
        $previous_previous = null;
        $is_select = false;
        $is_parameter = false;
        $is_whitespace = false;
        $is_complete = false;
        $is_elseif = false;
        $is_else = false;
        $is_trimmed = false;
        $content = [
            'if' => null,
            'elseif' => [],
            'else' => null
        ];
        foreach($token as $nr => $record){
            if($nr < $if['token']['nr']){
                $previous_previous = $previous;
                $previous = $nr;
                continue;
            }
            if($nr == $if['token']['nr']){
                $is_parameter = true;
                $previous_previous = $previous;
                $previous = $nr;
                continue;
            }
            if($is_parameter === true){
                if($record['type'] == Token::TYPE_PARENTHESE_OPEN){
                    $depth = $record['depth'];
                }
                elseif(
                    $record['type'] == Token::TYPE_PARENTHESE_CLOSE &&
                    $depth == $record['depth']
                ){
                    $is_parameter = false;
                    $depth = 0;
                    $previous_previous = $previous;
                    $previous = $nr;
                    continue;
                }
            }
            if($need_tag_close === true){
                if(
                    $record['type'] == Token::TYPE_CURLY_CLOSE &&
                    $is_parameter === false
                ){
                    $is_select = true;
                    $previous_previous = $previous;
                    $previous = $nr;
                    continue;
                }
            }
            elseif($is_parameter === false) {
                $is_select = true;
            }
            if($is_select === true){
                $record['if_depth'] = $depth;
                if(
                    $is_whitespace === false &&
                    $is_elseif === false &&
                    $record['type'] == Token::TYPE_WHITESPACE
                ){
                    $is_whitespace = true;
                    $whitespace = $record;
                    $explode = explode("\n", $whitespace['value'], 2);
                    if(trim($explode[0]) == ''){
                        $whitespace['value'] = $explode[1];
                    }
                    $content['if'][] = $whitespace;
                    $previous_previous = $previous;
                    $previous = $nr;
                    continue;
                } else {
                    $is_whitespace = 'ignore';
                }
                if(
                    $record['type'] == Token::TYPE_METHOD &&
                    $record['method']['name'] == Core_if::IF
                ){
                    $depth++;
                    $record['if_depth'] = $depth;
                    if($is_elseif === true){
                        $content['elseif'][$elseif_nr][] = $record;
                    }
                    elseif($is_else === true){
                        $content['else'][] = $record;
                    } else {
                        $content['if'][] = $record;
                    }
                    continue;
                }
                elseif(
                    $record['type'] == Token::TYPE_METHOD &&
                    $record['method']['name'] == Core_if::ELSE_IF &&
                    $record['if_depth'] == 0
                ){
                    $is_elseif = true;
                    $elseif_nr++;
                    if($need_tag_close === true){
                        if($token[$previous]['type'] == Token::TYPE_CURLY_OPEN){
                            $key = 'if';
                            if($elseif_nr > 0){
                                $key = 'elseif';
                                array_pop($content[$key][$elseif_nr - 1]);
                            } else {
                                array_pop($content[$key]);
                            }
                        }
                        elseif(
                            $token[$previous]['type'] == Token::TYPE_WHITESPACE &&
                            $token[$previous_previous]['type'] == Token::TYPE_CURLY_OPEN
                        ){
                            $key = 'if';
                            if($elseif_nr > 0){
                                $key = 'elseif';
                                array_pop($content[$key][$elseif_nr - 1]);
                                array_pop($content[$key][$elseif_nr - 1]);
                            } else {
                                array_pop($content[$key]);
                                array_pop($content[$key]);
                            }
                        }
                    }
                    /*
                    if($is_elseif === true){
                        $content['elseif'][$elseif_nr][] = $record;
                    }
                    elseif($is_else === true){
                        $content['else'][] = $record;
                    } else {
                        $content['if'][] = $record;
                    }
                    continue;
                    */
                }
                elseif(
                    $record['type'] == Token::TYPE_STRING &&
                    $record['value'] == Core_if::ELSE &&
                    $record['if_depth'] == 0
                ){
                    $is_else = true;
                    $is_elseif = false;
                    if($need_tag_close === true){
                        if($token[$previous]['type'] == Token::TYPE_CURLY_OPEN){
                            $key = 'if';
                            if($elseif_nr > 0){
                                $key = 'elseif';
                                array_pop($content[$key][$elseif_nr - 1]);
                            } else {
                                array_pop($content[$key]);
                            }
                        }
                        elseif(
                            $token[$previous]['type'] == Token::TYPE_WHITESPACE &&
                            $token[$previous_previous]['type'] == Token::TYPE_CURLY_OPEN
                        ){
                            $key = 'if';
                            if($elseif_nr > 0){
                                $key = 'elseif';
                                array_pop($content[$key][$elseif_nr - 1]);
                                array_pop($content[$key][$elseif_nr - 1]);
                            } else {
                                array_pop($content[$key]);
                                array_pop($content[$key]);
                            }
                        }
                        $is_trimmed = true;
                    }
                }
                elseif(
                    $record['type'] == Token::TYPE_TAG_CLOSE &&
                    $record['tag']['name'] == '/' . Core_if::IF
                ){
                    if($record['if_depth'] == 0){
                        $is_complete = true;
                        if($need_tag_close === true){
                            if($token[$previous]['type'] == Token::TYPE_CURLY_OPEN){
                                $key = 'else';
                                array_pop($content[$key]);
                            }
                            elseif(
                                $token[$previous]['type'] == Token::TYPE_WHITESPACE &&
                                $token[$previous_previous]['type'] == Token::TYPE_CURLY_OPEN
                            ){
                                $key = 'else';
                                array_pop($content[$key]);
                                array_pop($content[$key]);
                            }
                        }
                        break;
                    }
                    /*
                    if($is_elseif === true){
                        $content['elseif'][$elseif_nr][] = $record;
                    }
                    elseif($is_else === true){
                        $content['else'][] = $record;
                    } else {
                        $content['if'][] = $record;
                    }
                    */
                    $depth--;
                }
                if($record['type'] == Token::TYPE_METHOD){
                    var_dump($record);
                    var_dump($elseif_nr);
                    var_dump($is_elseif);
                    var_dump($is_else);
                }
                if($is_elseif === true){
                    $content['elseif'][$elseif_nr][] = $record;
                }
                elseif($is_else === true){
                    $content['else'][] = $record;
                } else {
                    $content['if'][] = $record;
                }
            }
            if(
                $need_tag_close === true &&
                $is_trimmed === false
            ){
                if($token[$previous]['type'] == Token::TYPE_CURLY_OPEN){
                    $key = 'if';
                    if($elseif_nr >= 0){
                        $key = 'elseif';
                        array_pop($content[$key][$elseif_nr]);
                    } else {
                        array_pop($content[$key]);
                    }
                }
                elseif(
                    $token[$previous]['type'] == Token::TYPE_WHITESPACE &&
                    $token[$previous_previous]['type'] == Token::TYPE_CURLY_OPEN
                ){
                    $key = 'if';
                    if($elseif_nr >= 0){
                        $key = 'elseif';
                        array_pop($content[$key][$elseif_nr]);
                        array_pop($content[$key][$elseif_nr]);
                    } else {
                        array_pop($content[$key]);
                        array_pop($content[$key]);
                    }
                }
            }
            $previous_previous = $previous;
            $previous = $nr;
        }
        var_Dump($content['if']);
        die;
        if($is_complete === true){
            var_dump($content);
            die;
        } else {
            throw new Exception('Could not find close tag: /if');
        }
    }


}