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
//     var_dump('---------------------');
//     var_dump($argumentList);
    $string = array_shift($argumentList);
    $hide = array_shift($argumentList);
    $toHide = false;
    if($hide == 'hide' || $hide === false){
        $toHide = true;
        $parser->data($parser->random() . '.Parser.output.hide', true);
    }
    elseif($hide == 'show' || $hide == 'unhide' || $hide === true){
        $parser->data('delete', $parser->random() . '.Parser.output.hide');
    }

    $hide = $parser->data($parser->random() . '.Parser.output.hide');

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
