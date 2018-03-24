<?php

namespace Priya\Module\Parse;

use Priya\Module\Core;
use Priya\Module\Parse;

class Priya extends Core {
    const TAG = 'priya';
    const CLOSE = '/priya';
    const NEWLINE = "\n";
    const SPACE = ' ';
    const MIN = '-';
    const DELETE = 'delete';
    const MASK = Priya::SPACE . Tag::OPEN . Tag::CLOSE;

    public static function find($tag='', $string='', $parser=null){
        if($tag[Tag::TAG] == Tag::OPEN . Priya::TAG . TAG::CLOSE){
            $random = Parse::random();
            $parser->data('priya.module.parser.priya', Tag::OPEN . Priya::TAG . Priya::MIN . $random . Tag::CLOSE);

            while(stristr($string, $parser->data('priya.module.parser.priya'))){
                $random = Parse::random();
                $parser->data('priya.module.parser.priya', Tag::OPEN . Priya::TAG . Priya::MIN . $random .Tag::CLOSE);
            }
            $explode = explode(Tag::OPEN . Priya::TAG . TAG::CLOSE, $string, 2);
            $string = implode($parser->data('priya.module.parser.priya'), $explode);
            $parser->data('priya.module.parser.literal', true);
        }
        elseif($tag[Tag::TAG] == Tag::OPEN . Priya::CLOSE . TAG::CLOSE){
            $priya = $parser->data('priya.module.parser.priya');
            $explode = explode($priya, $string, 2);
            if(isset($explode[1])){
                $content = explode(Tag::OPEN . Priya::CLOSE . TAG::CLOSE, $explode[1], 2);
            }
            $part = explode(Priya::NEWLINE, $content[0]);
            $program = array();
            $mask = ' ' . Tag::OPEN . Tag::CLOSE;
            foreach($part as $nr => $line){
                //add split on ; or space (not between quotes nor parameter) for single line...
                $line = trim($line, $mask);
                if(!empty($line)){
                    $program[$nr] = Tag::OPEN . $line . Tag::CLOSE;
                }
            }
            $parser->data(Priya::DELETE, 'priya.module.parser.literal');

            $compile = $parser->compile($program, $parser->data(), false);

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
            $parser->data(Priya::DELETE, 'priya.module.parser.priya');
            $string = implode('', $explode);
        }
        return $string;
    }

    public static function exectute($tag=array(), $attribute='', $parser=null){
        return $tag;
    }
}