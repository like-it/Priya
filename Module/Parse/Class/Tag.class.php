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
}