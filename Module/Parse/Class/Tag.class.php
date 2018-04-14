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
    const TYPE_BIN = 'bin';
    const TYPE_HEX = 'hex';
    const TYPE_OCT = 'oct';
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
    const TYPE_EXCLAMATION = 'exclamation';
    const TYPE_ASSIGN = 'assign';
    const TYPE_COLON = 'colon';
    const TYPE_SEMI_COLON = 'semi-colon';
    const TYPE_DOUBLE_COLON = 'double-colon';
    const TYPE_COMMA = 'comma';
    const TYPE_BRACKET = 'bracket';

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
        $next = null;
        $next_next = null;
        $next_next_next = null;
        $no_parse = false;
        $parse = false;
        $variable = false;
        $set = false;
        $int = false;
        $hex = false;
        $oct = false;
        $bin = false;
        $before = '';
        $skip = 0;
        $counter = 0;
        $set_depth = 0;
        $statement = array();
        $statement[$counter]['string'] = '';
        $count = count($tag['split']);
        foreach($tag['split'] as $nr => $char){
            if($skip > 0){
                $skip--;
                $previous_char = $char;
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
            if(isset($tag['split'][$nr + 1])){
                $next = $tag['split'][$nr + 1];
            } else {
                $next = null;
            }
            if(isset($tag['split'][$nr + 2])){
                $next_next = $tag['split'][$nr + 2];
            } else {
                $next_next = null;
            }
            if(isset($tag['split'][$nr + 3])){
                $next_next_next = $tag['split'][$nr + 3];
            } else {
                $next_next_next = null;
            }
            if(
                $parse === false &&
                $no_parse === false
            ){
                if(
                    $variable === false &&
                    $char == ','
                ){
                    if(!empty($statement[$counter]['string'])){
                        $counter++;
                    }
                    $statement[$counter]['string'] = $char;
                    $statement[$counter]['type'] = TAG::TYPE_COMMA;
                    $counter++;
                    continue;
                }
                elseif(
                    $variable === false &&
                    $char == ':' &&
                    $next != ':'
                ){
                    if(!empty($statement[$counter]['string'])){
                        $counter++;
                    }
                    $statement[$counter]['string'] = $char;
                    $statement[$counter]['type'] = TAG::TYPE_COLON;
                    $counter++;
                    continue;
                }
                elseif(
                    $variable === false &&
                    $char == ':' &&
                    $next == ':'
                ){
                    if(!empty($statement[$counter]['string'])){
                        $counter++;
                    }
                    $statement[$counter]['string'] = $char . $next;
                    $statement[$counter]['type'] = TAG::TYPE_DOUBLE_COLON;
                    $counter++;
                    $skip = 1;
                    continue;
                }
                elseif(
                    $variable === false &&
                    in_array(
                        $char,
                        array(
                            '[',
                            ']',
                            '{',
                            '}',
                        )
                    )
                ){
                    if(!empty($statement[$counter]['string'])){
                        $counter++;
                    }
                    $statement[$counter]['string'] = $char;
                    $statement[$counter]['type'] = TAG::TYPE_BRACKET;
                    $counter++;
                    continue;
                }
                elseif(
                    $variable === false &&
                    $char == '('
                ){
                    $set = true;
                    $set_depth++;

                    if(!empty($statement[$counter]['string'])){
                        $counter++;
                    }
                    $statement[$counter]['string'] = $char;
                    $statement[$counter]['type'] = TAG::TYPE_SET;
                    $statement[$counter]['set_depth'] = $set_depth;
                    $counter++;
                    continue;
                }
                elseif(
                    $variable === false &&
                    $set === true &&
                    $char == ')'
                ){
                    if(!empty($statement[$counter]['string'])){
                        $counter++;
                    }
                    $statement[$counter]['string'] = $char;
                    $statement[$counter]['type'] = TAG::TYPE_SET;
                    $statement[$counter]['set_depth'] = $set_depth;
                    $counter++;
                    $set_depth--;
                    if($set_depth == 0){
                        $set = false;
                    }
                }
                elseif(
                    $variable === false &&
                    $int === false &&
                    $hex === false &&
                    $oct === false &&
                    $bin === false &&
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
                    $counter++;
                    if(
                        in_array(
                            $next,
                            array(
                                'x',
                                'X'
                            )
                        )
                    ){
                        $hex = true;
                        $statement[$counter]['string'] = $char . $next;
                        $statement[$counter]['type'] = Tag::TYPE_HEX;
                        $skip = 1;
                        continue;
                    }
                    elseif(
                        in_array(
                            $next,
                            array(
                                'b',
                                'B'
                            )
                        )
                    ){
                        $bin = true;
                        $statement[$counter]['string'] = $char . $next;
                        $statement[$counter]['type'] = Tag::TYPE_BIN;
                        $skip = 1;
                        continue;
                    }
                    elseif(
                        $char == '0' &&
                        in_array(
                            $next,
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
                        $oct = true;
                        $statement[$counter]['string'] = $char . $next;
                        $statement[$counter]['type'] = Tag::TYPE_OCT;
                        $skip = 1;
                        continue;
                    } else {
                        $int = true;
                        $statement[$counter]['string'] = $char;
                        $statement[$counter]['type'] = Tag::TYPE_INT;
                    }
                    if(
                        !in_array(
                            $next,
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
                        $statement[$counter]['int'] = $statement[$counter]['string'] + 0;
                        $counter++;
                    }
                    continue;
                }
                elseif(
                    $variable === false &&
                    $int === true
                ){
                    if(!isset($statement[$counter])){
                        var_dump($statement);
                        var_dump($char);
                        var_dump($counter);
                        die;
                    }
                    $statement[$counter]['string'] .= $char;

                    if($char == '.'){
                        $statement[$counter]['type'] = Tag::TYPE_FLOAT;
                    }
                    if(
                        !in_array(
                            $next,
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
                        if($statement[$counter]['type'] == Tag::TYPE_FLOAT){
                            $statement[$counter]['float'] = $statement[$counter]['string'] + 0;
                        } else {
                            $statement[$counter]['int'] = $statement[$counter]['string'] + 0;
                        }
                        $counter++;
                    }
                    continue;
                }
                elseif(
                    $variable === false &&
                    $oct === true
                ){
                    $statement[$counter]['string'] .= $char;
                    if(
                        !in_array(
                            $next,
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
                        $oct = false;
                        $statement[$counter]['oct'] = $statement[$counter]['string']; //move to int
                        $counter++;
                    }
                    continue;
                }
                elseif(
                    $variable === false &&
                    $bin === true
                ){
                    $statement[$counter]['string'] .= $char;
                    if(
                        !in_array(
                            $next,
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
                        $bin = false;
                        $statement[$counter]['bin'] = $statement[$counter]['string']; //move to int
                        $counter++;
                    }
                    continue;
                }
                elseif(
                    $variable === false &&
                    $hex === true
                ){
                    $statement[$counter]['string'] .= $char;
                    if(
                        !in_array(
                            $next,
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
                                'a',
                                'A',
                                'b',
                                'B',
                                'c',
                                'C',
                                'd',
                                'D',
                                'e',
                                'E',
                                'f',
                                'F'
                            )
                        )
                    ){
                        $hex = false;
                        $statement[$counter]['hex'] = $statement[$counter]['string']; //move to int
                        $counter++;
                    }
                    continue;
                }
                elseif(
                    $char == '+' &&
                    $variable === false
                ){
                    if(!empty($statement[$counter]['string'])){
                        $counter++;
                        $statement[$counter]['string'] = $char;
                    } else {
                        $statement[$counter]['string'] = $char;
                    }
                    $statement[$counter]['type'] = Tag::TYPE_OPERATOR;
                    if($next == '+'){
                        $statement[$counter]['string'] .= $next;
                        $skip = 1;
                    }
                    elseif($next == '='){
                        $statement[$counter]['string'] .= $next;
                        $statement[$counter]['type'] = Tag::TYPE_ASSIGN;
                        $skip = 1;
                    }
                    $counter++;
                    $previous_char = $char;
                    continue;
                }
                elseif(
                    $char == '-'&&
                    $variable === false
                ){
                    if(!empty($statement[$counter]['string'])){
                        $counter++;
                        $statement[$counter]['string'] = $char;
                    } else {
                        $statement[$counter]['string'] = $char;
                    }
                    $statement[$counter]['type'] = Tag::TYPE_OPERATOR;
                    if($next == '-'){
                        $statement[$counter]['string'] .= $next;
                        $skip = 1;
                    }
                    elseif($next == '='){
                        $statement[$counter]['string'] .= $next;
                        $statement[$counter]['type'] = Tag::TYPE_ASSIGN;
                        $skip = 1;
                    }
                    $counter++;
                    $previous_char = $char;
                    continue;
                }
                elseif(
                    $char == '*' &&
                    $variable === false
                ){
                    if(!empty($statement[$counter]['string'])){
                        $counter++;
                        $statement[$counter]['string'] = $char;
                    } else {
                        $statement[$counter]['string'] = $char;
                    }
                    $statement[$counter]['type'] = Tag::TYPE_OPERATOR;
                    if($next == '*' && $next_next != '='){
                        $statement[$counter]['string'] .= $next;
                        $skip = 1;
                    }
                    elseif($next == '='){
                        $statement[$counter]['string'] .= $next;
                        $statement[$counter]['type'] = Tag::TYPE_ASSIGN;
                        $skip = 1;
                    }
                    elseif($next == '*' && $next_next == '='){
                        $statement[$counter]['string'] .= $next;
                        $statement[$counter]['type'] = Tag::TYPE_ASSIGN;
                        $skip = 2;
                    }
                    $counter++;
                    $previous_char = $char;
                    continue;
                }
                elseif(
                    $char == '/' &&
                    $variable === false
                ){
                    if(!empty($statement[$counter]['string'])){
                        $counter++;
                        $statement[$counter]['string'] = $char;
                    } else {
                        $statement[$counter]['string'] = $char;
                    }
                    $statement[$counter]['type'] = Tag::TYPE_OPERATOR;
                    if($next == '='){
                        $statement[$counter]['string'] .= $next;
                        $statement[$counter]['type'] = Tag::TYPE_ASSIGN;
                        $skip = 1;
                    }
                    $counter++;
                    $previous_char = $char;
                    continue;
                }
                elseif(
                    $char == '%' &&
                    $variable === false
                ){
                    if(!empty($statement[$counter]['string'])){
                        $counter++;
                        $statement[$counter]['string'] = $char;
                    } else {
                        $statement[$counter]['string'] = $char;
                    }
                    $statement[$counter]['type'] = Tag::TYPE_OPERATOR;
                    if($next == '='){
                        $statement[$counter]['string'] .= $next;
                        $statement[$counter]['type'] = Tag::TYPE_ASSIGN;
                        $skip = 1;
                    }
                    $counter++;
                    $previous_char = $char;
                    continue;
                }
                elseif(
                    $char == '=' &&
                    $variable === false
                ){
                    if(!empty($statement[$counter]['string'])){
                        $counter++;
                        $statement[$counter]['string'] = $char;
                    } else {
                        $statement[$counter]['string'] = $char;
                    }
                    $statement[$counter]['type'] = Tag::TYPE_ASSIGN;
                    if($next == '=' && $next_next == '='){
                        $statement[$counter]['string'] .= $next . $next_next;
                        $statement[$counter]['type'] = Tag::TYPE_OPERATOR;
                        $skip = 1;
                    }
                    elseif($next == '='){
                        $statement[$counter]['string'] .= $next;
                        $statement[$counter]['type'] = Tag::TYPE_OPERATOR;
                        $skip = 1;
                    }
                    $counter++;
                    $previous_char = $char;
                    continue;
                }
                elseif(
                    $char == '.' &&
                    $variable === false
                ){
                    if(!empty($statement[$counter]['string'])){
                        $counter++;
                        $statement[$counter]['string'] = $char;
                    } else {
                        $statement[$counter]['string'] = $char;
                    }
                    $statement[$counter]['type'] = Tag::TYPE_OPERATOR;
                    if($next == '='){
                        $statement[$counter]['string'] .= $next;
                        $statement[$counter]['type'] = Tag::TYPE_ASSIGN;
                        $skip = 1;
                    }
                    $counter++;
                    $previous_char = $char;
                    continue;
                }
                elseif(
                    $char == '!' &&
                    $variable === false
                ){
                    if(!empty($statement[$counter]['string'])){
                        $counter++;
                        $statement[$counter]['string'] = $char;
                    } else {
                        $statement[$counter]['string'] = $char;
                    }
                    $statement[$counter]['type'] = Tag::TYPE_EXCLAMATION;
                    if($next == '=' && $next_next == '='){
                        $statement[$counter]['string'] .= $next . $next_next;
                        $statement[$counter]['type'] = Tag::TYPE_OPERATOR;
                        $skip = 2;
                    }
                    elseif($next == '='){
                        $statement[$counter]['string'] .= $next;
                        $statement[$counter]['type'] = Tag::TYPE_OPERATOR;
                        $skip = 1;
                    }
                    $counter++;
                    $previous_char = $char;
                    continue;
                }
                elseif(
                    $char == '&' &&
                    $variable === false
                ){
                    if(!empty($statement[$counter]['string'])){
                        $counter++;
                        $statement[$counter]['string'] = $char;
                    } else {
                        $statement[$counter]['string'] = $char;
                    }
                    $statement[$counter]['type'] = Tag::TYPE_OPERATOR;
                    if($next == '&'){
                        $statement[$counter]['string'] .= $next;
                        $skip = 1;
                    }
                    $counter++;
                    $previous_char = $char;
                    continue;
                }
                elseif(
                    $char == '|' &&
                    $variable === false
                ){
                    if(!empty($statement[$counter]['string'])){
                        $counter++;
                        $statement[$counter]['string'] = $char;
                    } else {
                        $statement[$counter]['string'] = $char;
                    }
                    $statement[$counter]['type'] = Tag::TYPE_OPERATOR;
                    if($next == '|'){
                        $statement[$counter]['string'] .= $next;
                        $skip = 1;
                    }
                    $counter++;
                    $previous_char = $char;
                    continue;
                }
                elseif(
                    $char == '^' &&
                    $variable === false
                ){
                    if(!empty($statement[$counter]['string'])){
                        $counter++;
                        $statement[$counter]['string'] = $char;
                    } else {
                        $statement[$counter]['string'] = $char;
                    }
                    $statement[$counter]['type'] = Tag::TYPE_OPERATOR;
                    $counter++;
                    $previous_char = $char;
                    continue;
                }
                elseif(
                    $char == '~' &&
                    $variable === false
                ){
                    if(!empty($statement[$counter]['string'])){
                        $counter++;
                        $statement[$counter]['string'] = $char;
                    } else {
                        $statement[$counter]['string'] = $char;
                    }
                    $statement[$counter]['type'] = Tag::TYPE_OPERATOR;
                    $counter++;
                    $previous_char = $char;
                    continue;
                }
                elseif(
                    $char == '<' &&
                    $variable === false
                ){
                    if(!empty($statement[$counter]['string'])){
                        $counter++;
                        $statement[$counter]['string'] = $char;
                    } else {
                        $statement[$counter]['string'] = $char;
                    }
                    $statement[$counter]['type'] = Tag::TYPE_OPERATOR;
                    if($next == '<'){
                        $statement[$counter]['string'] .= $next;
                        $skip = 1;
                    }
                    elseif($next == '=' && $next_next == '>'){
                        $statement[$counter]['string'] .= $next . $next_next;
                        $skip = 2;
                    }
                    elseif($next == '='){
                        $statement[$counter]['string'] .= $next;
                        $skip = 1;
                    }
                    $counter++;
                    $previous_char = $char;
                    continue;
                }
                elseif(
                    $char == '>' &&
                    $variable === false
                ){
                    if(!empty($statement[$counter]['string'])){
                        $counter++;
                        $statement[$counter]['string'] = $char;
                    } else {
                        $statement[$counter]['string'] = $char;
                    }
                    $statement[$counter]['type'] = Tag::TYPE_OPERATOR;
                    if($next == '>'){
                        $statement[$counter]['string'] .= $next;
                        $skip = 1;
                    }
                    elseif($next == '='){
                        $statement[$counter]['string'] .= $next;
                        $skip = 1;
                    }
                    $counter++;
                    $previous_char = $char;
                    continue;
                }
                elseif(
                    in_array(
                        $previous_char,
                        array(
                            ' ',
                            '(',
                            ')'
                        )
                    ) &&
                    in_array(
                        $next_next,
                        array(
                            ' ',
                            '(',
                            ')'
                        )
                    )
                ){
                    if(
                        $char == 'o' &&
                        $next == 'r'  &&
                        $variable === false
                    ){
                        if(!empty($statement[$counter]['string'])){
                            $counter++;
                            $statement[$counter]['string'] = $char;
                        } else {
                            $statement[$counter]['string'] = $char;
                        }
                        $statement[$counter]['type'] = Tag::TYPE_OPERATOR;
                        $statement[$counter]['string'] .= $next;
                        $skip = 1;
                        $counter++;
                        $previous_char = $char;
                        continue;
                    }
                }
                elseif(
                    in_array(
                        $previous_char,
                        array(
                            ' ',
                            '(',
                            ')'
                        )
                    ) &&
                    in_array(
                        $next_next_next,
                        array(
                            ' ',
                            '(',
                            ')'
                        )
                    )
                ){
                    if(
                        (
                        $char == 'a' &&
                        $next == 'n' &&
                        $next_next == 'd' &&
                        $variable === false
                        ) ||
                        $char == 'x' &&
                        $next == 'o' &&
                        $next_next == 'r' &&
                        $variable === false
                    ){
                        if(!empty($statement[$counter]['string'])){
                            $counter++;
                            $statement[$counter]['string'] = $char;
                        } else {
                            $statement[$counter]['string'] = $char;
                        }
                        $statement[$counter]['type'] = Tag::TYPE_OPERATOR;
                        $statement[$counter]['string'] .= $next . $next_next;
                        $skip = 2;
                        $counter++;
                        $previous_char = $char;
                        continue;
                    }
                }
                elseif(
                    $char == '$' &&
                    $variable === false
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
                    $variable === true
                ){
                    $statement[$counter]['string'] .= $char;
                    if(
                        in_array(
                            $next,
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
                        $variable = false;
                        $counter++;
                    }
                    $previous_char = $char;
                    continue;
                } else {
                    if(!isset($statement[$counter]['string'])){
                        $statement[$counter]['string'] = $char;
                    } else {
                        $statement[$counter]['string'] .= $char;
                    }
                }
            }
        }
        var_dump($statement);
        die;
        return $tag;//110 linr nr
    }
}