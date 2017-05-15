<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_output($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $string = array_shift($argumentList);
    $hide = array_shift($argumentList);
    $toHide = false;
    if(
        in_array($hide, array(
            'hide',
            'hidden',
            'off'
        )) ||
        $hide === false
       ){
        $toHide = true;
        $parser->data($parser->random() . '.Parser.output.hidden', true);
    }
    elseif(
        in_array($hide, array(
            'show',
            'unhide',
            'on'
        )) ||
        $hide === true
    ){
        $parser->data('delete', $parser->random() . '.Parser.output.hidden');
    }

    $hide = $parser->data($parser->random() . '.Parser.output.hidden');

    if(is_object($string) && method_exists($string, '__toString')){
        if(empty($hide) || (!empty($hide) && $toHide === true)){
            echo $string;
        }
        return $string;
    }
    elseif(is_object($string) || is_array($string)){
        $json = $parser->object($string, 'json');
        if(empty($hide) || (!empty($hide) && $toHide === true)){
            echo $json;
        }
        return $string;
    } else {
        $string = str_replace('PHP_EOL', PHP_EOL, $string);
        if(empty($hide) || (!empty($hide) && $toHide === true)){
            echo $string;
        }
        return $string;
    }

}
