<?php

namespace Priya\Module\Parse;

use Priya\Module\Core;
use Priya\Module\Parse;
use Priya\Module\Parse\Literal;

class Priya extends Core {
    const TAG = 'priya';
    const CLOSE = '/priya';
    const NEWLINE = "\n";
    const SPACE = ' ';
    const MIN = '-';
    const DELETE = 'delete';
    const MASK = Priya::SPACE . Tag::OPEN . Tag::CLOSE;

    const DATA_TAG = 'priya.module.parser.tag.priya';
    const DATA_LITERAL = 'priya.module.parser.priya.literal';

    public static function find($tag='', $string='', $parser=null){
        if($tag[Tag::TAG] == Tag::OPEN . Priya::TAG . TAG::CLOSE){
            $random = Parse::random();
            $parser->data(Priya::DATA_TAG, Tag::OPEN . Priya::TAG . Priya::MIN . $random . Tag::CLOSE);

            while(stristr($string, $parser->data(Priya::DATA_TAG))){
                $random = Parse::random();
                $parser->data(Priya::DATA_TAG, Tag::OPEN . Priya::TAG . Priya::MIN . $random .Tag::CLOSE);
            }
            $explode = explode(Tag::OPEN . Priya::TAG . TAG::CLOSE, $string, 2);
            $string = implode($parser->data(Priya::DATA_TAG), $explode);
            $parser->data(Priya::DATA_LITERAL, true);
            $parser->data('priya.module.parser.tag.line', $tag[Tag::LINE] - 1);
            $parser->data('priya.module.parser.tag.colum', $tag[Tag::COLUMN]);
        }
        elseif($tag[Tag::TAG] == Tag::OPEN . Priya::CLOSE . TAG::CLOSE){
            $priya = $parser->data(Priya::DATA_TAG);
            $explode = explode($priya, $string, 2);
            if(isset($explode[1])){
                $content = explode(Tag::OPEN . Priya::CLOSE . TAG::CLOSE, $explode[1], 2);
            }
            $code = Priya::tag($content[0], $parser);
            var_dump($code);

            $program = array();
            $mask = ' ' . Tag::OPEN . Tag::CLOSE;
            $count = count($code);
            $counter = 0;

            $line_nr = $parser->data('priya.module.parser.tag.line');

            $parser->data(Priya::DELETE, Priya::DATA_LITERAL);
            foreach($code as $nr => $line){
                $line = trim($line, $mask);
                if($parser->data('priya.debug4') === true){
                    $parser->data('priya.module.parser.tag.line', $line_nr + $counter);
                    $parser->data('priya.module.parser.tag.multiline', true);
                    $program[$nr] = Parse::token(Tag::OPEN . $line . Tag::CLOSE, $parser->data(), false, $parser);
                    var_dump($program[$nr]);
                    die;
                }
                if(!empty($line)){
                    $parser->data('priya.module.parser.tag.line', $line_nr + $counter);
                    $parser->data('priya.module.parser.tag.multiline', true);
                    $program[$nr] = Parse::token(Tag::OPEN . $line . Tag::CLOSE, $parser->data(), false, $parser);
                } else {
                    $program[$nr] = '';
                }
                $counter++;
            }

            $compile = $program;
            var_Dump($compile);
            die;
            $explode[1] = '';

//             var_dump($program);

            foreach($compile as $line){
                if(empty($line)){
                    continue;
                }
                elseif(is_array($line)){
                    continue;
                }
                elseif(is_object($line)){
                    continue;
                }
//                 var_dump($line);
                $explode[1] .= $line . Parse::NEWLINE;
            }
//             var_dump($explode[1]);
//             die;
            $explode[1] .= $content[1];
            $parser->data(Priya::DELETE, Priya::DATA_TAG);
            $string = implode('', $explode);
        }
        return $string;
    }

    public static function tag($string='', $parser=null){
        $previous_char = '';
        $next = null;
        $next_next = null;
        $parse = false;
        $no_parse = false;
        $variable = false;
        $skip = 0;
        $counter = 0;
        $set_depth = 0;
        $comment_depth = 0;
        $comment = '';
        $statement = array();
        $statement[$counter]['string'] = '';
        $split = str_split($string);
        $count = count($split);
        foreach($split as $nr => $char){
            if($skip > 0){
                $skip--;
                $previous_char = $char;
                continue;
            }
            if(isset($split[$nr + 1])){
                $next = $split[$nr + 1];
            } else {
                $next = null;
            }
            if(isset($split[$nr + 2])){
                $next_next = $split[$nr + 2];
            } else {
                $next_next = null;
            }
            if($comment_depth > 0){
                if(
                    $variable === false &&
                    $char == '*' &&
                    $next == '/'
                ){
                    $comment_depth--;
                    $comment_depth = 0; //no nested comments...
                    $previous_char = $char;
                    $comment .= '*/';
                    $counter++;
                    $statement[$counter]['string'] = $comment;
                    $statement[$counter]['type'] = Tag::TYPE_COMMENT;
                    $skip = 1;
                    $counter++;
                    $comment = '';
                    continue;
                }
                $previous_char = $char;
                $comment .= $char;
                continue;
            }
            if(
                $char == '"' &&
                $parse === true &&
                $no_parse == false &&
                $previous_char !== '\\'
            ){
                $statement[$counter]['string'] .= $char;
                $parse = false; //no counter++
                if($nr < $count){
                    $counter++;
                }
                $previous_char = $char;
                continue;
            }
            elseif(
                $char == '"' &&
                $parse === false &&
                $no_parse === false &&
                $previous_char !== '\\'
            ){
                if(!empty($statement[$counter]['string'])){
                    $counter++;
                    $statement[$counter]['string'] = '';
                }
                if(!isset($statement[$counter])){
                    $statement[$counter]['string'] = '';
                }
                $statement[$counter]['string'] .= $char;
                $statement[$counter]['type'] = Tag::TYPE_PARSER;
                $parse = true;
                $variable = false;
                $previous_char = $char;
                continue;
            }
            elseif(
                $char == '\'' &&
                $no_parse === true &&
                $parse === false &&
                $previous_char !== '\\'
            ){
                $statement[$counter]['string'] .= $char;
                $no_parse = false; //no counter++
                if($nr < $count){
                    $counter++;
                }
                $previous_char = $char;
                continue;
            }
            elseif(
                $char == '\'' &&
                $no_parse === false &&
                $parse === false &&
                $previous_char !== '\\'
            ){
                if(!empty($statement[$counter]['string'])){
                    $counter++;
                    $statement[$counter]['string'] = '';
                }
                if(!isset($statement[$counter])){
                    $statement[$counter]['string'] = '';
                }
                $statement[$counter]['string'] .= $char;
                $statement[$counter]['type'] = Tag::TYPE_STRING;
                $no_parse = true;
                $variable = false;
                $previous_char = $char;
                continue;
            }
            elseif(
                $parse === true &&
                $no_parse === false &&
                $variable === false

            ){
                $statement[$counter]['string'] .= $char;
                $previous_char = $char;
                continue;
            }
            elseif(
                $no_parse === true &&
                $parse === false &&
                $variable === false
            ){
                $statement[$counter]['string'] .= $char;
                $previous_char = $char;
                continue;
            }
            if(
                $parse === false &&
                $no_parse === false
            ){
                if(
                    $variable === false &&
                    $char == ';'
                ){
                    if(!empty($statement[$counter]['string'])){
                        $counter++;
                    }
                    $statement[$counter]['string'] = $char;
                    $statement[$counter]['type'] = TAG::TYPE_SEMI_COLON;
                    $counter++;
                    continue;
                }
                elseif(
                    $variable === false &&
                    $char == '/' &&
                    $next == '*'
                ){
                    $comment_depth++;
                    $comment .= $char;
                    continue;
                }
                else {
                    if(!isset($statement[$counter]['string'])){
                        $statement[$counter]['string'] = $char;
                    } else {
                        $statement[$counter]['string'] .= $char;
                    }
                }
            } else {
                if(!isset($statement[$counter]['string'])){
                    $statement[$counter]['string'] = $char;
                } else {
                    $statement[$counter]['string'] .= $char;
                }
            }
        }
        $result = array();
        $code = '';

//         var_dump($statement);

        foreach($statement as $counter => $part){
            if(isset($part['type']) && $part['type'] == Tag::TYPE_COMMENT){
//                 var_dump($part);
                continue; //wtf... yup working again...
            }
            if(isset($part['type']) && $part['type'] == TAG::TYPE_SEMI_COLON){
                $result[] = '{' . trim($code) . '}';
                $code = '';
            } else {
                $code .= $part['string'];
            }
        }
        if(!empty($code)){
            $result[] = '{' . trim($code) . '}';
        }
//         var_dump($result);
        return $result;
    }

    public static function exectute($tag=array(), $attribute='', $parser=null){
        return $tag;
    }
}