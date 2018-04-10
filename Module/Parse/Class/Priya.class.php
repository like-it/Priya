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
            $part = explode(Priya::NEWLINE, $content[0]);
            $program = array();
            $mask = ' ' . Tag::OPEN . Tag::CLOSE;
            $count = count($part);
            $counter = 0;

            $line_nr = $parser->data('priya.module.parser.tag.line');

            $parser->data(Priya::DELETE, Priya::DATA_LITERAL);
            foreach($part as $nr => $line){
                //add split on ; or space (not between quotes nor parameter) for single line...
                $line = trim($line, $mask);
                if(!empty($line)){
                    $parser->data('priya.module.parser.tag.line', $line_nr + $counter);
//                     $program[$nr] = Tag::OPEN . $line . Tag::CLOSE;
                    $program[$nr] = $parser->compile(Tag::OPEN . $line . Tag::CLOSE, $parser->data(), false);
                } else {
                    $program[$nr] = '';
                }
                $counter++;
            }

            $compile = $program;
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

    public static function exectute($tag=array(), $attribute='', $parser=null){
        return $tag;
    }
}