<?php

namespace Priya\Module\Parse;

use Priya\Module\Core;
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

    const OPEN = '{';
    const CLOSE = '}';
    const MIN = '-';
    const NEWLINE = "\n";

    const PATTERN = '/\\' . Tag::OPEN . '.*\\' . Tag::CLOSE . '/';

    public static function find($input=null, $parser=null){
        $tagged = array();
        if(!is_string($input)){
            throw new Exception('Tag::find:Input should be string...');
            return $tagged;
        }
        $pattern = '/\\' . Tag::OPEN . '.*\\' . Tag::CLOSE . '/';
        $page = $input;
        $counter = 0;
        preg_match_all(Tag::PATTERN, $input, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
        if(!empty($matches)){
            foreach ($matches as $occurence => $set){
                foreach ($set as $nr => $record){
                    $explode = explode($record[0], $page, 2);
                    $line = strlen($explode[0]) - strlen(str_replace(Tag::NEWLINE, '', $explode[0])) + 1;
                    if(isset($explode[1])){
                        $page = $explode[0] . str_repeat(' ', strlen($record[0])) . $explode[1];
                    } else {
                        $page = $explode[0] . str_repeat(' ', strlen($record[0]));
                    }
                    $explode = explode(Tag::NEWLINE, strrev($explode[0]), 2);
                    $column = strlen($explode[0]) +1;
                    $node = array();
                    $node[Tag::TAG] = $record[0];
                    $node[Tag::LINE] = $line;
                    $node[Tag::COLUMN] = $column;
                    $tagged[] = $node;
                    $counter++;
                }
            }
        }
        $parser->data('parser.document.tag.counter', $counter);
        $parser->data('parser.document.tag.nodelist', $tagged);
        return $tagged;
    }
}