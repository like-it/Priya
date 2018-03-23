<?php

namespace Priya\Module\Parse;

use Priya\Module\Core;
use Priya\Module\Parse;

class Priya extends Core {
    const TAG = 'priya';
    const CLOSE = '/priya';

    public static function find($tag='', $string='', $parser=null){
        if($tag[Tag::TAG] == Tag::OPEN . Priya::TAG . TAG::CLOSE){
            $random = Parse::random();
            $parser->data('priya.module.parser.priya', Tag::OPEN . Priya::TAG . Tag::MIN . $random .']');

            while(stristr($string, $parser->data('priya.module.parser.priya'))){
                $random = Parse::random();
                $parser->data('priya.module.parser.priya', '[' . Priya::TAG . '-' . $random .']');
            }
            $explode = explode(Tag::OPEN . Priya::TAG . TAG::CLOSE, $string, 2);
            $string = implode($parser->data('priya.module.parser.priya'), $explode);
        }
        elseif($tag[Tag::TAG] == Tag::OPEN . Priya::CLOSE . TAG::CLOSE){
            $priya = $parser->data('priya.module.parser.priya');
            $explode = explode($priya, $string, 2);
            if(isset($explode[1])){
                $content = explode(Tag::OPEN . Priya::CLOSE . TAG::CLOSE, $explode[1], 2);
            }
            $part = explode(Tag::NEWLINE, $content[0]);
            $program = array();
            foreach($part as $nr => $line){
                //add split on ; for single line...
                $mask = ' ' . Tag::OPEN . Tag::CLOSE;
                $line = trim($line, $mask);
                if(!empty($line)){
                    $program[$nr] = Tag::OPEN . $line . Tag::CLOSE;
                }
            }
            $program = $parser->compile($program, $parser->data(), false);
            $explode[1] = $content[1]; //implode(TAG::NEWLINE, $program);
            $parser->data('delete', 'priya.module.parser.priya');
            $string = implode('', $explode);
        }
        return $string;
    }

    public static function exectute($tag=array(), $attribute='', $parser=null){
        return $tag;
    }
}