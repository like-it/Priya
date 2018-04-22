<?php

namespace Priya\Module\Parse;

use Priya\Module\Core;
use Priya\Module\Parse;
use Exception;

class Tag extends Core {
    const ATTRIBUTE = 'attribute';
    const LINE = 'line';
    const COLUMN = 'column';
    const EXECUTE = 'execute';
    const PARAMETER = 'parameter';
    const METHOD = 'method';
    const STRING = 'string';
    const TAG = 'tag';
    const CAST = 'cast';
    const EXCLAMATION = 'exclamation';
    const NAME = 'name';
    const ASSIGN = 'assign';
    const VALUE = 'value';
    const RUN = 'function';
    const STATEMENT = 'statement';

    const OPEN = '{';
    const CLOSE = '}';
    const SPACE = ' ';
    const NEWLINE = "\n";
    const EMPTY = '';

    const TYPE_NULL = 'null';
    const TYPE_INT = 'int';
    const TYPE_FLOAT = 'float';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_ARRAY = 'array';
    const TYPE_OBJECT = 'object';
    const TYPE_PARSER = 'parser';
    const TYPE_STRING = 'string';
    const TYPE_METHOD = 'method';
    const TYPE_OPERATOR = 'operator';
    const TYPE_VARIABLE = 'variable';
    const TYPE_UNSURE = 'unsure';
    const TYPE_SET = 'set';

    const PATTERN = '/\\' . Tag::OPEN . '.*\\' . Tag::CLOSE . '/';
    const FLAG = PREG_SET_ORDER | PREG_OFFSET_CAPTURE;

    //change to priya.parser.document.tag.counter /nodelist
    const COUNTER = 'parser.document.tag.counter';
    const NODELIST = 'parser.document.tag.nodelist';

    const EXCEPTION_TYPE = 'Tag::find:Input should be a string...';



    public static function find($input=null, $parser=null){
        $tagged = array();
        if(!is_string($input)){
            throw new Exception(Tag::EXCEPTION_TYPE);
            return $tagged;
        }
        $pattern = '/\\' . Tag::OPEN . '.*\\' . Tag::CLOSE . '/';
        $page = $input;
        $counter = 0;
        if($parser->data('priya.debug') === true){
//             var_dump($input);
//             die;
        }
        preg_match_all(Tag::PATTERN, $input, $matches, Tag::FLAG);
        if(!empty($matches)){
            foreach ($matches as $occurence => $set){
                foreach ($set as $nr => $record){
                    $explode = explode($record[0], $page, 2);
                    $line = strlen($explode[0]) - strlen(str_replace(Tag::NEWLINE, Tag::EMPTY, $explode[0])) + 1;
                    if(isset($explode[1])){
                        $page = $explode[0] . str_repeat(Tag::SPACE, strlen($record[0])) . $explode[1];
                    } else {
                        $page = $explode[0] . str_repeat(Tag::SPACE, strlen($record[0]));
                    }
                    $explode = explode(Tag::NEWLINE, strrev($explode[0]), 2);
                    $column = strlen($explode[0]) +1;

                    if($parser->data('priya.module.parser.tag.line')){
                        $line += $parser->data('priya.module.parser.tag.line');
                    }
                    if($parser->data('priya.module.parser.tag.column')){
                        $column += $parser->data('priya.module.parser.tag.column');
                    }

                    $node = array();
                    $node[Tag::TAG] = $record[0];
                    $node[Tag::LINE] = $line;
                    $node[Tag::COLUMN] = $column;
                    $tagged[] = $node;
                    $counter++;
                }
            }
        }
        $parser->data(Tag::COUNTER, $counter);
        $parser->data(Tag::NODELIST, $tagged);
        return $tagged;
    }

    public static function token($tag=array(), $tags=array(), $string='', $parser=null){
        $tag['find'] = $tags;
        $tag['split'] = str_split($tag['tag']);
        $previous_char = '';
        $next = '';
        $next_next = '';
        $no_parse = false;
        $parse = false;
        $variable = false;
        $set = false;
        $int = false;
        $before = '';
        $skip = 0;
        $counter = 0;
        $set_depth = 0;
        $statement = array();
        $statement[$counter]['string'] = '';
        $count = count($tag['split']);
        foreach($tag['split'] as $nr => $char){
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
                $statement[$counter]['string'] .= $char;
                $statement[$counter]['type'] = Tag::TYPE_PARSER;
                $parse = true;
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
                $statement[$counter]['string'] .= $char;
                $statement[$counter]['type'] = Tag::TYPE_STRING;
                $no_parse = true;
                $previous_char = $char;
                continue;
            }
            elseif($parse === true && $no_parse === false){
                $statement[$counter]['string'] .= $char;
                $previous_char = $char;
                continue;
            }
            elseif($no_parse === true && $parse === false){
                $statement[$counter]['string'] .= $char;
                $previous_char = $char;
                continue;
            }
            elseif(
                $parse === false &&
                $no_parse === false &&
                $variable === false &&
                $char == '$'
            ){
                if(!empty($statement[$counter]['string'])){
                    $counter++;
                    $statement[$counter]['string'] = $char;
                } else {
                    $statement[$counter]['string'] = $char;
                }
                $statement[$counter]['type'] = Tag::TYPE_VARIABLE;
                $variable = true;
                $previous_char = $char;
                continue;
            }
            elseif(
                $parse === false &&
                $no_parse === false &&
                $variable === true
            ){
                if(
                    !in_array(
                        $char,
                        array(
                            ' ',
                            '=',
                            '+',
                            '-',
                            '/',
                            '*',
                            '^',
                            '%',
                            '&',
                            '|',
                            '~',
                        )
                    )
                ){
                    $statement[$counter]['string'] .= $char;
                    $previous_char = $char;
                    continue;
                } else {
                    $variable = false;
                    $counter++;
                    $statement[$counter]['string'] = '';
                    if($char != ' '){
//                         $statement[$counter]['type'] = Tag::TYPE_OPERATOR;
                    }
                }
            }
            /*
            elseif(
                $no_parse === false &&
                $parse === false &&
                $variable === false &&
                is_numeric($char) &&
                $int === false
            ){
                if($statement[$counter]['type'] != Tag::TYPE_INT){
                    $int = true;
                    $counter++;
                    $statement[$counter]['type'] = Tag::TYPE_INT;
                    $statement[$counter]['int'] = $char;
                } else {
                    $statement[$counter]['int'] .= $char;
                }
            }
            elseif(
                $no_parse === false &&
                $parse === false &&
                $variable === false &&
                $int === true
            ){
                if($int === true && isset($statement[$counter]['int'])){
                    $statement[$counter]['int'] .= $char;
                }
                elseif($int === true){
                    var_dump($char);
                    var_Dump($counter);
                    var_dump($statement);
                    var_dump($string);
                    die;
                }
            }
            */
            if($skip > 0){
                $skip--;
                $previous_char = $char;
                continue;
            }
            /*
            elseif(
                in_array(
                    $char,
                    array(
                        '+',
                        '-',
                        '/',
                        '*',
                        '&',
                        '|',
                        '=',
                        '~',
                        '<',
                        '>',
                        'a',
                        'o',
                        'x',
                        '!',
                        '^',
                        '%',
                        '.',
                    )
                )
            ){
                if(isset($set[$nr + 1])){
                    $next = $set[$nr + 1];
                }
                if(isset($set[$nr + 2])){
                    $next_next = $set[$nr + 2];
                }
                if(
                    $no_parse === false &&
                    $parse === false &&
                    $variable === false &&
                    $char == 'a' &&
                    $next =='n' &&
                    $next_next == 'd'
                ){
                    $counter++;
                    $statement[$counter]['string'] = $char;
//                     $statement[$counter]['type'] = Tag::TYPE_OPERATOR;
                } else {
                    if(
                        $no_parse === false &&
                        $parse === false &&
                        $variable === false &&
                        $char == 'o' &&
                        $next =='r'
                    ){
                        $counter++;
                        $statement[$counter]['string'] = $char;
//                         $statement[$counter]['type'] = Tag::TYPE_OPERATOR;
                    } else {
                        if(
                            $no_parse === false &&
                            $parse === false &&
                            $variable === false &&
                            $char == 'x' &&
                            $next =='o' &&
                            $next_next == 'r'
                        ){
                            $counter++;
                            $statement[$counter]['string'] = $char;
//                             $statement[$counter]['type'] = Tag::TYPE_OPERATOR;
                        } else {
                            //add single operators
                            if(in_array(
                                $char,
                                array(
                                    '+',
                                    '-',
                                    '/',
                                    '*',
                                    '&',
                                    '|',
                                    '=',
                                    '~',
                                    '<',
                                    '>',
                                    '!',
                                    '^',
                                    '%',
                                    '.',
                                )
                            ) &&
                                $no_parse === false &&
                                $parse === false &&
                                $variable === false
                            ){
                                if(
                                    in_array(
                                        $char,
                                        array(
                                            '+',
                                            '-',
                                            '/',
                                            '*',
                                            '&',
                                            '|',
                                            '=',
                                            '~',
                                            '<',
                                            '>',
                                            '!',
                                            '^',
                                            '%',
                                            '.',
                                        )
                                    )
                                ){
                                    $statement[$counter]['type'] = Tag::TYPE_OPERATOR;
                                    $statement[$counter]['string'] .= $char;
                                    $statement[$counter]['operator'] = trim($statement[$counter]['string']);
                                    $counter++;
                                    $statement[$counter]['string'] = '';
                                    $statement[$counter]['type'] = Tag::TYPE_UNSURE;
                                } else {
                                    unset($statement[$counter]['operator']);
                                    $statement[$counter]['type'] = Tag::TYPE_UNSURE;
                                }
                            } else {
                                $statement[$counter]['string'] .= $char;
                            }
                        }
                    }
                }
                if(
                    (
                        $char == '*' &&
                        $next == '*'
                    ) ||
                    (
                        $char == '<' &&
                        $next == '='
                    ) ||
                    (
                        $char == '&' &&
                        $next == '&'
                    ) ||
                    (
                        $char == '|' &&
                        $next == '|'
                    ) ||
                    (
                        $char == '>' &&
                        $next == '='
                    ) ||
                    (
                        $char == '<' &&
                        $next == '>'
                    ) ||
                    (
                        $char == '<' &&
                        $next == '<'
                    ) ||
                    (
                        $char == '>' &&
                        $next == '>'
                    ) ||
                    (
                        $char == 'o' &&
                        $next == 'r'
                    ) ||
                    (
                           $char == '=' &&
                        $next == '=' &&
                        $next_next != '='
                    ) ||
                    (
                        $char == '!' &&
                        $next == '=' &&
                        $next_next != '='
                    )
                ){
                    $statement[$counter]['string'] .= $next;
                    $statement[$counter]['type'] = Tag::TYPE_OPERATOR;
                    $statement[$counter]['operator'] = trim($statement[$counter]['string']);
                    $skip = 1;
                    $counter++;
                    $statement[$counter]['string'] = '';
                }
                elseif(
                    (
                        $char == '=' &&
                        $next == '=' &&
                        $next_next == '='
                       ) ||
                    (
                        $char == '!' &&
                        $next == '=' &&
                        $next_next == '='
                    ) ||
                    (
                        $char == 'a' &&
                        $next == 'n' &&
                        $next_next == 'd'
                    ) ||
                    (
                        $char == 'x' &&
                        $next == 'o' &&
                        $next_next == 'r'
                    )
                ){
                    $statement[$counter]['string'] .= $next . $next_next;
                    $statement[$counter]['type'] = Tag::TYPE_OPERATOR;
                    $statement[$counter]['operator'] = trim($statement[$counter]['string']);
                    if(
                        in_array(
                            $statement[$counter]['operator'],
                            array(
                                '.',
                                '+',
                                '-',
                                '/',
                                '*',
                                '%',
                                '^',
                                '|',
                                '&',
                                '~',
                                '**',
                                '<',
                                '>',
                                '<<',
                                '>>',
                                '<=',
                                '>=',
                                '==',
                                '===',
                                '=',
                                '!=',
                                '!==',
                                'and',
                                'or',
                                'xor',
                            )
                        )){

                    }
                    $skip = 2;
                    $counter++;
                    $statement[$counter]['string'] = '';
                }
            }
            elseif(
                $no_parse === false &&
                $parse === false &&
                $variable === false &&
                $char == '('
            ){
                if(
                    $statement[$counter]['type'] == Tag::TYPE_OPERATOR &&
                    in_array(
                        $statement[$counter]['operator'],
                        array(
                            '.',
                            '+',
                            '-',
                            '/',
                            '*',
                            '%',
                            '^',
                            '|',
                            '&',
                            '~',
                            '**'.
                            '<',
                            '>',
                            '<<',
                            '>>',
                            '<=',
                            '>=',
                            '==',
                            '===',
                            '=',
                            '!=',
                            '!==',
                            'and',
                            'or',
                            'xor',
                        )
                    )
                ){
                    $counter++;
                    $statement[$counter]['string'] = $char;
                    $statement[$counter]['type'] = Tag::TYPE_SET;
                    $statement[$counter]['set_depth'] = $set_depth;
                    $counter++;
                    $statement[$counter]['string'] = '';
                    $set_depth++;
                    $set = true;
                }


                if(isset($statement[$counter]['string']) && trim($statement[$counter]['string']) != ''){
                    $statement[$counter]['string'] .= $char;
                    var_dump($statement[$counter]['string']);
                    $statement[$counter]['type'] = Tag::TYPE_METHOD;
                    if(substr($statement[$counter]['string'], 0, 1) == '{'){
                        $statement[$counter]['method'] = substr($statement[$counter]['string'], 1);
                    } else {
                        var_dump($statement);
                        die;
                    }
                    $statement[$counter]['method'] = substr($statement[$counter]['method'], 0, -1);
                    unset($statement[$counter]['int']);
                    unset($statement[$counter]['operator']); //might need to add more...
                    $counter++;
                }
                elseif(isset($statement[$counter]['string']) && trim($statement[$counter]['string']) == ''){

                }

            }
            elseif(
                $no_parse === false &&
                $parse === false &&
                $variable === false &&
                $set === true &&
                $char == ')'
            ){
                $counter++;
                $statement[$counter]['string'] = $char;
                $statement[$counter]['type'] = Tag::TYPE_SET;
                $statement[$counter]['set_depth'] = $set_depth;
                $counter++;
                $statement[$counter]['string'] = '';
                $set_depth--;
                //current type should be or method or parameter
            }
            */
            elseif(
                $no_parse === false &&
                $parse === false &&
                $variable === false &&
                $int === false &&
                in_array(
                    $char,
                    array(
                        '0',
                        '1',
                        '2',
                        '3',
                        '4',
                        '5',
                        '6',
                        '7',
                        '8',
                        '9',
                    )
                )
            ){
                $int = true;
                $counter++;
                $statement[$counter]['string'] = $char;
                $statement[$counter]['type'] = Tag::TYPE_INT;
                $statement[$counter]['int'] = $statement[$counter]['string'] + 0;
            }
            elseif(
                $no_parse === false &&
                $parse === false &&
                $variable === false &&
                $int === true &&
                in_array(
                    $char,
                    array(
                        '0',
                        '1',
                        '2',
                        '3',
                        '4',
                        '5',
                        '6',
                        '7',
                        '8',
                        '9',
                        '.',
                    )
                )
            ){
                $statement[$counter]['string'] .= $char;
                if($char == '.'){
                    $statement[$counter]['type'] = Tag::TYPE_FLOAT;
                    $statement[$counter]['float'] = $statement[$counter]['string'] + 0;
                    unset($statement[$counter]['int']);
                }
                elseif($statement[$counter]['type'] == Tag::TYPE_FLOAT){
                    $statement[$counter]['float'] = $statement[$counter]['string'] + 0;
                }
                else {
                    $statement[$counter]['int'] = $statement[$counter]['string'] + 0;
                }

            }
            elseif(
                $no_parse === false &&
                $parse === false &&
                $variable === false &&
                $int === true &&
                !in_array(
                    $char,
                    array(
                        '0',
                        '1',
                        '2',
                        '3',
                        '4',
                        '5',
                        '6',
                        '7',
                        '8',
                        '9',
                        '.',
                    )
                )
            ){
                $int = false;
                $counter++;
                if(!isset($statement[$counter]['string'])){
                    $statement[$counter]['string'] = '';
                }
                $statement[$counter]['string'] .= $char;

                //                     $statement[$counter]['type'] = Tag::TYPE_UNSURE;
                //move above nr for not having to double it
                /*
                if(
                    $no_parse === false &&
                    $parse === false &&
                    $variable === false &&
                    $char == '('
                ){
                    $set = true;
                    $set_depth++;
                    $statement[$counter]['string'] = $char;
                    $statement[$counter]['type'] = TAG::TYPE_SET;
                    $statement[$counter]['set_depth'] = $set_depth;
                    $counter++;
                }
                elseif(
                    $no_parse === false &&
                    $parse === false &&
                    $variable === false &&
                    $set === true &&
                    $char == ')'
                ){
                    $statement[$counter]['string'] = $char;
                    $statement[$counter]['type'] = TAG::TYPE_SET;
                    $statement[$counter]['set_depth'] = $set_depth;
                    $counter++;
                    $set_depth--;
                    if($set_depth == 0){
                        $set = false;
                    }
                }
                else {
                    if(!isset($statement[$counter]['string'])){
                        $statement[$counter]['string'] = '';
                    }
                    $statement[$counter]['string'] .= $char;
//                     $statement[$counter]['type'] = Tag::TYPE_UNSURE;
                }
            }
            elseif(
                $no_parse === false &&
                $parse === false &&
                $variable === false &&
                $char == '('
            ){
                $set = true;
                $set_depth++;
                $counter++;
                $statement[$counter]['string'] = $char;
                $statement[$counter]['type'] = TAG::TYPE_SET;
                $statement[$counter]['set_depth'] = $set_depth;
                $counter++;
            }
            elseif(
                $no_parse === false &&
                $parse === false &&
                $variable === false &&
                $set === true &&
                $char == ')'
            ){
                $counter++;
                $statement[$counter]['string'] = $char;
                $statement[$counter]['type'] = TAG::TYPE_SET;
                $statement[$counter]['set_depth'] = $set_depth;
                $counter++;
                $set_depth--;
                if($set_depth == 0){
                    $set = false;
                }
            }
            else {
                $int = false;
                if(!isset($statement[$counter]['string'])){
                    $statement[$counter]['string'] = '';
                }

                $statement[$counter]['string'] .= $char;
//                 $statement[$counter]['type'] = Tag::TYPE_UNSURE;
            }
            $previous_char = $char;
        }
        var_dump($statement);
        die;
        return $tag;
    }
}