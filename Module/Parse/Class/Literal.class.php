<?php

namespace Priya\Module\Parse;

use Priya\Module\Core;
use Priya\Module\Parse;

class Literal extends Core {
    const TAG = 'literal';
    const CLOSE = '/literal';
    const NEWLINE = "\n";
    const SPACE = ' ';
    const MIN = '-';
    const DELETE = 'delete';
    const MASK = Priya::SPACE . Tag::OPEN . Tag::CLOSE;

    const DATA_TAG = 'priya.module.parser.tag.literal';
    const DATA_LITERAL = 'priya.module.parser.literal';

    public static function find($tag='', $string='', $parser=null){
        if($tag[Tag::TAG] == Tag::OPEN . Priya::TAG . TAG::CLOSE){
            $random = Parse::random();
            $parser->data(Literal::DATA_TAG, Tag::OPEN . Literal::TAG . Literal::MIN . $random . Tag::CLOSE);

            while(stristr($string, $parser->data(Literal::DATA_TAG))){
                $random = Parse::random();
                $parser->data(Literal::DATA_TAG, Tag::OPEN . Literal::TAG . Literal::MIN . $random .Tag::CLOSE);
            }
            $explode = explode(Tag::OPEN . Literal::TAG . TAG::CLOSE, $string, 2);
            $string = implode($parser->data(Literal::DATA_TAG), $explode);
            $parser->data(Literal::DATA_LITERAL, true);
        }
        elseif($tag[Tag::TAG] == Tag::OPEN . Literal::CLOSE . TAG::CLOSE){
            $delimiter = $parser->data(Literal::DATA_TAG);
            $explode = explode($delimiter, $string, 2);
            if(isset($explode[1])){
                $content = explode(Tag::OPEN . Literal::CLOSE . TAG::CLOSE, $explode[1], 2);
            }
            $parser->data(Priya::DELETE, Literal::DATA_TAG);
            $parser->data(Priya::DELETE, Literal::DATA_LITERAL);
            $string = implode('', $content);
        }
        return $string;
    }

    public static function exectute($tag=array(), $attribute='', $parser=null){
        return $tag;
    }
}