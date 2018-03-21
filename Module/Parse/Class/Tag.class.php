<?php

namespace Priya\Module\Parse;

use Priya\Module\Core;
use Exception;

class Tag extends Core {

    public static function find($input=null, $parser=null){
        $tagged = array();
        if(!is_string($input)){
            throw new Exception('Tag::find:Input should be string...');
            return $tagged;
        }
        $pattern = '/\{.*\}/';
        $page = $input;
        $counter = 0;
        preg_match_all($pattern, $input, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
        if(!empty($matches)){
            foreach ($matches as $occurence => $set){
                foreach ($set as $nr => $record){
                    $explode = explode($record[0], $page, 2);
                    $row = strlen($explode[0]) - strlen(str_replace("\n", '', $explode[0])) + 1;
                    if(isset($explode[1])){
                        $page = $explode[0] . str_repeat(' ', strlen($record[0])) . $explode[1];
                    } else {
                        $page = $explode[0] . str_repeat(' ', strlen($record[0]));
                    }
                    $explode = explode("\n", strrev($explode[0]), 2);
                    $col = strlen($explode[0]) +1;
                    $node = array();
                    $node['tag'] = $record[0];

                    //below should not be neccesary, otherways the '"}}"' get replaced too
                    /*
                    $node['tag'] = str_replace(
                        array('}}'),
                        array('}'),
                        $record[0]
                    );
                    */
                    $node['line'] = $row;
                    $node['column'] = $col;
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